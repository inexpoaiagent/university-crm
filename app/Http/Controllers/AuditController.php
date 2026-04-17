<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);
        $logs = AuditLog::query()
            ->forTenant($auth->tenant_id, $auth->role_slug)
            ->latest('id')
            ->paginate(40);

        return view('audit.index', compact('logs'));
    }
}

