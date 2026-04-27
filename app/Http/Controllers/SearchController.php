<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Student;
use App\Models\University;
use Illuminate\Support\Facades\DB;
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
        $hasProgramsTable = Schema::hasTable('university_programs');

        $students = Student::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->whereNull('deleted_at')
            ->when($keywords !== '', fn ($query) => $query->where(function ($sub) use ($keywords) {
                $sub->where('full_name', 'like', "%{$keywords}%")
                    ->orWhere('email', 'like', "%{$keywords}%")
                    ->orWhere('phone', 'like', "%{$keywords}%")
                    ->orWhere('field_of_study', 'like', "%{$keywords}%");
            }))
            ->when($studyField !== '', fn ($query) => $query->where('field_of_study', 'like', "%{$studyField}%"))
            ->when($stage !== '', fn ($query) => $query->where('stage', $stage))
            ->when($country !== '', fn ($query) => $query->where('target_country', 'like', "%{$country}%"))
            ->latest('id')
            ->limit(30)
            ->get();

        $programUniversityIds = null;
        if ($hasProgramsTable && ($degree !== '' || $studyField !== '')) {
            $programUniversityIds = DB::table('university_programs')
                ->where('tenant_id', $user->tenant_id)
                ->when($degree !== '', fn ($query) => $query->where('degree_level', 'like', "%{$degree}%"))
                ->when($studyField !== '', fn ($query) => $query->where('program_name', 'like', "%{$studyField}%"))
                ->distinct()
                ->pluck('university_id')
                ->all();
        }

        $applications = Application::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->when($keywords !== '', fn ($query) => $query->where(function ($sub) use ($keywords) {
                $sub->where('program', 'like', "%{$keywords}%")
                    ->orWhere('intake', 'like', "%{$keywords}%")
                    ->orWhere('notes', 'like', "%{$keywords}%");
            }))
            ->when($degree !== '', fn ($query) => $query->where('program', 'like', "%{$degree}%"))
            ->when($studyField !== '', fn ($query) => $query->where('program', 'like', "%{$studyField}%"))
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
            ->when($countryUniversity !== '', fn ($query) => $query->where('country', $countryUniversity))
            ->when($universityName !== '', fn ($query) => $query->where('name', 'like', "%{$universityName}%"))
            ->when($cityUniversity !== '', function ($query) use ($cityUniversity, $hasCityColumn) {
                if ($hasCityColumn) {
                    $query->where('city', $cityUniversity);
                    return;
                }
                $query->where(function ($sub) use ($cityUniversity) {
                    $sub->where('name', 'like', "%{$cityUniversity}%")
                        ->orWhere('description', 'like', "%{$cityUniversity}%")
                        ->orWhere('programs_summary', 'like', "%{$cityUniversity}%");
                });
            })
            ->when($universityType !== '', function ($query) use ($universityType, $hasTypeColumn) {
                $needle = mb_strtolower($universityType);
                if ($hasTypeColumn) {
                    $query->whereRaw('LOWER(institution_type) = ?', [$needle]);
                    return;
                }
                if ($needle === 'school') {
                    $query->where(function ($sub) {
                        $sub->whereRaw('LOWER(name) like ?', ['%school%'])
                            ->orWhereRaw('LOWER(description) like ?', ['%school%']);
                    });
                    return;
                }
                $query->where(function ($sub) {
                    $sub->whereRaw('LOWER(name) like ?', ['%university%'])
                        ->orWhereRaw('LOWER(description) like ?', ['%university%']);
                });
            })
            ->when(is_array($programUniversityIds) && !empty($programUniversityIds), fn ($query) => $query->whereIn('id', $programUniversityIds))
            ->when(is_array($programUniversityIds) && empty($programUniversityIds) && ($degree !== '' || $studyField !== ''), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('id')
            ->limit(20)
            ->get();

        $countryOptions = University::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->values()
            ->all();

        $citiesByCountry = [];
        if ($hasCityColumn) {
            $cityRows = University::query()
                ->forTenant($user->tenant_id, $user->role_slug)
                ->whereNotNull('country')
                ->whereNotNull('city')
                ->where('country', '!=', '')
                ->where('city', '!=', '')
                ->get(['country', 'city']);
            foreach ($cityRows as $row) {
                $citiesByCountry[$row->country] ??= [];
                if (!in_array($row->city, $citiesByCountry[$row->country], true)) {
                    $citiesByCountry[$row->country][] = $row->city;
                }
            }
        }
        foreach ($citiesByCountry as $countryKey => $cities) {
            $citiesByCountry[$countryKey] = collect($cities)->unique()->sort()->values()->all();
        }

        $degreeOptions = [];
        if ($hasProgramsTable) {
            $degreeOptions = DB::table('university_programs')
                ->where('tenant_id', $user->tenant_id)
                ->whereNotNull('degree_level')
                ->where('degree_level', '!=', '')
                ->distinct()
                ->orderBy('degree_level')
                ->pluck('degree_level')
                ->values()
                ->all();
        }
        if (empty($degreeOptions)) {
            $degreeOptions = ['Bachelor', 'Master', 'PhD', 'Diploma', 'Foundation'];
        }

        $studyFieldOptions = Student::query()
            ->forTenant($user->tenant_id, $user->role_slug)
            ->whereNotNull('field_of_study')
            ->where('field_of_study', '!=', '')
            ->distinct()
            ->orderBy('field_of_study')
            ->pluck('field_of_study')
            ->values()
            ->all();
        if (empty($studyFieldOptions)) {
            $studyFieldOptions = ['Business', 'Computer Science', 'Engineering', 'Medicine', 'Law', 'Architecture'];
        }

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
            'universities',
            'countryOptions',
            'citiesByCountry',
            'degreeOptions',
            'studyFieldOptions'
        ));
    }
}
