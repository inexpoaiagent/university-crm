<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Notification;
use App\Models\StudentMessage;
use App\Models\StudentRequest;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PortalWebController extends Controller
{
    public function showLogin(): View
    {
        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => 'nullable|string|max:190',
            'email' => 'nullable|string|max:190',
            'password' => 'required|string',
        ]);
        $login = trim((string) ($data['login'] ?? $data['email'] ?? ''));
        if ($login === '') {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'The login field is required.']);
        }
        $user = User::query()
            ->where(function ($q) use ($login) {
                $q->where('email', $login)->orWhere('name', $login);
            })
            ->where('role_slug', 'student')
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            $studentExists = Student::query()
                ->where(function ($q) use ($login) {
                    $q->where('email', $login)->orWhere('full_name', $login);
                })
                ->exists();

            if ($studentExists) {
                return back()
                    ->withInput($request->except('password'))
                    ->withErrors(['login' => 'Portal account is not configured for this student. Please contact admin.']);
            }
        }

        if (!$user || !$user->is_active || !Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'Invalid credentials']);
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
        $documents = Document::query()->where('tenant_id', $user->tenant_id)->where('student_id', $student->id)->get();
        $requiredTypes = ['passport', 'diploma', 'transcript', 'english_certificate', 'photo'];
        $requiredCount = count($requiredTypes);
        $uploadedCount = $documents
            ->whereIn('type', $requiredTypes)
            ->pluck('type')
            ->unique()
            ->count();
        $documentProgress = $requiredCount > 0
            ? (int) round(($uploadedCount / $requiredCount) * 100)
            : 0;

        $messages = collect();
        if (Schema::hasTable('student_messages')) {
            $messages = StudentMessage::query()->where('tenant_id', $user->tenant_id)->where('student_user_id', $user->id)->latest('id')->limit(5)->get();
        }

        return view('portal.dashboard', compact(
            'student',
            'applications',
            'documents',
            'messages',
            'uploadedCount',
            'requiredCount',
            'documentProgress'
        ));
    }

    public function universities(Request $request): View
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $universities = University::query()->where('tenant_id', $user->tenant_id)->where('is_active', 1)->get();
        return view('portal.universities', compact('universities', 'student'));
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
            ->findOrFail((int) $data['university_id']);

        StudentRequest::query()->create([
            'tenant_id' => $user->tenant_id,
            'full_name' => $student->full_name,
            'email' => $student->email,
            'phone' => $student->phone,
            'nationality' => $student->nationality,
            'target_program' => $data['target_program'] ?: ($student->field_of_study ?: $university->name),
            'status' => 'pending',
            'review_note' => 'Portal request for university #'.$university->id.' - '.$university->name,
        ]);

        return back()->with('success', 'Your request was sent to admin successfully.');
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
        $documents = Document::query()->where('tenant_id', $user->tenant_id)->where('student_id', $student->id)->get();
        return view('portal.documents', compact('documents', 'student'));
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $user = $this->authUser($request);
        $student = $this->studentForUser($user);
        $data = $request->validate([
            'type' => 'required|string|in:passport,diploma,transcript,english_certificate,photo,other_documents,payment_receipt',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
        $path = $request->file('file')->store('docs', 'public');
        Document::query()->create([
            'tenant_id' => $user->tenant_id,
            'student_id' => $student->id,
            'type' => $data['type'],
            'file_url' => '/storage/'.$path,
            'file_name' => basename($path),
            'status' => 'uploaded',
        ]);

        return back()->with('success', 'Document uploaded');
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
