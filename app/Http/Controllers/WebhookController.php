<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function studentStatus(Request $request): JsonResponse
    {
        $token = (string) $request->header('X-API-Token', '');
        if ($token === '') {
            return response()->json(['ok' => false, 'message' => 'Missing API token'], 401);
        }
        $tokenRow = DB::table('api_tokens')
            ->where('token_hash', hash('sha256', $token))
            ->where('is_active', 1)
            ->first();
        if (!$tokenRow) {
            return response()->json(['ok' => false, 'message' => 'Invalid API token'], 401);
        }

        $data = $request->validate([
            'application_id' => 'required|integer|exists:applications,id',
            'status' => 'required|string|in:submitted,under_review,accepted,rejected,enrolled',
        ]);

        $app = Application::query()
            ->where('tenant_id', $tokenRow->tenant_id)
            ->findOrFail((int) $data['application_id']);
        $app->status = $data['status'];
        $app->save();

        DB::table('api_tokens')->where('id', $tokenRow->id)->update(['last_used_at' => now(), 'updated_at' => now()]);

        return response()->json(['ok' => true]);
    }
}

