<x-filament::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Career Management</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        My Applications
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Track application status and review history in one place.
                    </p>
                </div>

                <a
                    href="{{ url('/portal/jobs') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                >
                    Browse Jobs
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            {{ $this->table }}
        </div>
    </div>
</x-filament::page>
