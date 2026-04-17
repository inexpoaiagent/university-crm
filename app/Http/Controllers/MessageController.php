<?php

namespace App\Http\Controllers;

use App\Models\StudentMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);
        $messages = collect();
        $tableExists = Schema::hasTable('student_messages');

        if ($tableExists) {
            $messages = StudentMessage::query()
                ->forTenant($auth->tenant_id, $auth->role_slug)
                ->latest('id')
                ->paginate(20);
        }

        return view('messages.index', [
            'messages' => $messages,
            'tableExists' => $tableExists,
        ]);
    }
}
