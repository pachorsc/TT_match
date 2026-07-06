export default function initVideoLoader() {
    const containers = document.querySelectorAll('.videos-container');

    containers.forEach((container) => {
        const playerIds = getPlayerIds(container);
        if (playerIds.length === 0) return;

        loadVideosForPlayers(container, playerIds);
    });

    setupVideosSearchPage();
}

export function loadVideosForPlayers(container, playerIds) {
    const playerNameMap = getPlayerNameMap(container);

    playerIds.forEach((playerId) => {
        const slot = createOrGetSlot(container, playerId);
        const grid = slot.querySelector('.videos-grid');
        const spinner = slot.querySelector('.videos-spinner');
        const empty = slot.querySelector('.videos-empty');
        const errorEl = slot.querySelector('.videos-error');

        spinner.classList.remove('hidden');
        grid.classList.add('hidden');
        if (empty) empty.classList.add('hidden');
        if (errorEl) errorEl.classList.add('hidden');

        axios
            .get(`/api/players/${playerId}/videos`)
            .then((response) => {
                spinner.classList.add('hidden');

                const videos = response.data.videos;

                if (!videos || videos.length === 0) {
                    if (empty) empty.classList.remove('hidden');
                    return;
                }

                grid.innerHTML = '';
                grid.classList.remove('hidden');

                videos.forEach((video) => {
                    const card = createVideoCard(video, playerNameMap[playerId]);
                    grid.appendChild(card);
                });
            })
            .catch(() => {
                spinner.classList.add('hidden');
                if (errorEl) errorEl.classList.remove('hidden');
            });
    });
}

function getPlayerIds(container) {
    const attr = container.dataset.players;
    if (!attr) return [];

    try {
        const ids = JSON.parse(attr);
        return Array.isArray(ids) ? ids : [];
    } catch {
        return [];
    }
}

function getPlayerNameMap(container) {
    try {
        const attr = container.dataset.playerNames;
        return attr ? JSON.parse(attr) : {};
    } catch {
        return {};
    }
}

function createOrGetSlot(container, playerId) {
    let slot = container.querySelector(`.videos-player-slot[data-player-id="${playerId}"]`);

    if (slot) return slot;

    slot = document.createElement('div');
    slot.className = 'videos-player-slot';
    slot.dataset.playerId = playerId;

    slot.innerHTML = `
        <div class="videos-spinner flex items-center justify-center py-16">
            <svg class="w-8 h-8 text-sport-400 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        <div class="videos-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 hidden"></div>
        <div class="videos-empty hidden">
            <div class="card-glass px-6 py-14 sm:py-16 text-center space-y-4">
                <svg class="w-10 h-10 mx-auto text-white/15" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <p class="text-sm text-white/30 max-w-xs mx-auto">No se encontraron videos para este jugador.</p>
            </div>
        </div>
        <div class="videos-error hidden">
            <div class="card-glass px-6 py-14 sm:py-16 text-center space-y-4">
                <svg class="w-10 h-10 mx-auto text-white/15" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <p class="text-sm text-white/30 max-w-xs mx-auto">Error al cargar videos. Intenta de nuevo.</p>
            </div>
        </div>
    `;

    container.appendChild(slot);

    return slot;
}

function setupVideosSearchPage() {
    const searchBtn = document.getElementById('videos-search-btn');
    if (!searchBtn) return;

    const searchInput = document.querySelector('.player-search-input');
    const hiddenInput = document.querySelector('.player-search-hidden');

    function updateButtonState() {
        const hasPlayer = hiddenInput && hiddenInput.value;
        searchBtn.disabled = !hasPlayer;
    }

    if (hiddenInput) {
        const observer = new MutationObserver(() => updateButtonState());
        observer.observe(hiddenInput, { attributes: true, attributeFilter: ['value'] });
        updateButtonState();
    }

    searchBtn.addEventListener('click', () => {
        if (!hiddenInput || !hiddenInput.value) return;

        const playerId = parseInt(hiddenInput.value, 10);
        if (!playerId) return;

        const playerName = searchInput ? searchInput.value : '';

        const container = document.querySelector('.videos-container');
        if (!container) return;

        container.dataset.players = JSON.stringify([playerId]);
        container.dataset.playerNames = JSON.stringify({ [playerId]: playerName });

        container.innerHTML = '';

        loadVideosForPlayers(container, [playerId]);
    });
}

function createVideoCard(video, playerName) {
    const publishedAt = video.published_at
        ? timeAgo(video.published_at)
        : '';

    const channelTitle = video.channel_title || 'YouTube';

    const wrapper = document.createElement('a');
    wrapper.href = video.url;
    wrapper.target = '_blank';
    wrapper.rel = 'noopener noreferrer';
    wrapper.className =
        'card-glass group block overflow-hidden cursor-pointer';

    wrapper.innerHTML = `
        <div class="relative aspect-video overflow-hidden rounded-t-2xl bg-black/40">
            <img src="${escapeHtml(video.thumbnail_url)}"
                 alt="${escapeHtml(video.title)}"
                 class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105"
                 loading="lazy">
            <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm">
                    <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="p-4 space-y-2">
            <h4 class="text-sm font-bold leading-snug line-clamp-2 text-white/90 group-hover:text-sport-400 transition-colors duration-200">
                ${escapeHtml(video.title)}
            </h4>
            ${video.description ? `<p class="text-xs text-white/40 leading-relaxed line-clamp-2">${escapeHtml(video.description)}</p>` : ''}
            <div class="flex items-center justify-between gap-3 pt-2">
                <span class="text-xs text-white/30">${publishedAt}</span>
                <span class="text-xs text-white/50 font-medium truncate">${escapeHtml(channelTitle)}</span>
            </div>
        </div>
    `;

    return wrapper;
}

function timeAgo(dateStr) {
    const now = new Date();
    const date = new Date(dateStr);
    const diffMs = now - date;
    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHr = Math.floor(diffMin / 60);
    const diffDay = Math.floor(diffHr / 24);
    const diffMonth = Math.floor(diffDay / 30);
    const diffYear = Math.floor(diffDay / 365);

    if (diffYear > 0) return `${diffYear}y ago`;
    if (diffMonth > 0) return `${diffMonth}mo ago`;
    if (diffDay > 0) return `${diffDay}d ago`;
    if (diffHr > 0) return `${diffHr}h ago`;
    if (diffMin > 0) return `${diffMin}m ago`;
    return 'now';
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
