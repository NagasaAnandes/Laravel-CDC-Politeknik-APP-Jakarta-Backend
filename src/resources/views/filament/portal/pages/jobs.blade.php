<x-filament::page>
    @php
        $perPage = (int) request('per_page', 12);
        $jobs = $this->getTableQuery()->paginate($perPage)->withQueryString();
        $filtersActive = filled(request('search')) || filled(request('employment_type')) || filled(request('location'));
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl border border-gray-200 bg-white/95 p-4 shadow-sm shadow-gray-950/5 backdrop-blur dark:border-gray-800 dark:bg-gray-950/80">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <form method="GET" class="w-full lg:max-w-4xl">
                    <div class="grid gap-4 md:grid-cols-[minmax(0,1.4fr)_minmax(0,0.9fr)_auto]">
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Search jobs</span>
                            <input
                                name="search"
                                value="{{ request('search') }}"
                                type="search"
                                placeholder="Search jobs, skills, or companies"
                                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
                            />
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Employment type</span>
                            <select
                                name="employment_type"
                                class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
                            >
                                <option value="">All types</option>
                                <option value="fulltime" {{ request('employment_type') === 'fulltime' ? 'selected' : '' }}>Full-time</option>
                                <option value="parttime" {{ request('employment_type') === 'parttime' ? 'selected' : '' }}>Part-time</option>
                                <option value="intern" {{ request('employment_type') === 'intern' ? 'selected' : '' }}>Intern</option>
                                <option value="remote" {{ request('employment_type') === 'remote' ? 'selected' : '' }}>Remote</option>
                            </select>
                        </label>

                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-gray-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                            >
                                Search
                            </button>
                            <a
                                href="{{ url('/portal/jobs') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
                            >
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="flex items-center gap-3 rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-600 ring-1 ring-inset ring-gray-200 dark:bg-gray-900/60 dark:text-gray-300 dark:ring-gray-800">
                    <span class="font-medium text-gray-950 dark:text-white">{{ $jobs->total() }}</span>
                    <span>jobs found</span>
                </div>
            </div>

            @if($filtersActive)
                <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-400">
                    @if(request('search'))
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-800 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20">
                            Search: {{ request('search') }}
                        </span>
                    @endif

                    @if(request('employment_type'))
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-medium text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                            Type: {{ ucfirst(request('employment_type')) }}
                        </span>
                    @endif

                    @if(request('location'))
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-medium text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                            Location: {{ request('location') }}
                        </span>
                    @endif
                </div>
            @endif
        </section>

        @if($jobs->count())
            <section aria-label="Available jobs" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($jobs as $job)
                    @php
                        $company = $job->company;
                        $companyName = trim((string) ($company?->name ?? 'Independent'));
                        $companyInitials = collect(explode(' ', $companyName))
                            ->filter()
                            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
                            ->take(2)
                            ->implode('');
                        $companyInitials = $companyInitials !== '' ? $companyInitials : 'IN';
                        $hasApplied = (int) ($job->current_user_application_count ?? 0) > 0;
                        $type = $job->employment_type;
                        $typeClass = match($type) {
                            'fulltime' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-200 dark:ring-blue-500/20',
                            'parttime' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20',
                            'intern' => 'bg-indigo-50 text-indigo-700 ring-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-200 dark:ring-indigo-500/20',
                            'remote' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20',
                            default => 'bg-gray-100 text-gray-700 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700',
                        };
                    @endphp

                    <article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-5 shadow-sm shadow-gray-950/5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-950/80 dark:shadow-black/10" data-job-card data-job-id="{{ $job->id }}">
                        <div class="flex items-start gap-4">
                            <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-gray-100 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:ring-gray-700" aria-hidden="true">
                                @if($company?->logo_url)
                                    <img
                                        src="{{ $company->logo_url }}"
                                        alt="{{ $companyName }} logo"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                        data-company-logo
                                    />
                                @endif

                                <div
                                    class="flex h-full w-full items-center justify-center text-sm font-semibold tracking-wide text-gray-500"
                                    data-company-avatar-fallback
                                    @if($company?->logo_url) hidden @endif
                                >
                                    {{ $companyInitials }}
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $typeClass }}">
                                        {{ ucfirst($type ?: 'General') }}
                                    </span>

                                    @if($hasApplied)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">
                                            Applied
                                        </span>
                                    @endif
                                </div>

                                <h2 class="mt-3 text-lg font-semibold leading-snug text-gray-950 dark:text-white">
                                    <a href="{{ url('/portal/jobs/show?job=' . $job->id) }}" class="line-clamp-2 transition hover:text-amber-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                                        {{ $job->title }}
                                    </a>
                                </h2>

                                <p class="mt-2 line-clamp-1 text-sm font-medium text-gray-600 dark:text-gray-300">
                                    {{ $companyName }}
                                </p>

                                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                                    @if($job->location)
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4.5 8-10A8 8 0 004 12c0 5.5 8 10 8 10z"></path>
                                            </svg>
                                            <span class="line-clamp-1">{{ $job->location }}</span>
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                                        </svg>
                                        <span>Posted {{ optional($job->published_at)->diffForHumans() }}</span>
                                    </span>
                                </div>
                            </div>

                            <div class="shrink-0 text-right text-xs text-gray-500 dark:text-gray-400">
                                <div class="rounded-full bg-gray-100 px-2.5 py-1 font-medium text-gray-600 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                                    {{ optional($job->published_at)->format('d M Y') }}
                                </div>
                            </div>
                        </div>

                        <p class="mt-4 line-clamp-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                            {{ Str::limit(strip_tags($job->description ?? ''), 180) ?: 'No description available.' }}
                        </p>

                        <div class="mt-auto pt-5">
                            <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                <a
                                    href="{{ url('/portal/jobs/show?job=' . $job->id) }}"
                                    class="inline-flex items-center text-sm font-medium text-gray-700 transition hover:text-gray-950 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:text-gray-300 dark:hover:text-white"
                                >
                                    View details
                                </a>

                                <div class="flex min-w-0 flex-col items-stretch gap-2 sm:items-end">
                                    @if($hasApplied)
                                        <span class="inline-flex items-center justify-center rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/20">
                                            Application already submitted
                                        </span>
                                    @else
                                        <button
                                            type="button"
                                            data-apply-button
                                            data-default-label="Apply now"
                                            data-loading-label="Opening application link..."
                                            data-job-id="{{ $job->id }}"
                                            class="inline-flex items-center justify-center rounded-full bg-gray-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200"
                                            aria-label="Apply to {{ $job->title }}"
                                        >
                                            Apply now
                                        </button>
                                    @endif

                                    <p class="min-h-5 text-right text-xs text-gray-500" data-apply-feedback aria-live="polite"></p>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    Showing <span class="font-medium text-gray-900 dark:text-white">{{ $jobs->firstItem() ?? 0 }}-{{ $jobs->lastItem() ?? 0 }}</span>
                    of <span class="font-medium text-gray-900 dark:text-white">{{ $jobs->total() }}</span>
                </div>

                <div class="pagination-wrapper">
                    {{ $jobs->links() }}
                </div>
            </div>
        @else
            <section class="rounded-3xl border border-dashed border-gray-300 bg-white p-10 text-center shadow-sm dark:border-gray-700 dark:bg-gray-950/60">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-300" aria-hidden="true">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6M9 8h6m-9 8h14M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
                    </svg>
                </div>

                <h2 class="mt-6 text-xl font-semibold text-gray-950 dark:text-white">No jobs found</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    {{ $filtersActive ? 'Try a broader search or clear the active filters.' : 'New jobs will appear here when employers publish them.' }}
                </p>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="{{ url('/portal/jobs') }}" class="inline-flex items-center justify-center rounded-2xl bg-gray-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200">
                        Reset search
                    </a>
                </div>
            </section>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-company-logo]').forEach((image) => {
                image.addEventListener('error', () => {
                    image.hidden = true;
                    image.parentElement?.querySelector('[data-company-avatar-fallback]')?.removeAttribute('hidden');
                }, { once: true });
            });
        });

        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-apply-button]');

            if (!button || button.disabled) {
                return;
            }

            const card = button.closest('[data-job-card]');
            const feedback = card?.querySelector('[data-apply-feedback]');
            const jobId = button.dataset.jobId;
            const defaultLabel = button.dataset.defaultLabel || 'Apply now';
            const loadingLabel = button.dataset.loadingLabel || 'Opening application link...';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const setFeedback = (message, isError = false) => {
                if (!feedback) {
                    return;
                }

                feedback.textContent = message;
                feedback.classList.toggle('text-red-600', isError);
                feedback.classList.toggle('text-gray-500', !isError);
            };

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = loadingLabel;
            setFeedback('Preparing application link...');

            try {
                const response = await fetch(`/api/jobs/${jobId}/apply`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({}),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload?.message || 'Unable to open the application link.');
                }

                if (payload?.redirect_url) {
                    setFeedback('Opening the employer application page...');
                    window.setTimeout(() => {
                        window.location.href = payload.redirect_url;
                    }, 180);
                    return;
                }

                throw new Error('Application link is not available right now.');
            } catch (error) {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.textContent = defaultLabel;
                setFeedback(error instanceof Error ? error.message : 'Network error. Please try again.', true);
            }
        });
    </script>
</x-filament::page>
