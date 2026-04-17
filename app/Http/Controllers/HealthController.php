<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HealthController extends Controller
{
    public function index(Request $request): View
    {
        $dbOk = true;
        try {
            DB::select('SELECT 1');
        } catch (\Throwable) {
            $dbOk = false;
        }

        $storageOk = Storage::disk('public')->exists('.') || is_dir(storage_path('app/public'));

        return view('health.index', [
            'dbOk' => $dbOk,
            'storageOk' => $storageOk,
            'appEnv' => config('app.env'),
            'appDebug' => (bool) config('app.debug'),
            'appUrl' => (string) config('app.url'),
        ]);
    }

    public function backup(Request $request)
    {
        $auth = $this->authUser($request);
        $data = [
            'generated_at' => now()->toDateTimeString(),
            'tenant_id' => $auth->tenant_id,
            'students' => Student::query()->where('tenant_id', $auth->tenant_id)->get(),
            'applications' => Application::query()->where('tenant_id', $auth->tenant_id)->get(),
            'documents' => Document::query()->where('tenant_id', $auth->tenant_id)->get(),
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="tenant-backup-'.$auth->tenant_id.'.json"');
    }

    public function restore(Request $request)
    {
        $auth = $this->authUser($request);
        $payload = $request->validate([
            'backup_json' => 'required|string',
        ]);
        $json = json_decode($payload['backup_json'], true);
        if (!is_array($json)) {
            return back()->withErrors(['backup_json' => 'Invalid JSON']);
        }

        DB::transaction(function () use ($auth, $json): void {
            foreach (($json['students'] ?? []) as $row) {
                Student::query()->updateOrCreate(
                    ['tenant_id' => $auth->tenant_id, 'id' => $row['id'] ?? 0],
                    array_merge($row, ['tenant_id' => $auth->tenant_id])
                );
            }
            foreach (($json['applications'] ?? []) as $row) {
                Application::query()->updateOrCreate(
                    ['tenant_id' => $auth->tenant_id, 'id' => $row['id'] ?? 0],
                    array_merge($row, ['tenant_id' => $auth->tenant_id])
                );
            }
            foreach (($json['documents'] ?? []) as $row) {
                Document::query()->updateOrCreate(
                    ['tenant_id' => $auth->tenant_id, 'id' => $row['id'] ?? 0],
                    array_merge($row, ['tenant_id' => $auth->tenant_id])
                );
            }
        });

        return back()->with('success', 'Backup restored.');
    }
}

