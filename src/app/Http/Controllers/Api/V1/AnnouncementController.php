<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    /**
     * GET /api/v1/announcements
     * Public announcement list
     */
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where(function ($q) {
                $q->whereNull('expired_at')
                    ->orWhere('expired_at', '>=', now());
            })
            ->orderByDesc('published_at')
            ->get()
            ->map(function (Announcement $announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'category' => $announcement->category,
                    'priority' => $announcement->priority,
                    'target_audience' => $announcement->target_audience,
                    'published_at' => $announcement->published_at,
                ];
            });

        return response()->json([
            'data' => $announcements,
        ]);
    }

    /**
     * GET /api/v1/announcements/{id}
     * Public announcement detail
     */
    public function show(Request $request, Announcement $announcement)
    {
        // Guard publik (WAJIB)
        if (
            ! $announcement->is_active ||
            $announcement->published_at === null ||
            ($announcement->expired_at !== null && $announcement->expired_at < now())
        ) {
            abort(404);
        }

        // Log announcement view (guest allowed)
        DB::table('announcement_views')->insert([
            'announcement_id' => $announcement->id,
            'user_id'         => optional($request->user())->id,
            'viewed_at'       => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'content' => $announcement->content,
                'category' => $announcement->category,
                'priority' => $announcement->priority,
                'target_audience' => $announcement->target_audience,
                'redirect_url' => $announcement->redirect_url,
                'published_at' => $announcement->published_at,
            ],
        ]);
    }
}
