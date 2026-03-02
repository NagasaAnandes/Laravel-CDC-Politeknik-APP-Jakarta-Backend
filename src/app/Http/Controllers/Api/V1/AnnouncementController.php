<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementView;
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
        $query = Announcement::published();
        $user = $request->user();

        $query->where(function ($q) use ($user) {

            if (! $user) {
                // Guest hanya boleh lihat announcement untuk all
                $q->where('target_audience', 'all');
                return;
            }

            $role = $user->role->value; // student / alumni

            $q->where('target_audience', 'all')
                ->orWhere('target_audience', $role);
        });

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        $announcements = $query
            ->orderByDesc('published_at')
            ->get()
            ->map(fn(Announcement $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'priority' => $a->priority,
                'target_audience' => $a->target_audience,
                'published_at' => $a->published_at,
            ]);

        return response()->json(['data' => $announcements]);
    }

    /**
     * GET /api/v1/announcements/{id}
     * Public announcement detail
     */
    public function show(Request $request, Announcement $announcement)
    {
        // 1️⃣ Lifecycle guard
        if (! $announcement->isPublished()) {
            abort(404);
        }

        // 2️⃣ Audience guard
        $user = $request->user();

        if ($announcement->target_audience !== 'all') {

            if (! $user) {
                abort(404);
            }

            if ($announcement->target_audience !== $user->role->value) {
                abort(404);
            }
        }

        // 3️⃣ Log view
        AnnouncementView::create([
            'announcement_id' => $announcement->id,
            'user_id'         => optional($user)->id,
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
