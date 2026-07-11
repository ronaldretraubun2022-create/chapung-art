

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const debounce = (callback, delay = 300) => {
    let timer;

    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => callback(...args), delay);
    };
};

const createResult = (item) => {
    const link = document.createElement('a');
    link.href = item.url;
    link.className = 'grid gap-3 px-4 py-3 transition hover:bg-zinc-900 sm:grid-cols-[3.5rem_1fr]';

    const image = document.createElement('img');
    image.src = item.image;
    image.alt = item.title;
    image.width = 96;
    image.height = 72;
    image.loading = 'lazy';
    image.decoding = 'async';
    image.className = 'hidden aspect-[4/3] h-14 w-14 rounded-md object-cover sm:block';

    const body = document.createElement('span');
    body.className = 'min-w-0';

    const type = document.createElement('span');
    type.className = 'block text-[10px] font-black uppercase tracking-[0.16em] text-yellow-600';
    type.textContent = item.type;

    const title = document.createElement('span');
    title.className = 'mt-1 block truncate text-sm font-black uppercase text-white';
    title.textContent = item.title;

    const subtitle = document.createElement('span');
    subtitle.className = 'mt-1 block truncate text-xs text-zinc-400';
    subtitle.textContent = item.subtitle || '';

    body.append(type, title, subtitle);
    link.append(image, body);

    return link;
};

const initGlobalSearch = () => {
    document.querySelectorAll('[data-global-search]').forEach((form) => {
        const input = form.querySelector('[data-search-input]');
        const panel = form.querySelector('[data-search-panel]');
        const status = form.querySelector('[data-search-status]');
        const results = form.querySelector('[data-search-results]');
        const endpoint = form.dataset.searchUrl;
        const messages = {
            idle: form.dataset.messageIdle || 'Type to search',
            min: form.dataset.messageMin || 'Type at least 2 characters',
            loading: form.dataset.messageLoading || 'Searching',
            empty: form.dataset.messageEmpty || 'No results found',
            error: form.dataset.messageError || 'Search unavailable',
            results: form.dataset.messageResults || 'results',
        };
        let controller;

        if (! input || ! panel || ! status || ! results || ! endpoint) {
            return;
        }

        const showPanel = () => {
            panel.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
        };

        const hidePanel = () => {
            panel.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
        };

        const setStatus = (message) => {
            status.textContent = message;
        };

        const setLoading = (loading) => {
            status.classList.toggle('animate-pulse', loading);
            input.setAttribute('aria-busy', loading ? 'true' : 'false');
        };

        const render = (payload) => {
            results.replaceChildren();
            setLoading(false);

            if (! payload.total) {
                setStatus(payload.query ? messages.empty : messages.idle);
                return;
            }

            setStatus(`${payload.total} ${messages.results}`);

            Object.values(payload.groups).forEach((group) => {
                if (! group.items.length) {
                    return;
                }

                const heading = document.createElement('p');
                heading.className = 'border-y border-zinc-800 bg-black px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500 first:border-t-0';
                heading.textContent = group.label;
                results.append(heading);

                group.items.forEach((item) => results.append(createResult(item)));
            });
        };

        const runSearch = debounce(async () => {
            const query = input.value.trim();

            if (query.length < 2) {
                if (controller) {
                    controller.abort();
                }

                results.replaceChildren();
                setLoading(false);
                setStatus(messages.min);
                showPanel();
                return;
            }

            if (controller) {
                controller.abort();
            }

            controller = new AbortController();
            setLoading(true);
            setStatus(messages.loading);
            showPanel();

            try {
                const url = `${endpoint}?${new URLSearchParams({ q: query })}`;
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                if (! response.ok) {
                    throw new Error('Search request failed.');
                }

                render(await response.json());
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                results.replaceChildren();
                setLoading(false);
                setStatus(messages.error);
            }
        }, 300);

        input.addEventListener('input', runSearch);
        input.addEventListener('focus', () => {
            if (input.value.trim().length) {
                showPanel();
                runSearch();
            }
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                hidePanel();
            }
        });
        document.addEventListener('click', (event) => {
            if (! form.contains(event.target)) {
                hidePanel();
            }
        });
    });
};

const updateFavoriteCounts = (count) => {
    document.querySelectorAll('[data-favorite-count]').forEach((badge) => {
        const nextCount = Number(count) || 0;
        const format = badge.dataset.favoriteCountFormat || 'plain';

        badge.textContent = format === 'paren' ? `(${nextCount})` : `${nextCount}`;
        badge.classList.toggle('hidden', nextCount < 1);
    });
};

const setFavoriteState = (form, favorited) => {
    const button = form.querySelector('[data-favorite-button]');
    const method = form.querySelector('[data-favorite-method]');
    const label = form.querySelector('[data-favorite-label]');
    const addLabel = form.dataset.addLabel || 'Add Favorite';
    const removeLabel = form.dataset.removeLabel || 'Remove Favorite';
    const nextLabel = favorited ? removeLabel : addLabel;

    form.dataset.favorited = favorited ? 'true' : 'false';
    form.action = favorited ? form.dataset.destroyUrl : form.dataset.storeUrl;

    if (method) {
        method.value = favorited ? 'DELETE' : 'POST';
    }

    if (button) {
        button.setAttribute('aria-pressed', favorited ? 'true' : 'false');
        button.setAttribute('aria-label', nextLabel);
        button.classList.toggle('border-yellow-500', favorited);
        button.classList.toggle('bg-yellow-600', favorited);
        button.classList.toggle('text-black', favorited);
    }

    if (label) {
        label.textContent = nextLabel;
    }
};

const syncFavoriteState = (slug, favorited) => {
    document.querySelectorAll(`[data-favorite-form][data-artwork-slug="${CSS.escape(slug)}"]`).forEach((form) => {
        setFavoriteState(form, favorited);
    });

    if (! favorited && window.location.pathname === '/favorites') {
        document.querySelectorAll(`[data-favorite-card][data-artwork-slug="${CSS.escape(slug)}"]`).forEach((card) => {
            card.remove();
        });
    }
};

const initFavorites = () => {
    document.querySelectorAll('[data-favorite-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = form.querySelector('[data-favorite-button]');
            const method = form.querySelector('[data-favorite-method]')?.value || 'POST';
            const slug = form.dataset.artworkSlug;

            if (! slug) {
                form.submit();
                return;
            }

            button?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(form.action, {
                    method,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });

                if (response.status === 401 || response.redirected) {
                    window.location.href = response.url || '/login';
                    return;
                }

                if (! response.ok) {
                    throw new Error('Favorite request failed.');
                }

                const payload = await response.json();
                syncFavoriteState(slug, Boolean(payload.favorited));
                updateFavoriteCounts(payload.count);
            } catch (error) {
                form.submit();
            } finally {
                button?.removeAttribute('disabled');
            }
        });
    });
};

document.addEventListener('DOMContentLoaded', initGlobalSearch);
document.addEventListener('DOMContentLoaded', initFavorites);

Alpine.start();
