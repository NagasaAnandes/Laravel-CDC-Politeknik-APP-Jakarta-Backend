<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CvDownloadController
{
    /**
     * Download CV associated with a job application.
     * Authorization: uses JobApplicationPolicy@view
     */
    public function download(Request $request, JobApplication $application)
    {
        Gate::authorize('view', $application);

        $user = $application->user;

        if (! $user || ! filled($user->cv_path)) {
            abort(404);
        }

        $path = $user->cv_path;

        // Prefer private disk (staged migration); fall back to public disk.
        $disks = ['private', 'public'];

        foreach ($disks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Log::channel('daily')->info('CV download', [
                        'user_id' => $request->user()?->id,
                        'target_user_id' => $user->id,
                        'application_id' => $application->id,
                        'disk' => $disk,
                        'ip' => $request->ip(),
                    ]);

                    return Storage::disk($disk)->download($path);
                }
            } catch (\Exception $e) {
                // If disk not configured, skip and continue to next
                continue;
            }
        }

        abort(404);
    }
}
