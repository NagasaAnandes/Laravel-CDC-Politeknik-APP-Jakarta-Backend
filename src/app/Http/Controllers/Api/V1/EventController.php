<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\EventRegisterRequest;
use App\Models\Event;
use App\Models\EventLog;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class EventController extends Controller
{
    /**
     * GET /api/v1/events
     */
    public function index(Request $request)
    {
        $query = Event::published()
            ->withCount('registrations');

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('organizer', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->string('event_type'));
        }

        if ($request->boolean('upcoming')) {
            $query->where('registration_deadline', '>=', now());
        }

        $events = $query
            ->orderBy('registration_deadline')
            ->paginate(20)
            ->through(function (Event $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'event_type' => $event->event_type,
                    'poster_url' => $event->poster_url,
                    'registration_deadline' => $event->registration_deadline,
                    'is_registration_open' => $event->isRegistrationOpen(),
                    'location' => $event->location,
                    'registration_method' => $event->registration_method,
                    'is_full' => $event->quota !== null
                        ? $event->registrations_count >= $event->quota
                        : false,
                    'published_at' => $event->published_at,
                ];
            });

        return response()->json($events);
    }

    /**
     * GET /api/v1/events/{event}
     */
    public function show(Request $request, Event $event)
    {
        if (! $event->isPublished()) {
            abort(404);
        }

        DB::afterCommit(function () use ($event, $request) {
            EventLog::create([
                'event_id'   => $event->id,
                'user_id'    => optional($request->user())->id,
                'action'     => 'view',
                'created_at' => now(),
            ]);
        });

        $registrationsCount = $event->registrations()->count();

        return response()->json([
            'data' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'event_type' => $event->event_type,
                'poster_url' => $event->poster_url,
                'organizer' => $event->organizer,
                'location' => $event->location,
                'registration_deadline' => $event->registration_deadline,
                'is_registration_open' => $event->isRegistrationOpen(),
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
     * POST /api/v1/events/{event}/register
     */
    public function register(EventRegisterRequest $request, Event $event)
    {
        if (! $event->isPublished()) {
            abort(404);
        }

        if ($event->registration_method === 'redirect') {

            DB::afterCommit(function () use ($event, $request) {
                EventLog::create([
                    'event_id' => $event->id,
                    'user_id'  => $request->user()->id,
                    'action'   => 'redirect_register',
                ]);
            });

            return response()->json([
                'message'      => 'Redirect to external registration',
                'redirect_url' => $event->registration_url,
            ]);
        }

        if (! $event->canRegister()) {
            abort(422, 'Registration is not allowed.');
        }

        $registeredAt = now();

        DB::transaction(function () use ($request, $event, $registeredAt) {

            $locked = Event::whereKey($event->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->canRegister()) {
                abort(422, 'Registration is not allowed.');
            }

            // ✅ Insert dulu (handle duplicate safely)
            try {
                EventRegistration::create([
                    'event_id'      => $locked->id,
                    'user_id'       => $request->user()->id,
                    'registered_at' => $registeredAt,
                ]);
            } catch (QueryException $e) {
                abort(409, 'Already registered.');
            }

            // ✅ Atomic quota update (anti race condition)
            if ($locked->quota !== null) {
                $updated = Event::whereKey($locked->id)
                    ->whereColumn('registrations_count', '<', 'quota')
                    ->update([
                        'registrations_count' => DB::raw('registrations_count + 1'),
                    ]);

                if (! $updated) {
                    abort(422, 'Event quota is full.');
                }
            }

            // ✅ Logging after commit (safe)
            DB::afterCommit(function () use ($locked, $request) {
                EventLog::create([
                    'event_id' => $locked->id,
                    'user_id'  => $request->user()->id,
                    'action'   => 'register',
                ]);
            });
        });

        return response()->json([
            'message'       => 'Successfully registered',
            'registered_at' => $registeredAt,
        ]);
    }

    /**
     * GET /api/v1/events/my
     */
    public function myEvents(Request $request)
    {
        $user = $request->user();

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

                if (! $event) {
                    return null;
                }

                return [
                    'event_id' => $event->id,
                    'title' => $event->title,
                    'poster_url' => $event->poster_url,
                    'event_type' => $event->event_type,
                    'registration_deadline' => $event->registration_deadline,
                    'is_registration_open' => $event->isRegistrationOpen(),
                    'location' => $event->location,
                    'registered_at' => $registration->registered_at,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'data' => $registrations,
        ]);
    }
}
