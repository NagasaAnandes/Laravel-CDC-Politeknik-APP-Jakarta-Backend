<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        $jobs = JobVacancy::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('expired_at')
                    ->orWhere('expired_at', '>=', today());
            })
            ->orderByDesc('published_at')
            ->get([
                'id',
                'title',
                'company_name',
                'location',
                'employment_type',
                'published_at',
            ]);

        return response()->json([
            'data' => $jobs,
        ]);
    }

    public function show(JobVacancy $job)
    {
        if (
            ! $job->is_active ||
            ! $job->published_at ||
            $job->published_at->isFuture() ||
            ($job->expired_at && $job->expired_at->isPast())
        ) {
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
            ],
        ]);
    }


    public function apply(Request $request, JobVacancy $job)
    {
        if (
            ! $job->is_active ||
            ! $job->published_at ||
            $job->published_at->isFuture() ||
            ($job->expired_at && $job->expired_at->isPast())
        ) {
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
