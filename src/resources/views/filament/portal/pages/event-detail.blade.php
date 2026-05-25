<x-filament::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Event Detail</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $this->event->title }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->event->company?->name ?? 'External Organizer' }}
                        @if($this->event->location)
                            <span class="mx-2">•</span>{{ $this->event->location }}
                        @endif
                    </p>
                </div>

                @if($this->event->registration_method === 'redirect' && filter_var($this->event->registration_url, FILTER_VALIDATE_URL))
                    <a
                        href="{{ $this->event->registration_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                    >
                        Register Now
                    </a>
                @elseif($this->event->registration_method === 'internal' && $this->event->isRegistrationOpen() && ! $this->event->isQuotaFull())
                    <button
                        type="button"
                        wire:click="registerEvent"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                    >
                        Register Now
                    </button>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Description</h2>
                <div class="prose prose-gray mt-4 max-w-none dark:prose-invert">
                    {!! nl2br(e($this->event->description ?? 'No description available.')) !!}
                </div>
            </div>

            <aside class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Quick Facts</h2>

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Event Type</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            {{ ucfirst($this->event->event_type ?? '-') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Registration Deadline</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            {{ optional($this->event->registration_deadline)->format('d M Y') ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Registrations</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            {{ $this->event->registrations_count ?? 0 }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Registration Method</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            {{ ucfirst($this->event->registration_method ?? '-') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Registration Status</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            @if($this->event->registration_method === 'redirect')
                                External Registration
                            @elseif($this->event->isRegistrationOpen() && ! $this->event->isQuotaFull())
                                Open
                            @elseif($this->event->isQuotaFull())
                                Full
                            @else
                                Closed
                            @endif
                        </dd>
                    </div>
                </dl>

                <a
                    href="{{ url('/portal/events') }}"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                >
                    Back to Events
                </a>
            </aside>
        </div>
    </div>
</x-filament::page>
