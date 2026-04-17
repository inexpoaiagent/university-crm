<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Notification;
use App\Models\StudentRequest;
use App\Models\StudentMessage;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Services\StudentDocumentService;
use App\Services\UniversityMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PortalWebController extends Controller
{
    public function __construct(
        private readonly StudentDocumentService $documentService,
        private readonly UniversityMatchingService $matchingService
    ) {
    }

    public function showLogin(): View
    {
        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $user = User::query()
            ->where('email', $data['email'])
            ->where('role_slug', 'student')
            ->whereNull('deleted_at')
            ->first();
        if (!$user || !$user->is_active || !Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }
        Auth::guard('crm')->logout();
        Auth::guard('student')->login($user, false);
        $request->session()->regenerate();

        return redirect('/portal/dashboard');
    }

    public function dashboard(Request $request): View
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $applications = Application::query()->where('tenant_id', $user->tenant_id)->where('student_id', $student->id)->get();
        $documents = $this->documentService->requiredRows($user->tenant_id, $student->id);
        $messages = collect();
        if (Schema::hasTable('student_messages')) {
            $messages = StudentMessage::query()->where('tenant_id', $user->tenant_id)->where('student_user_id', $user->id)->latest('id')->limit(5)->get();
        }
        $uploadedCount = $documents->where('is_missing', false)->count();
        $requiredCount = count(StudentDocumentService::REQUIRED_DOCUMENTS);
        $documentProgress = $requiredCount > 0 ? (int) round(($uploadedCount / $requiredCount) * 100) : 0;

        return view('portal.dashboard', compact('student', 'applications', 'documents', 'messages', 'uploadedCount', 'requiredCount', 'documentProgress'));
    }

    public function universities(Request $request): View
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $universities = University::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', 1)
            ->get();
        $universities = $this->matchingService->rankedForStudent($universities, [
            'field' => $request->query('field', $student->field_of_study),
            'country' => $request->query('country', $student->target_country),
            'budget' => $request->query('budget', $student->budget_usd ?? 0),
            'language' => $request->query('language', $student->english_level),
            'gpa' => $student->gpa,
        ]);

        return view('portal.universities', compact('universities', 'student'));
    }

    public function applications(Request $request): View
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $applications = Application::query()->where('tenant_id', $user->tenant_id)->where('student_id', $student->id)->get();
        return view('portal.applications', compact('applications'));
    }

    public function documents(Request $request): View
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $documents = $this->documentService->requiredRows($user->tenant_id, $student->id);
        return view('portal.documents', compact('documents'));
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $data = $request->validate([
            'type' => 'required|string|in:passport,diploma,transcript,english_certificate,photo',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'expiry_date' => 'nullable|date',
        ]);
        $path = $request->file('file')->store('docs', 'public');
        $document = Document::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('student_id', $student->id)
            ->where('type', $data['type'])
            ->latest('id')
            ->first();

        $payload = [
            'tenant_id' => $user->tenant_id,
            'student_id' => $student->id,
            'type' => $data['type'],
            'file_url' => '/storage/'.$path,
            'file_name' => basename($path),
            'status' => 'uploaded',
            'expiry_date' => $data['expiry_date'] ?? null,
        ];
        if ($document) {
            $document->update($payload);
        } else {
            Document::query()->create($payload);
        }

        return back()->with('success', 'Document uploaded');
    }

    public function applyToUniversity(Request $request): RedirectResponse
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $data = $request->validate([
            'university_id' => 'required|integer|exists:universities,id',
            'target_program' => 'nullable|string|max:190',
        ]);

        $university = University::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', 1)
            ->findOrFail((int) $data['university_id']);

        $targetProgram = trim((string) ($data['target_program'] ?? ''));
        $targetProgram = $targetProgram !== '' ? $targetProgram : ($student->field_of_study ?: 'General Admission');
        $payloadProgram = $targetProgram.' @ '.$university->name;

        $existingPending = StudentRequest::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('email', $student->email)
            ->where('status', 'pending')
            ->where('target_program', $payloadProgram)
            ->first();
        if ($existingPending) {
            return back()->withErrors(['target_program' => 'You already sent this request and it is pending review.']);
        }

        $requestRow = StudentRequest::query()->create([
            'tenant_id' => $user->tenant_id,
            'full_name' => $student->full_name,
            'email' => $student->email,
            'phone' => $student->phone,
            'nationality' => $student->nationality,
            'target_program' => $payloadProgram,
            'status' => 'pending',
        ]);

        $admins = User::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('role_slug', ['admin', 'super_admin'])
            ->where('is_active', 1)
            ->get();
        foreach ($admins as $admin) {
            Notification::query()->create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $admin->id,
                'type' => 'student_request',
                'title' => 'New portal application request',
                'body' => $student->full_name.' requested '.$payloadProgram,
                'meta_json' => json_encode([
                    'student_request_id' => $requestRow->id,
                    'student_id' => $student->id,
                    'university_id' => $university->id,
                ]),
            ]);
        }

        return back()->with('success', 'Your request was sent to admin for review.');
    }

    public function messages(Request $request): View
    {
        $user = $this->authUser($request);
        if (!Schema::hasTable('student_messages')) {
            return back()->withErrors(['message' => 'Messaging module is not migrated yet.']);
        }
        $messages = StudentMessage::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('student_user_id', $user->id)
            ->latest('id')
            ->paginate(20);

        return view('portal.messages', compact('messages'));
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $user = $this->authUser($request);
        if (!Schema::hasTable('student_messages')) {
            return back()->withErrors(['message' => 'Messaging module is not migrated yet.']);
        }
        $student = $this->studentForUser($user);
        $data = $request->validate([
            'message' => 'required|string|min:3|max:4000',
        ]);

        $recipient = User::query()
            ->where('tenant_id', $user->tenant_id)
            ->whereIn('role_slug', ['admin', 'agent', 'super_admin'])
            ->where('is_active', 1)
            ->orderByRaw("FIELD(role_slug, 'agent', 'admin', 'super_admin')")
            ->first();

        $row = StudentMessage::query()->create([
            'tenant_id' => $user->tenant_id,
            'student_id' => $student->id,
            'student_user_id' => $user->id,
            'recipient_user_id' => $recipient?->id,
            'sender_role' => 'student',
            'body' => $data['message'],
        ]);

        if ($recipient) {
            Notification::query()->create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $recipient->id,
                'type' => 'student_message',
                'title' => 'New message from student',
                'body' => $student->full_name.': '.$data['message'],
                'meta_json' => json_encode(['message_id' => $row->id, 'student_id' => $student->id]),
            ]);
        }

        return back()->with('success', 'Message sent.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/portal/login');
    }

    private function studentForUser(User $user): Student
    {
        $query = Student::query()->where('tenant_id', $user->tenant_id);
        if (Schema::hasColumn('students', 'user_id')) {
            $query->where(function ($sub) use ($user) {
                $sub->where('user_id', $user->id)->orWhere('email', $user->email);
            });
        } else {
            $query->where('email', $user->email);
        }

        return $query->firstOrFail();
    }
}
