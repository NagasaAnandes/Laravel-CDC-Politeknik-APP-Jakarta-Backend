<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EventRegisterRequest;
use App\Models\Event;
use App\Models\EventLog;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * GET /api/v1/events
     * Public event list
     */
    public function index(Request $request)
    {
        $events = Event::query()
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('end_datetime', '>=', now())
            ->withCount('registrations')
            ->orderBy('start_datetime')
            ->get()
            ->map(function (Event $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'event_type' => $event->event_type,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->end_datetime,
                    'location' => $event->location,
                    'registration_method' => $event->registration_method,
                    'is_full' => $event->quota !== null
                        ? $event->registrations_count >= $event->quota
                        : false,
                    'published_at' => $event->published_at,
                ];
            });

        return response()->json([
            'data' => $events,
        ]);
    }

    /**
     * GET /api/v1/events/{id}
     * Public event detail
     */
    public function show(Request $request, Event $event)
    {
        if (
            ! $event->is_active ||
            $event->published_at === null ||
            $event->end_datetime < now()
        ) {
            abort(404);
        }

        // Log event view (guest allowed)
        EventLog::create([
            'event_id'   => $event->id,
            'user_id'    => optional($request->user())->id,
            'action'     => 'view',
            'created_at' => now(),
        ]);

        $registrationsCount = $event->registrations()->count();

        return response()->json([
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'event_type' => $event->event_type,
                'organizer' => $event->organizer,
                'location' => $event->location,
                'start_datetime' => $event->start_datetime,
                'end_datetime' => $event->end_datetime,
                'registration_method' => $event->registration_method,
                'registration_url' => $event->registration_method === 'redirect'
                    ? $event->registration_url
                    : null,
                'quota' => $event->quota,
                'is_full' => $event->quota !== null
                    ? $registrationsCount >= $event->quota
                    : false,
                'published_at' => $event->published_at,
            ],
        ]);
    }

    /**
     * POST /api/v1/events/{id}/register
     * Auth required
     */
    public function register(EventRegisterRequest $request, Event $event)
    {
        // Guard: event must be public & active
        if (
            ! $event->is_active ||
            $event->published_at === null ||
            $event->end_datetime < now()
        ) {
            abort(404);
        }

        // Redirect-only event
        if ($event->registration_method === 'redirect') {
            EventLog::create([
                'event_id'   => $event->id,
                'user_id'    => $request->user()->id,
                'action'     => 'redirect_register',
                'created_at' => now(),
            ]);

            return response()->json([
                'message' => 'Redirect to external registration',
                'redirect_url' => $event->registration_url,
            ]);
        }

        // Prevent duplicate registration
        $alreadyRegistered = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'message' => 'You are already registered for this event',
            ], 409);
        }

        // Quota check
        if ($event->quota !== null) {
            $registeredCount = EventRegistration::where('event_id', $event->id)->count();

            if ($registeredCount >= $event->quota) {
                return response()->json([
                    'message' => 'Event quota is full',
                ], 422);
            }
        }

        // Single source of truth for time
        $registeredAt = now();

        DB::transaction(function () use ($request, $event, $registeredAt) {
            EventRegistration::create([
                'event_id'            => $event->id,
                'user_id'             => $request->user()->id,
                'registration_status' => 'registered',
                'registered_at'       => $registeredAt,
            ]);

            EventLog::create([
                'event_id'   => $event->id,
                'user_id'    => $request->user()->id,
                'action'     => 'register',
                'created_at' => $registeredAt,
            ]);
        });

        return response()->json([
            'message' => 'Successfully registered for event',
            'registered_at' => $registeredAt,
        ]);
    }

    public function myEvents(Request $request)
    {
        $user = $request->user();

        // Guard role (defensive, walau sudah di request)
        if (! in_array($user->role->value, ['student', 'alumni'])) {
            abort(403);
        }

        $registrations = EventRegistration::query()
            ->where('user_id', $user->id)
            ->with('event')
            ->orderByDesc('registered_at')
            ->get()
            ->map(function (EventRegistration $registration) {
                $event = $registration->event;

                return [
                    'event_id' => $event->id,
                    'title' => $event->title,
                    'event_type' => $event->event_type,
                    'start_datetime' => $event->start_datetime,
                    'end_datetime' => $event->end_datetime,
                    'location' => $event->location,
                    'registration_status' => $registration->registration_status,
                    'registered_at' => $registration->registered_at,
                ];
            });

        return response()->json([
            'data' => $registrations,
        ]);
    }
}
