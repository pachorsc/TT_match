import './bootstrap';
import initPlayerSearch from './components/player-search';
import initPlayerFilter from './components/player-filter';
import initRankingFilter from './components/ranking-filter';
import initVideoLoader from './components/video-loader';

document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            html.classList.toggle('dark', !isDark);
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        });
    }

    initPlayerSearch();
    initPlayerFilter();
    initRankingFilter();
    initVideoLoader();

    const predictForm = document.getElementById('predict-form');
    if (predictForm) {
        predictForm.addEventListener('submit', function () {
            const btn = predictForm.querySelector('.predict-submit');
            if (btn && !btn.disabled) {
                const icon = btn.querySelector('.predict-submit-icon');
                const text = btn.querySelector('.predict-submit-text');
                const spinner = btn.querySelector('.predict-submit-spinner');
                if (icon) icon.classList.add('hidden');
                if (text) text.textContent = 'Loading...';
                if (spinner) spinner.classList.remove('hidden');
                btn.disabled = true;
                btn.classList.add('opacity-60', 'cursor-wait');
            }
        });
    }
});
