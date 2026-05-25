@php
    /** @var \App\Models\JobApplication $application */
    $logs = $application->statusLogs->sortByDesc('created_at');
@endphp

<div class="space-y-5">
    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950">
        <div class="text-sm text-gray-500">{{ $application->jobVacancy?->company?->name ?? 'Independent' }}</div>
        <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $application->jobVacancy?->title }}</div>
        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Current status: <span class="font-medium text-gray-950 dark:text-white">{{ $application->status?->label() ?? 'Pending' }}</span>
        </div>
        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Reviewed by: <span class="font-medium text-gray-950 dark:text-white">{{ $application->reviewedBy?->name ?? '—' }}</span>
        </div>
        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Reviewed at: <span class="font-medium text-gray-950 dark:text-white">{{ optional($application->reviewed_at)->format('d M Y H:i') ?? '—' }}</span>
        </div>
        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Internal note: <span class="font-medium text-gray-950 dark:text-white">{{ $application->internal_note ?: '—' }}</span>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($logs as $log)
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm font-semibold text-gray-950 dark:text-white">
                        {{ $log->from_status?->label() ?? 'Submitted' }} → {{ $log->to_status?->label() ?? 'Pending' }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ optional($log->created_at)->format('d M Y H:i') }}
                    </div>
                </div>

                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Changed by: <span class="font-medium text-gray-950 dark:text-white">{{ $log->changedBy?->name ?? 'System' }}</span>
                </div>

                @if(filled($log->note))
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Note: <span class="font-medium text-gray-950 dark:text-white">{{ $log->note }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-950">
                No status history yet.
            </div>
        @endforelse
    </div>
</div>
