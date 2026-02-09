<x-filament::page>
    <x-filament::section columns="3" class="grid-cols-1 md:grid-cols-3 gap-y-10 gap-x-8">
        {{-- Announcements --}}
        <x-filament::card>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">
                Announcements
            </h2>

            <div class="flex items-end gap-4 mb-3">

                <p class="text-sm text-gray-500 pb-1">
                    Total publish : <span class="text-4xl font-bold">{{ $stats['announcements_total'] }}</span>
                </p>
                <p class="text-4xl font-bold">

                </p>
            </div>

            <p class="text-sm text-gray-400">
                Total views : <span class="font-medium text-gray-200">{{ $stats['announcement_views'] }}</span>
            </p>

            @if ($stats['top_announcement'])
                <div class="mt-4 pt-3 border-t border-gray-700">
                    <p class="text-xs text-gray-500 uppercase mb-1">
                        Most viewed announcement: <span>{{ $stats['top_announcement']->title }}</span>
                        ({{ $stats['top_announcement']->views }} views)
                    </p>
                </div>
            @endif
        </x-filament::card>


        {{-- Events --}}
        <x-filament::card>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">
                Events
            </h2>

            <div class="flex items-end gap-4 mb-3">
                <p class="text-sm text-gray-500 pb-1">
                    Total events :
                    <span class="text-4xl font-bold">
                        {{ $stats['events_total'] }}
                    </span>
                </p>
            </div>

            <p class="text-sm text-gray-400">
                Total registrations :
                <span class="font-medium text-gray-200">
                    {{ $stats['event_registrations'] }}
                </span>
            </p>

            @if ($stats['top_event'])
                <div class="mt-4 pt-3 border-t border-gray-700">
                    <p class="text-xs text-gray-500 uppercase mb-1">
                        Most registered event:
                        <span class="font-medium text-gray-200">
                            {{ $stats['top_event']->title }}
                        </span>
                        ({{ $stats['top_event']->registrations }} registrations)
                    </p>
                </div>
            @endif
        </x-filament::card>

        {{-- Jobs --}}
        <x-filament::card>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-4">
                Jobs
            </h2>

            <div class="flex items-end gap-4 mb-3">
                <p class="text-sm text-gray-500 pb-1">
                    Total jobs :
                    <span class="text-4xl font-bold">
                        {{ $stats['jobs_total'] }}
                    </span>
                </p>
            </div>

            <p class="text-sm text-gray-400">
                Application clicks :
                <span class="font-medium text-gray-200">
                    {{ $stats['job_apply_clicks'] }}
                </span>
            </p>

            @if ($stats['top_job'])
                <div class="mt-4 pt-3 border-t border-gray-700">
                    <p class="text-xs text-gray-500 uppercase mb-1">
                        Most clicked job:
                        <span class="font-medium text-gray-200">
                            {{ $stats['top_job']->title }}
                        </span>
                        ({{ $stats['top_job']->clicks }} clicks)
                    </p>
                </div>
            @endif
        </x-filament::card>
    </x-filament::section>
</x-filament::page>
