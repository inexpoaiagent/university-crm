<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        $auth = $this->authUser($request);
        $tokens = DB::table('api_tokens')
            ->where('tenant_id', $auth->tenant_id)
            ->orderByDesc('id')
            ->get();

        return view('api_tokens.index', compact('tokens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $auth = $this->authUser($request);
        $data = $request->validate(['name' => 'required|string|max:120']);
        $plain = 'vtk_'.Str::random(40);

        DB::table('api_tokens')->insert([
            'tenant_id' => $auth->tenant_id,
            'created_by' => $auth->id,
            'name' => $data['name'],
            'token_hash' => hash('sha256', $plain),
            'is_active' => 1,
            'last_used_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Token created: '.$plain.' (copy now)');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $auth = $this->authUser($request);
        DB::table('api_tokens')
            ->where('tenant_id', $auth->tenant_id)
            ->where('id', $id)
            ->delete();

        return back()->with('success', 'Token revoked.');
    }
}
