<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Student;
use App\Models\StudentMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CrmMessageController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);
        if (!Schema::hasTable('student_messages')) {
            abort(500, 'Messaging module is not migrated yet.');
        }

        $selectedStudentId = (int) $request->query('student_id', 0);
        $students = Student::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $selectedStudent = $selectedStudentId > 0
            ? $students->firstWhere('id', $selectedStudentId)
            : $students->first();

        $messages = collect();
        if ($selectedStudent) {
            $messages = StudentMessage::query()
                ->forTenant($auth->tenant_id, $auth->role_slug)
                ->where('student_id', $selectedStudent->id)
                ->latest('id')
                ->paginate(30)
                ->withQueryString();
        }

        return view('messages.index', compact('students', 'selectedStudent', 'messages'));
    }

    public function send(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        if (!Schema::hasTable('student_messages')) {
            return back()->withErrors(['message' => 'Messaging module is not migrated yet.']);
        }

        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'body' => 'required|string|min:2|max:4000',
        ]);

        $student = Student::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->whereNull('deleted_at')
            ->findOrFail((int) $data['student_id']);

        $studentUser = null;
        if ($student->user_id) {
            $studentUser = User::query()->where('id', $student->user_id)->where('role_slug', 'student')->first();
        }
        if (!$studentUser && !empty($student->email)) {
            $studentUser = User::query()->where('email', $student->email)->where('role_slug', 'student')->first();
        }
        if (!$studentUser) {
            return back()->withErrors(['body' => 'Student portal account was not found.']);
        }

        $message = StudentMessage::query()->create([
            'tenant_id' => $auth->tenant_id,
            'student_id' => $student->id,
            'student_user_id' => $studentUser->id,
            'recipient_user_id' => $studentUser->id,
            'sender_role' => 'admin',
            'body' => $data['body'],
        ]);

        Notification::query()->create([
            'tenant_id' => $auth->tenant_id,
            'user_id' => $studentUser->id,
            'type' => 'admin_message',
            'title' => 'New message from CRM',
            'body' => $data['body'],
            'meta_json' => json_encode(['message_id' => $message->id, 'student_id' => $student->id]),
        ]);

        return back()->with('success', 'Message sent to student.');
    }
}

