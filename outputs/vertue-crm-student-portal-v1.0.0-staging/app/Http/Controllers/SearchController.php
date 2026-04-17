<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->authUser($request);
        $q = trim((string) $request->query('q', ''));
        $keywords = trim((string) $request->query('keywords', $q));
        $countryUniversity = trim((string) $request->query('country_university', ''));
        $cityUniversity = trim((string) $request->query('city_university', ''));
        $universityType = trim((string) $request->query('university_type', ''));
        $universityName = trim((string) $request->query('university_name', ''));
        $degree = trim((string) $request->query('degree', ''));
        $studyField = trim((string) $request->query('study_field', ''));
        $stage = trim((string) $request->query('stage', ''));
        $country = trim((string) $request->query('country', ''));
        $status = trim((string) $request->query('status', ''));
        $hasCityColumn = Schema::hasColumn('universities', 'city');
        $hasTypeColumn = Schema::hasColumn('universities', 'institution_type');
        $hasDegreeColumn = Schema::hasColumn('universities', 'degree_levels');
        $hasFieldColumn = Schema::hasColumn('universities', 'study_fields');

        $students = Student::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->whereNull('deleted_at')
            ->when($keywords !== '', fn ($query) => $query->where(function ($sub) use ($keywords) {
                $sub->where('full_name', 'like', "%{$keywords}%")
                    ->orWhere('email', 'like', "%{$keywords}%")
                    ->orWhere('phone', 'like', "%{$keywords}%")
                    ->orWhere('field_of_study', 'like', "%{$keywords}%");
            }))
            ->when($stage !== '', fn ($query) => $query->where('stage', $stage))
            ->when($country !== '', fn ($query) => $query->where('target_country', 'like', "%{$country}%"))
            ->latest('id')
            ->limit(30)
            ->get();

        $applications = Application::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->when($keywords !== '', fn ($query) => $query->where(function ($sub) use ($keywords) {
                $sub->where('program', 'like', "%{$keywords}%")
                    ->orWhere('intake', 'like', "%{$keywords}%")
                    ->orWhere('notes', 'like', "%{$keywords}%");
            }))
            ->when($degree !== '', fn ($query) => $query->where('program', 'like', "%{$degree}%"))
            ->when($studyField !== '', fn ($query) => $query->where(function ($sub) use ($studyField) {
                $sub->where('program', 'like', "%{$studyField}%")
                    ->orWhere('notes', 'like', "%{$studyField}%");
            }))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->limit(30)
            ->get();

        $universities = University::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->when($keywords !== '', fn ($query) => $query->where(function ($sub) use ($keywords) {
                $sub->where('name', 'like', "%{$keywords}%")
                    ->orWhere('country', 'like', "%{$keywords}%")
                    ->orWhere('programs_summary', 'like', "%{$keywords}%")
                    ->orWhere('description', 'like', "%{$keywords}%");
            }))
            ->when($countryUniversity !== '', fn ($query) => $query->where('country', 'like', "%{$countryUniversity}%"))
            ->when($universityName !== '', fn ($query) => $query->where('name', 'like', "%{$universityName}%"))
            ->when($cityUniversity !== '', function ($query) use ($cityUniversity, $hasCityColumn) {
                if ($hasCityColumn) {
                    $query->where('city', 'like', "%{$cityUniversity}%");
                    return;
                }
                $query->where(function ($sub) use ($cityUniversity) {
                    $sub->where('description', 'like', "%{$cityUniversity}%")
                        ->orWhere('programs_summary', 'like', "%{$cityUniversity}%");
                });
            })
            ->when($universityType !== '', function ($query) use ($universityType, $hasTypeColumn) {
                if ($hasTypeColumn) {
                    $query->where('institution_type', 'like', "%{$universityType}%");
                    return;
                }
                $query->where('description', 'like', "%{$universityType}%");
            })
            ->when($degree !== '', function ($query) use ($degree, $hasDegreeColumn) {
                if ($hasDegreeColumn) {
                    $query->where('degree_levels', 'like', "%{$degree}%");
                    return;
                }
                $query->where(function ($sub) use ($degree) {
                    $sub->where('programs_summary', 'like', "%{$degree}%")
                        ->orWhere('description', 'like', "%{$degree}%");
                });
            })
            ->when($studyField !== '', function ($query) use ($studyField, $hasFieldColumn) {
                if ($hasFieldColumn) {
                    $query->where('study_fields', 'like', "%{$studyField}%");
                    return;
                }
                $query->where(function ($sub) use ($studyField) {
                    $sub->where('programs_summary', 'like', "%{$studyField}%")
                        ->orWhere('description', 'like', "%{$studyField}%");
                });
            })
            ->latest('id')
            ->limit(20)
            ->get();

        return view('search.index', compact(
            'q',
            'keywords',
            'countryUniversity',
            'cityUniversity',
            'universityType',
            'universityName',
            'degree',
            'studyField',
            'stage',
            'country',
            'status',
            'students',
            'applications',
            'universities'
        ));
    }
}
