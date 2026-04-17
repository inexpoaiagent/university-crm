<?php

namespace App\Services;

use App\Models\University;
use Illuminate\Support\Collection;

class UniversityMatchingService
{
    public function rankedForStudent(Collection $universities, array $studentSignals): Collection
    {
        $field = strtolower((string) ($studentSignals['field'] ?? ''));
        $country = strtolower((string) ($studentSignals['country'] ?? ''));
        $budget = (float) ($studentSignals['budget'] ?? 0);
        $language = strtolower((string) ($studentSignals['language'] ?? ''));
        $gpa = (float) ($studentSignals['gpa'] ?? 0);

        return $universities->map(function (University $u) use ($field, $country, $budget, $language, $gpa) {
            $score = 0;
            $programText = strtolower((string) ($u->programs_summary ?: $u->description ?: ''));

            if ($field !== '' && str_contains($programText, $field)) {
                $score += 30;
            }
            if ($country !== '' && str_contains(strtolower((string) $u->country), $country)) {
                $score += 15;
            }
            if ($language !== '' && str_contains(strtolower((string) $u->language), $language)) {
                $score += 15;
            }
            if ($gpa > 0) {
                $score += $gpa >= 3.2 ? 15 : 8;
            }

            $tuitionDigits = preg_replace('/[^\d]/', '', (string) $u->tuition_range);
            $tuition = is_numeric($tuitionDigits) ? (float) $tuitionDigits : 0;
            if ($budget > 0 && $tuition > 0) {
                $score += $tuition <= $budget ? 20 : 5;
            } else {
                $score += 10;
            }

            $deadlineDays = $u->deadline ? now()->diffInDays($u->deadline, false) : null;
            if ($deadlineDays !== null) {
                $score += ($deadlineDays >= 0 && $deadlineDays <= 90) ? 10 : 5;
            } else {
                $score += 5;
            }

            $u->match_score = min(100, $score);
            return $u;
        })->sortByDesc('match_score')->values();
    }
}

