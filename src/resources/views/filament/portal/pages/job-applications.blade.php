<x-filament::page>
    <div class="space-y-6">
        <x-filament::section
            heading="My applications"
            description="Track application status, review history, and recruiter feedback in one place."
            :compact="true"
            class="shadow-sm"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Keep an eye on each submission while the portal keeps the rest of the workflow stable.
                </p>

                <x-filament::button tag="a" href="{{ url('/portal/jobs') }}" color="gray" outlined size="sm">
                    Browse Jobs
                </x-filament::button>
            </div>
        </x-filament::section>

        <div class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950 dark:ring-gray-800">
            {{ $this->table }}
        </div>
    </div>
</x-filament::page>
