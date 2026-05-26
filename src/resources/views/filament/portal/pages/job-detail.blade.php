<x-filament::page>
    <div class="space-y-6">
        <x-filament::section
            heading="Job detail"
            description="Review the role, then apply directly from the portal with your primary CV."
            :compact="true"
            class="shadow-sm"
        >
            <div class="flex flex-col gap-4 rounded-3xl border border-gray-200 bg-white p-5 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge color="gray">{{ ucfirst($this->job->employment_type ?? 'General') }}</x-filament::badge>
                        @if($this->job->expired_at)
                            <x-filament::badge color="warning">Deadline {{ optional($this->job->expired_at)->format('d M Y') }}</x-filament::badge>
                        @endif
                        @if($this->myApplication)
                            <x-filament::badge :color="$this->myApplication->status?->dashboardColor() ?? 'gray'">
                                {{ $this->myApplication->status?->label() ?? 'Pending' }}
                            </x-filament::badge>
                        @endif
                    </div>

                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $this->job->title }}
                    </h1>

                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $this->job->company?->name ?? 'Independent' }}
                        @if($this->job->location)
                            <span class="mx-2">•</span>{{ $this->job->location }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-filament::button tag="a" href="#application-panel" color="primary" size="sm" class="w-full justify-center sm:w-auto">
                        {{ $this->myApplication ? 'View application panel' : 'Apply now' }}
                    </x-filament::button>

                    <x-filament::button tag="a" href="{{ url('/portal/jobs') }}" color="gray" outlined size="sm" class="w-full justify-center sm:w-auto">
                        Back to Jobs
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <x-filament::section heading="Description" :compact="true" class="shadow-sm">
                <div class="prose prose-gray max-w-none dark:prose-invert">
                    {!! nl2br(e($this->job->description ?? 'No description available.')) !!}
                </div>
            </x-filament::section>

            <aside class="space-y-4 lg:sticky lg:top-24">
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Quick facts</h2>

                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Employment Type</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                                {{ ucfirst($this->job->employment_type ?? '-') }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Published At</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                                {{ optional($this->job->published_at)->format('d M Y H:i') ?? '-' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Expires At</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                                {{ optional($this->job->expired_at)->format('d M Y H:i') ?? 'No expiration' }}
                            </dd>
                        </div>
                    </dl>

                    @if(auth()->user()?->cv_url)
                        <x-filament::button
                            tag="a"
                            href="{{ auth()->user()->cv_url }}"
                            target="_blank"
                            color="gray"
                            outlined
                            size="sm"
                            class="mt-4 w-full justify-center"
                        >
                            View Current CV
                        </x-filament::button>
                    @endif
                </div>

                <div id="application-panel" class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-gray-800">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                        {{ $this->myApplication ? 'Application status' : 'Apply to this job' }}
                    </h2>

                    @if(! $this->myApplication)
                        <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            Upload a primary CV if you do not have one yet. The portal reuses your user profile CV for future applications.
                        </p>

                        <form wire:submit.prevent="submitApplication" class="mt-4 space-y-4">
                            <div>
                                <label for="cvFile" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    CV file
                                </label>
                                <input
                                    id="cvFile"
                                    type="file"
                                    wire:model="cvFile"
                                    accept=".pdf,.doc,.docx"
                                    class="block w-full rounded-2xl border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-950 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 dark:file:bg-white dark:file:text-gray-950"
                                >

                                @error('cvFile')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="note" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Note
                                </label>
                                <textarea
                                    id="note"
                                    wire:model.defer="note"
                                    rows="4"
                                    placeholder="Optional note for the recruiter"
                                    class="block w-full rounded-2xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                                ></textarea>

                                @error('note')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-filament::button type="submit" color="primary" size="sm" class="w-full justify-center">
                                Submit Application
                            </x-filament::button>
                        </form>
                    @else
                        <div class="mt-4 rounded-2xl border p-4 text-sm {{ $this->myApplication->status?->badgeClasses() ?? 'border-gray-200 bg-gray-50 text-gray-800 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-200' }}">
                            <p class="text-xs font-medium uppercase tracking-wide opacity-80">Latest application status</p>
                            <p class="mt-2 text-base font-semibold">
                                {{ $this->myApplication->status?->label() ?? 'Pending' }}
                            </p>
                            <p class="mt-1 opacity-80">
                                Applied at {{ optional($this->myApplication->applied_at)->format('d M Y H:i') ?? '-' }}
                            </p>

                            <x-filament::button
                                tag="a"
                                href="{{ url('/portal/applications') }}"
                                color="gray"
                                outlined
                                size="sm"
                                class="mt-4 w-full justify-center"
                            >
                                View Application History
                            </x-filament::button>
                        </div>
                    @endif

                    @if(filter_var($this->job->external_apply_url, FILTER_VALIDATE_URL))
                        <x-filament::button
                            tag="a"
                            href="{{ $this->job->external_apply_url }}"
                            target="_blank"
                            color="gray"
                            outlined
                            size="sm"
                            class="mt-4 w-full justify-center"
                        >
                            Open Employer Site
                        </x-filament::button>
                    @endif
                </div>

                <div class="sticky bottom-0 z-20 rounded-3xl border border-gray-200 bg-white/95 p-4 shadow-lg shadow-gray-950/5 backdrop-blur dark:border-gray-800 dark:bg-gray-950/95 lg:hidden">
                    <div class="flex items-center gap-2">
                        <x-filament::button tag="a" href="#application-panel" color="primary" size="sm" class="flex-1 justify-center">
                            {{ $this->myApplication ? 'Application status' : 'Apply now' }}
                        </x-filament::button>

                        <x-filament::button tag="a" href="{{ url('/portal/jobs') }}" color="gray" outlined size="sm" class="flex-1 justify-center">
                            Back
                        </x-filament::button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-filament::page>
