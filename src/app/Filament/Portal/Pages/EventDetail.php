<?php

namespace App\Filament\Portal\Pages;

use App\Models\EventLog;
use App\Models\EventRegistration;
use App\Models\Event;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class EventDetail extends Page
{
    protected string $view = 'filament.portal.pages.event-detail';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'events/show';

    public Event $event;

    protected function resolveEvent(int $eventId): Event
    {
        /** @var Event $event */
        $event = Event::published()
            ->with(['company:id,name'])
            ->withCount('registrations')
            ->findOrFail($eventId);

        return $event;
    }

    public function registerEvent()
    {
        if (! $this->event->isPublished()) {
            abort(404);
        }

        if ($this->event->registration_method === 'redirect') {

            if (! filter_var($this->event->registration_url, FILTER_VALIDATE_URL)) {
                Notification::make()
                    ->title('Invalid registration URL')
                    ->danger()
                    ->send();

                return null;
            }

            EventLog::create([
                'event_id' => $this->event->id,
                'user_id' => auth()->id(),
                'action' => EventLog::ACTION_REDIRECT_REGISTER,
            ]);

            return redirect()->away($this->event->registration_url);
        }

        if (! $this->event->canRegister()) {
            Notification::make()
                ->title('Registration is not allowed')
                ->danger()
                ->send();

            return null;
        }

        try {
            DB::transaction(function () {
                $locked = Event::whereKey($this->event->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $locked->canRegister()) {
                    abort(422, 'Registration is not allowed.');
                }

                EventRegistration::create([
                    'event_id' => $locked->id,
                    'user_id' => auth()->id(),
                    'registered_at' => now(),
                ]);

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
            });
        } catch (QueryException) {
            Notification::make()
                ->title('You are already registered')
                ->warning()
                ->send();

            return null;
        }

        EventLog::create([
            'event_id' => $this->event->id,
            'user_id' => auth()->id(),
            'action' => EventLog::ACTION_REGISTER,
        ]);

        $this->event->refresh();

        Notification::make()
            ->title('Successfully registered')
            ->success()
            ->send();

        return null;
    }

    public function mount(Request $request): void
    {
        $eventId = $request->integer('event');

        if ($eventId < 1) {
            abort(404);
        }

        $this->event = $this->resolveEvent($eventId);
    }

    public function getTitle(): string
    {
        return 'Event Detail';
    }
}
