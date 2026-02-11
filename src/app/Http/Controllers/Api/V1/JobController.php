<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = JobVacancy::published();

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->string('employment_type'));
        }

        if ($request->filled('location')) {
            $query->where('location', $request->string('location'));
        }

        $jobs = $query
            ->orderByDesc('published_at')
            ->get()
            ->map(fn($job) => [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'location' => $job->location,
                'employment_type' => $job->employment_type,
                'published_at' => $job->published_at,
                'poster_url' => $job->poster_url,
            ]);


        return response()->json([
            'data' => $jobs,
        ]);
    }

    public function show(JobVacancy $job)
    {
        if (! $job->isPublished()) {
            return response()->json([
                'message' => 'Job not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'location' => $job->location,
                'employment_type' => $job->employment_type,
                'description' => $job->description,
                'external_apply_url' => $job->external_apply_url,
                'published_at' => $job->published_at,
                'expired_at' => $job->expired_at,
                'poster_url' => $job->poster_url,
            ],
        ]);
    }

    public function apply(Request $request, JobVacancy $job)
    {
        if (! $job->isPublished()) {
            return response()->json([
                'message' => 'Job not available',
            ], 404);
        }

        $job->applicationLogs()->create([
            'user_id' => optional($request->user())->id,
            'clicked_at' => now(),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'redirect_url' => $job->external_apply_url,
        ]);
    }
}
