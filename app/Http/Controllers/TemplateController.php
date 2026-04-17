<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);
        $templates = DB::table('message_templates')
            ->where('tenant_id', $auth->tenant_id)
            ->orderBy('channel')
            ->orderBy('name')
            ->get();

        return view('templates.index', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        $data = $request->validate([
            'channel' => 'required|string|in:email,whatsapp,notification',
            'name' => 'required|string|max:150',
            'subject' => 'nullable|string|max:190',
            'body' => 'required|string|max:10000',
            'is_active' => 'nullable|boolean',
        ]);

        DB::table('message_templates')->insert([
            'tenant_id' => $auth->tenant_id,
            'channel' => $data['channel'],
            'name' => $data['name'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'is_active' => (int) ($data['is_active'] ?? 1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Template created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        $data = $request->validate([
            'subject' => 'nullable|string|max:190',
            'body' => 'required|string|max:10000',
            'is_active' => 'nullable|boolean',
        ]);

        DB::table('message_templates')
            ->where('tenant_id', $auth->tenant_id)
            ->where('id', $id)
            ->update([
                'subject' => $data['subject'] ?? null,
                'body' => $data['body'],
                'is_active' => (int) ($data['is_active'] ?? 1),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Template updated.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        DB::table('message_templates')
            ->where('tenant_id', $auth->tenant_id)
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'Template deleted.');
    }
}

