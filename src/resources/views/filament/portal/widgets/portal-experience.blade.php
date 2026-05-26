@php
    /** @var \App\Models\User $user */
    $latestJobs = $latestJobs ?? collect();
    $latestEvents = $latestEvents ?? collect();
    $applications = $applications ?? ['total' => 0, 'pending' => 0, 'reviewed' => 0, 'accepted' => 0, 'rejected' => 0];
    $latestApplication = $latestApplication ?? null;
    $profileCompletion = $profileCompletion ?? 0;
    $missingProfileFields = $missingProfileFields ?? [];
@endphp

<x-filament::section
    heading="Welcome back, {{ $user->name }}"
    description="Your latest jobs, events, and application status in one student-friendly surface."
    :compact="true"
    class="shadow-sm"
>
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        <div class="rounded-3xl border border-gray-200 bg-white p-5 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Portal overview</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        Keep momentum on jobs, events, and applications.
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-300">
                        Check published opportunities, follow your application progress, and keep your profile ready for recruiters.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <x-filament::button tag="a" href="{{ url('/portal/jobs') }}" color="primary" size="sm">Browse jobs</x-filament::button>
                    <x-filament::button tag="a" href="{{ url('/portal/events') }}" color="gray" outlined size="sm">View events</x-filament::button>
                    <x-filament::button tag="a" href="{{ url('/portal/applications') }}" color="gray" outlined size="sm">My applications</x-filament::button>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-4 ring-1 ring-inset ring-gray-200 dark:bg-gray-950 dark:ring-gray-800">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Applications</p>
                    <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $applications['total'] }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Total submissions</p>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-inset ring-gray-200 dark:bg-gray-950 dark:ring-gray-800">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Latest status</p>
                    <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $latestApplication?->status?->label() ?? 'None' }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $latestApplication ? optional($latestApplication->applied_at)->format('d M Y') : 'Submit your first application' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-4 ring-1 ring-inset ring-gray-200 dark:bg-gray-950 dark:ring-gray-800">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Profile completion</p>
                    <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $profileCompletion }}%</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Keep your CV and profile current</p>
                </div>
            </div>
        </div>

        <div class="space-y-4 rounded-3xl border border-gray-200 bg-white p-5 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Profile reminder</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">Your profile is {{ $profileCompletion }}% complete</h3>
                </div>

                <x-filament::badge :color="$profileCompletion >= 80 ? 'success' : 'warning'">
                    {{ $profileCompletion >= 80 ? 'Almost ready' : 'Needs attention' }}
                </x-filament::badge>
            </div>

            <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                <div class="h-full rounded-full bg-primary-500" style="width: {{ $profileCompletion }}%"></div>
            </div>

            <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
                {{ filled($missingProfileFields) ? 'Missing: ' . implode(', ', $missingProfileFields) : 'Your core profile fields are complete. Keep your CV ready for faster applications.' }}
            </p>

            <div class="flex flex-wrap gap-2">
                @foreach($missingProfileFields as $field)
                    <x-filament::badge color="gray">
                        {{ $field }}
                    </x-filament::badge>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950 dark:ring-gray-800">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pending</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $applications['pending'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Waiting for review</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950 dark:ring-gray-800">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Reviewed</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $applications['reviewed'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">In recruiter queue</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950 dark:ring-gray-800">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Accepted</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $applications['accepted'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Positive outcomes</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950 dark:ring-gray-800">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Rejected</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $applications['rejected'] }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Closed applications</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <section class="rounded-3xl border border-gray-200 bg-white p-5 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Latest jobs</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">Fresh opportunities</h3>
                </div>

                <x-filament::button tag="a" href="{{ url('/portal/jobs') }}" color="gray" outlined size="sm">View all</x-filament::button>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($latestJobs as $job)
                    @php
                        $isFeatured = $job->published_at?->diffInDays(now()) <= 3;
                        $isInternship = $job->employment_type === 'intern';
                        $isRemote = $job->employment_type === 'remote' || str_contains(strtolower((string) $job->location), 'remote');
                        $deadlineSoon = $job->expired_at && $job->expired_at->diffInDays(now(), false) <= 3 && $job->expired_at->isFuture();
                    @endphp

                    <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 transition hover:bg-white dark:border-gray-800 dark:bg-gray-900/40 dark:hover:bg-gray-900">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs font-semibold text-gray-500 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                                {{ strtoupper(substr($job->company?->name ?? 'IN', 0, 2)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    @if($isFeatured)
                                        <x-filament::badge color="warning">Featured</x-filament::badge>
                                    @endif
                                    @if($isInternship)
                                        <x-filament::badge color="info">Internship</x-filament::badge>
                                    @endif
                                    @if($isRemote)
                                        <x-filament::badge color="gray">Remote</x-filament::badge>
                                    @endif
                                    @if($deadlineSoon)
                                        <x-filament::badge color="danger">Deadline soon</x-filament::badge>
                                    @endif
                                </div>

                                <h4 class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-gray-950 dark:text-white">
                                    <a href="{{ url('/portal/jobs/show?job=' . $job->id) }}" class="transition hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                                        {{ $job->title }}
                                    </a>
                                </h4>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $job->company?->name ?? 'Independent' }} • {{ $job->location ?? 'Location flexible' }}
                                </p>
                            </div>

                            <x-filament::button tag="a" href="{{ url('/portal/jobs/show?job=' . $job->id) }}" color="gray" outlined size="sm">Open</x-filament::button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-950">
                        No jobs published yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-5 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Latest events</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">Events worth checking</h3>
                </div>

                <x-filament::button tag="a" href="{{ url('/portal/events') }}" color="gray" outlined size="sm">View all</x-filament::button>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($latestEvents as $event)
                    <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4 transition hover:bg-white dark:border-gray-800 dark:bg-gray-900/40 dark:hover:bg-gray-900">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs font-semibold text-gray-500 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                                {{ strtoupper(substr($event->company?->name ?? 'EV', 0, 2)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    <x-filament::badge color="gray">{{ ucfirst($event->event_type ?? 'event') }}</x-filament::badge>
                                    @if($event->registration_deadline?->isFuture())
                                        <x-filament::badge color="warning">Deadline {{ $event->registration_deadline->format('d M') }}</x-filament::badge>
                                    @endif
                                </div>

                                <h4 class="mt-2 line-clamp-2 text-sm font-semibold leading-6 text-gray-950 dark:text-white">
                                    <a href="{{ url('/portal/events/show?event=' . $event->id) }}" class="transition hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                                        {{ $event->title }}
                                    </a>
                                </h4>

                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $event->company?->name ?? 'External Organizer' }} • {{ $event->location ?? 'Location flexible' }}
                                </p>
                            </div>

                            <x-filament::button tag="a" href="{{ url('/portal/events/show?event=' . $event->id) }}" color="gray" outlined size="sm">Open</x-filament::button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-950">
                        No events published yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament::section>
