<x-filament::page>
    @php
        $perPage = (int) request('per_page', 12);
        $jobs = $this->getTableQuery()->paginate($perPage)->withQueryString();
        $filtersActive = filled(request('search')) || filled(request('employment_type')) || filled(request('location'));
        $searchValue = (string) request('search', '');
        $employmentTypeValue = (string) request('employment_type', '');
        $locationValue = (string) request('location', '');

        $employmentTypeLabel = match ($employmentTypeValue) {
            'fulltime' => 'Full-time',
            'parttime' => 'Part-time',
            'intern' => 'Intern',
            'remote' => 'Remote',
            default => null,
        };
    @endphp

    <div class="space-y-6">
        <x-filament::section
            heading="Browse jobs"
            description="Search published vacancies and keep the application flow inside the portal."
            :compact="true"
            class="shadow-sm"
        >
            <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_18rem]">
                <form method="GET" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-[minmax(0,1.35fr)_minmax(0,0.95fr)_auto]">
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Search jobs</span>
                            <input
                                name="search"
                                value="{{ $searchValue }}"
                                type="search"
                                placeholder="Search jobs, skills, or companies"
                                class="block w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
                            />
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Employment type</span>
                            <select
                                name="employment_type"
                                class="block w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
                            >
                                <option value="">All types</option>
                                <option value="fulltime" @selected($employmentTypeValue === 'fulltime')>Full-time</option>
                                <option value="parttime" @selected($employmentTypeValue === 'parttime')>Part-time</option>
                                <option value="intern" @selected($employmentTypeValue === 'intern')>Intern</option>
                                <option value="remote" @selected($employmentTypeValue === 'remote')>Remote</option>
                            </select>
                        </label>

                        <div class="flex items-end gap-2">
                            <x-filament::button
                                type="submit"
                                color="primary"
                                size="sm"
                                class="w-full justify-center sm:w-auto"
                            >
                                Search
                            </x-filament::button>

                            <x-filament::button
                                tag="a"
                                href="{{ url('/portal/jobs') }}"
                                color="gray"
                                outlined
                                size="sm"
                                class="w-full justify-center sm:w-auto"
                            >
                                Reset
                            </x-filament::button>
                        </div>
                    </div>

                    @if($filtersActive)
                        <div class="flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-400">
                            @if($searchValue !== '')
                                <x-filament::badge color="warning">
                                    Search: {{ $searchValue }}
                                </x-filament::badge>
                            @endif

                            @if($employmentTypeLabel)
                                <x-filament::badge color="gray">
                                    Type: {{ $employmentTypeLabel }}
                                </x-filament::badge>
                            @endif

                            @if($locationValue !== '')
                                <x-filament::badge color="gray">
                                    Location: {{ $locationValue }}
                                </x-filament::badge>
                            @endif
                        </div>
                    @endif
                </form>

                <aside class="flex h-full flex-col justify-between gap-4 rounded-2xl border border-gray-200 bg-gray-50 p-4 ring-1 ring-inset ring-gray-200 dark:border-gray-800 dark:bg-gray-900/60 dark:ring-gray-800">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Jobs found</p>
                        <p class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $jobs->total() }}</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                            Published vacancies that match the current filters.
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white px-4 py-3 ring-1 ring-inset ring-gray-200 dark:bg-gray-950 dark:ring-gray-800">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Need a clean slate?</p>
                        <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">Clear filters and return to the full job board.</p>
                        <x-filament::button
                            tag="a"
                            href="{{ url('/portal/jobs') }}"
                            color="gray"
                            outlined
                            size="sm"
                            class="mt-3 w-full justify-center"
                        >
                            Reset filters
                        </x-filament::button>
                    </div>
                </aside>
            </div>
        </x-filament::section>

        @if($jobs->count())
            <section aria-label="Available jobs" class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
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
                        $isFeatured = $job->published_at?->diffInDays(now()) <= 3;
                        $isInternship = $type === 'intern';
                        $isRemote = $type === 'remote' || str_contains(strtolower((string) $job->location), 'remote');
                        $deadlineUrgent = $job->expired_at && $job->expired_at->isFuture() && $job->expired_at->diffInDays(now(), false) <= 3;
                    @endphp

                    <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition duration-200 hover:-translate-y-0.5 hover:shadow-md hover:ring-gray-950/10 dark:border-gray-800 dark:bg-gray-950/80 dark:ring-white/10 dark:hover:ring-white/20" data-job-card data-job-id="{{ $job->id }}">
                        <div class="flex items-start gap-4">
                            <div class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-2xl bg-gray-100 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:ring-gray-700" aria-hidden="true">
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
                                    class="flex h-full w-full items-center justify-center text-xs font-semibold tracking-wide text-gray-500"
                                    data-company-avatar-fallback
                                    @if($company?->logo_url) hidden @endif
                                >
                                    {{ $companyInitials }}
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-filament::badge color="gray">
                                        {{ ucfirst($type ?: 'General') }}
                                    </x-filament::badge>

                                    @if($isFeatured)
                                        <x-filament::badge color="warning">Featured</x-filament::badge>
                                    @endif

                                    @if($isInternship)
                                        <x-filament::badge color="info">Internship</x-filament::badge>
                                    @endif

                                    @if($isRemote)
                                        <x-filament::badge color="gray">Remote</x-filament::badge>
                                    @endif

                                    @if($deadlineUrgent)
                                        <x-filament::badge color="danger">Deadline soon</x-filament::badge>
                                    @endif

                                    @if($hasApplied)
                                        <x-filament::badge color="success">
                                            Applied
                                        </x-filament::badge>
                                    @endif
                                </div>

                                <h2 class="mt-3 text-lg font-semibold leading-6 tracking-tight text-gray-950 dark:text-white">
                                    <a href="{{ url('/portal/jobs/show?job=' . $job->id) }}" class="line-clamp-2 transition hover:text-primary-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                                        {{ $job->title }}
                                    </a>
                                </h2>

                                <p class="mt-1 line-clamp-1 text-sm font-medium text-gray-600 dark:text-gray-300">
                                    {{ $companyName }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                            @if($job->location)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-2.5 py-1 ring-1 ring-inset ring-gray-200 dark:bg-gray-900/60 dark:ring-gray-800">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4.5 8-10A8 8 0 004 12c0 5.5 8 10 8 10z"></path>
                                    </svg>
                                    <span class="line-clamp-1">{{ $job->location }}</span>
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-50 px-2.5 py-1 ring-1 ring-inset ring-gray-200 dark:bg-gray-900/60 dark:ring-gray-800">
                                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                                </svg>
                                <span>Posted {{ optional($job->published_at)->diffForHumans() ?? 'recently' }}</span>
                            </span>

                            @if($job->expired_at)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/20">
                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Deadline {{ optional($job->expired_at)->format('d M Y') }}</span>
                                </span>
                            @endif
                        </div>

                        <p class="mt-4 line-clamp-3 text-sm leading-6 text-gray-600 dark:text-gray-300">
                            {{ Str::limit(strip_tags($job->description ?? ''), 180) ?: 'No description available.' }}
                        </p>

                        <div class="mt-auto pt-5">
                            <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                <x-filament::button
                                    tag="a"
                                    href="{{ url('/portal/jobs/show?job=' . $job->id) }}"
                                    color="gray"
                                    outlined
                                    size="sm"
                                    class="w-full justify-center sm:w-auto"
                                >
                                    View details
                                </x-filament::button>

                                <div class="flex min-w-0 flex-col items-stretch gap-2 sm:items-end">
                                    @if($hasApplied)
                                        <x-filament::button
                                            type="button"
                                            color="success"
                                            outlined
                                            size="sm"
                                            disabled
                                            class="w-full justify-center sm:w-auto"
                                        >
                                            Application already submitted
                                        </x-filament::button>
                                    @else
                                        <x-filament::button
                                            type="button"
                                            color="primary"
                                            size="sm"
                                            class="w-full justify-center sm:w-auto"
                                            data-apply-button
                                            data-default-label="Apply now"
                                            data-loading-label="Opening application link..."
                                            data-job-id="{{ $job->id }}"
                                            aria-label="Apply to {{ $job->title }}"
                                        >
                                            Apply now
                                        </x-filament::button>
                                    @endif

                                    <p class="min-h-5 text-right text-xs text-gray-500 dark:text-gray-400" data-apply-feedback aria-live="polite"></p>
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
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 ring-1 ring-inset ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700" aria-hidden="true">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6M9 8h6m-9 8h14M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"></path>
                    </svg>
                </div>

                <h2 class="mt-6 text-xl font-semibold tracking-tight text-gray-950 dark:text-white">No jobs found</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    {{ $filtersActive ? 'Try a broader search or clear the active filters.' : 'New jobs will appear here when employers publish them.' }}
                </p>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <x-filament::button
                        tag="a"
                        href="{{ url('/portal/jobs') }}"
                        color="gray"
                        outlined
                        size="sm"
                    >
                        Reset search
                    </x-filament::button>
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
