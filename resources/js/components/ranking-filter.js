export default function initRankingFilter() {
    const container = document.getElementById('ranking-filter');
    if (!container) return;

    const input = container.querySelector('.ranking-filter-input');
    const rows = container.querySelectorAll('.ranking-row');
    const counter = container.querySelector('.ranking-filter-count');

    input.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        let visible = 0;

        rows.forEach((row) => {
            const search = row.dataset.search || '';
            const match = !query || search.includes(query);
            row.classList.toggle('hidden', !match);
            if (match) visible++;
        });

        if (counter) {
            counter.textContent = visible === rows.length
                ? `${rows.length} jugadores`
                : `${visible} de ${rows.length} jugadores`;
        }
    });
}
