<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List Published Jobs
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $query = JobVacancy::published()
            ->select([
                'id',
                'company_id',
                'title',
                'location',
                'employment_type',
                'published_at',
                'poster_path',
            ])
            ->with('company:id,name');

        /*
        |--------------------------------------------------------------------------
        | Search (Safe & Limited)
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = substr(trim((string) $request->input('search')), 0, 100);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($companyQuery) use ($search) {
                        $companyQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Employment Type Filter (Whitelisted)
        |--------------------------------------------------------------------------
        */

        $allowedTypes = ['fulltime', 'parttime', 'intern', 'remote'];

        if ($request->filled('employment_type')) {
            $type = (string) $request->input('employment_type');

            if (in_array($type, $allowedTypes, true)) {
                $query->where('employment_type', $type);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Location Filter (Improved: LIKE instead of exact)
        |--------------------------------------------------------------------------
        */

        if ($request->filled('location')) {
            $location = substr(trim((string) $request->input('location')), 0, 100);

            $query->where('location', 'like', "%{$location}%");
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination (Hard Limited)
        |--------------------------------------------------------------------------
        */

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 50));

        $jobs = $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function (JobVacancy $job) {
                return [
                    'id'              => $job->id,
                    'title'           => $job->title,
                    'company_name'    => $job->company?->name,
                    'location'        => $job->location,
                    'employment_type' => $job->employment_type,
                    'published_at'    => $job->published_at,
                    'poster_url'      => $job->poster_url,
                ];
            });

        return response()->json($jobs);
    }

    /*
    |--------------------------------------------------------------------------
    | Show Published Job (FIXED: no data leak)
    |--------------------------------------------------------------------------
    */

    public function show(int $id): JsonResponse
    {
        $job = JobVacancy::published()
            ->with('company:id,name')
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'id'                  => $job->id,
                'title'               => $job->title,
                'company_name'        => $job->company?->name,
                'location'            => $job->location,
                'employment_type'     => $job->employment_type,
                'description'         => $job->description,
                'external_apply_url'  => $job->external_apply_url,
                'published_at'        => $job->published_at,
                'expired_at'          => $job->expired_at,
                'poster_url'          => $job->poster_url,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Apply (Click Logging + Redirect URL)
    |--------------------------------------------------------------------------
    | IMPORTANT: Use throttle middleware on route!
    |--------------------------------------------------------------------------
    */

    public function apply(Request $request, int $id): JsonResponse
    {
        $job = JobVacancy::published()->findOrFail($id);

        $userId = optional($request->user())->id;
        $ip = $request->ip();

        /*
        |--------------------------------------------------------------------------
        | Basic Dedup (Prevent Spam Click)
        | Strategy:
        | - Same user OR same IP
        | - Within last 10 seconds
        |--------------------------------------------------------------------------
        */

        $alreadyLogged = $job->applicationLogs()
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn($q) => $q->where('ip_address', $ip))
            ->where('clicked_at', '>=', now()->subSeconds(10))
            ->exists();

        if (! $alreadyLogged) {
            $job->applicationLogs()->create([
                'user_id'    => $userId,
                'clicked_at' => now(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'ip_address' => $ip,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Basic URL Safety Check (defensive layer)
        |--------------------------------------------------------------------------
        */

        $url = $job->external_apply_url;

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json([
                'message' => 'Invalid application URL.'
            ], 422);
        }

        return response()->json([
            'redirect_url' => $url,
        ]);
    }
}
