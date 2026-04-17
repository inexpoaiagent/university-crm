<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Collection;

class StudentDocumentService
{
    public const REQUIRED_DOCUMENTS = [
        'passport' => 'Passport',
        'diploma' => 'Diploma',
        'transcript' => 'Transcript',
        'english_certificate' => 'English Certificate',
        'photo' => 'Photo',
    ];

    public function requiredRows(int $tenantId, int $studentId): Collection
    {
        $latestDocs = Document::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->get()
            ->groupBy('type')
            ->map(fn ($rows) => $rows->sortByDesc('id')->first());

        return collect(self::REQUIRED_DOCUMENTS)->map(function (string $label, string $type) use ($latestDocs) {
            $doc = $latestDocs->get($type);
            return (object) [
                'id' => $doc?->id,
                'type' => $type,
                'label' => $label,
                'is_missing' => $doc === null || empty($doc->file_url),
                'status' => $doc?->status ?? 'missing',
                'file_url' => $doc?->file_url,
                'file_name' => $doc?->file_name,
                'uploaded_at' => $doc?->updated_at,
                'expiry_date' => $doc?->expiry_date,
            ];
        })->values();
    }
}

