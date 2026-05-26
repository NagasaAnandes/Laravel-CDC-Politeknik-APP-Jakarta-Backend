@php
    /** @var \App\Models\JobApplication $application */
    $logs = $application->statusLogs->sortByDesc('created_at');
@endphp

<div class="space-y-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Application timeline</p>
                <h3 class="mt-1 text-lg font-semibold tracking-tight text-gray-950 dark:text-white">{{ $application->jobVacancy?->title }}</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $application->jobVacancy?->company?->name ?? 'Independent' }}</p>
            </div>

            <x-filament::badge :color="$application->status?->dashboardColor() ?? 'gray'">
                {{ $application->status?->label() ?? 'Pending' }}
            </x-filament::badge>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-900/40">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Reviewed by</p>
                <p class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ $application->reviewedBy?->name ?? '—' }}</p>
            </div>

            <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-900/40">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Reviewed at</p>
                <p class="mt-1 text-sm font-medium text-gray-950 dark:text-white">{{ optional($application->reviewed_at)->format('d M Y H:i') ?? '—' }}</p>
            </div>

            <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-900/40">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Feedback</p>
                <p class="mt-1 text-sm font-medium text-gray-950 dark:text-white">
                    {{ $application->status?->isRejected() ? 'Review the job detail for next steps.' : 'No public feedback yet.' }}
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($logs as $log)
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $log->from_status?->label() ?? 'Submitted' }} → {{ $log->to_status?->label() ?? 'Pending' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ optional($log->created_at)->format('d M Y H:i') }}
                    </div>
                </div>

                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Changed by: <span class="font-medium text-gray-950 dark:text-white">{{ $log->changedBy?->name ?? 'System' }}</span>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-950">
                No status history yet.
            </div>
        @endforelse
    </div>
</div>
