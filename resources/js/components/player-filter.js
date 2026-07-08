export default function initPlayerFilter() {
    const container = document.getElementById('player-filter');
    if (!container) return;

    const input = container.querySelector('.player-filter-input');
    const cards = container.querySelectorAll('.player-card');
    const counter = container.querySelector('.player-filter-count');

    input.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        let visible = 0;

        cards.forEach((card) => {
            const search = card.dataset.search || '';
            const match = !query || search.includes(query);
            card.classList.toggle('hidden', !match);
            if (match) visible++;
        });

        if (counter) {
            counter.textContent = visible === cards.length
                ? `${cards.length} jugadores`
                : `${visible} de ${cards.length} jugadores`;
        }
    });
}
