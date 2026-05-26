<x-filament::page>
    <div class="space-y-6">
        <x-filament::section
            heading="Event detail"
            description="Review the event details and register without leaving the portal when possible."
            :compact="true"
            class="shadow-sm"
        >
            <div class="flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-5 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge color="gray">{{ ucfirst($this->event->event_type ?? 'Event') }}</x-filament::badge>
                        @if($this->event->registration_deadline)
                            <x-filament::badge color="warning">Deadline {{ optional($this->event->registration_deadline)->format('d M Y') }}</x-filament::badge>
                        @endif
                        <x-filament::badge color="info">
                            {{ $this->event->registration_method === 'redirect' ? 'External registration' : 'Internal registration' }}
                        </x-filament::badge>
                    </div>

                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $this->event->title }}
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $this->event->company?->name ?? 'External Organizer' }}
                        @if($this->event->location)
                            <span class="mx-2">•</span>{{ $this->event->location }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($this->event->registration_method === 'redirect' && filter_var($this->event->registration_url, FILTER_VALIDATE_URL))
                        <x-filament::button
                            tag="a"
                            href="{{ $this->event->registration_url }}"
                            target="_blank"
                            color="primary"
                            size="sm"
                            class="w-full justify-center sm:w-auto"
                        >
                            Register Now
                        </x-filament::button>
                    @elseif($this->event->registration_method === 'internal' && $this->event->isRegistrationOpen() && ! $this->event->isQuotaFull())
                        <x-filament::button
                            type="button"
                            wire:click="registerEvent"
                            wire:loading.attr="disabled"
                            color="primary"
                            size="sm"
                            class="w-full justify-center sm:w-auto"
                        >
                            Register Now
                        </x-filament::button>
                    @endif

                    <x-filament::button tag="a" href="{{ url('/portal/events') }}" color="gray" outlined size="sm" class="w-full justify-center sm:w-auto">
                        Back to Events
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <x-filament::section heading="Description" :compact="true" class="shadow-sm">
                <div class="prose prose-gray max-w-none dark:prose-invert">
                    {!! nl2br(e($this->event->description ?? 'No description available.')) !!}
                </div>
            </x-filament::section>

            <aside class="space-y-4 lg:sticky lg:top-24">
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Quick facts</h2>

                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Event Type</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                                {{ ucfirst($this->event->event_type ?? '-') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Registration Deadline</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                                {{ optional($this->event->registration_deadline)->format('d M Y') ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Registrations</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                                {{ $this->event->registrations_count ?? 0 }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Registration Method</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                                {{ ucfirst($this->event->registration_method ?? '-') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Registration Status</dt>
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
                </div>

                <div class="sticky bottom-0 z-20 rounded-3xl border border-gray-200 bg-white/95 p-4 shadow-lg shadow-gray-950/5 backdrop-blur dark:border-gray-800 dark:bg-gray-950/95 lg:hidden">
                    @if($this->event->registration_method === 'redirect' && filter_var($this->event->registration_url, FILTER_VALIDATE_URL))
                        <x-filament::button
                            tag="a"
                            href="{{ $this->event->registration_url }}"
                            target="_blank"
                            color="primary"
                            size="sm"
                            class="w-full justify-center"
                        >
                            Register Now
                        </x-filament::button>
                    @elseif($this->event->registration_method === 'internal' && $this->event->isRegistrationOpen() && ! $this->event->isQuotaFull())
                        <x-filament::button
                            type="button"
                            wire:click="registerEvent"
                            wire:loading.attr="disabled"
                            color="primary"
                            size="sm"
                            class="w-full justify-center"
                        >
                            Register Now
                        </x-filament::button>
                    @else
                        <x-filament::button tag="a" href="{{ url('/portal/events') }}" color="gray" outlined size="sm" class="w-full justify-center">
                            Back to Events
                        </x-filament::button>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-filament::page>
