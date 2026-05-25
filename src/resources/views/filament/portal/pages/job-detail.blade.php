<x-filament::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Job Detail</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $this->job->title }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->job->company?->name ?? 'Independent' }}
                        @if($this->job->location)
                            <span class="mx-2">•</span>{{ $this->job->location }}
                        @endif
                    </p>
                </div>

                @if($this->myApplication)
                    <div class="rounded-xl border px-4 py-3 text-sm {{ $this->myApplication->status?->badgeClasses() ?? 'border-gray-200 bg-gray-50 text-gray-800 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-200' }}">
                        <p class="text-xs font-medium uppercase tracking-wide opacity-80">Latest application status</p>
                        <p class="mt-1 text-base font-semibold">
                            {{ $this->myApplication->status?->label() ?? 'Pending' }}
                        </p>
                        <p class="mt-1 opacity-80">
                            Applied at {{ optional($this->myApplication->applied_at)->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Description</h2>
                <div class="prose prose-gray mt-4 max-w-none dark:prose-invert">
                    {!! nl2br(e($this->job->description ?? 'No description available.')) !!}
                </div>
            </div>

            <aside class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Quick Facts</h2>

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Employment Type</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            {{ ucfirst($this->job->employment_type ?? '-') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Published At</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            {{ optional($this->job->published_at)->format('d M Y H:i') ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-gray-500">Expires At</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">
                            {{ optional($this->job->expired_at)->format('d M Y H:i') ?? 'No expiration' }}
                        </dd>
                    </div>
                </dl>

                @if(auth()->user()?->cv_url)
                    <a
                        href="{{ auth()->user()->cv_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                    >
                        View Current CV
                    </a>
                @endif

                @if(! $this->myApplication)
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Apply to this job</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
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
                                    class="block w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-950 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 dark:file:bg-white dark:file:text-gray-950"
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
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-gray-400 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                                ></textarea>

                                @error('note')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-lg bg-gray-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                            >
                                Submit Application
                            </button>
                        </form>
                    </div>
                @else
                    <div class="rounded-2xl border p-4 text-sm {{ $this->myApplication->status?->badgeClasses() ?? 'border-gray-200 bg-gray-50 text-gray-800 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-200' }}">
                        <h3 class="font-semibold">Application status</h3>
                        <span class="mt-2 inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $this->myApplication->status?->badgeClasses() ?? 'border-gray-200 bg-gray-50 text-gray-800 dark:border-gray-800 dark:bg-gray-950/40 dark:text-gray-200' }}">
                            {{ $this->myApplication->status?->label() ?? 'Pending' }}
                        </span>
                        <p class="mt-1 opacity-80">
                            Applied at {{ optional($this->myApplication->applied_at)->format('d M Y H:i') ?? '-' }}
                        </p>

                        <a
                            href="{{ url('/portal/applications') }}"
                            class="mt-3 inline-flex items-center justify-center rounded-lg border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-900 transition hover:bg-emerald-100 dark:border-emerald-800 dark:text-emerald-100 dark:hover:bg-emerald-900/40"
                        >
                            View Application History
                        </a>
                    </div>
                @endif

                @if(filter_var($this->job->external_apply_url, FILTER_VALIDATE_URL))
                    <a
                        href="{{ $this->job->external_apply_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                    >
                        Open Employer Site
                    </a>
                @endif

                <a
                    href="{{ url('/portal/jobs') }}"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                >
                    Back to Jobs
                </a>
            </aside>
        </div>
    </div>
</x-filament::page>
