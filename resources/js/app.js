import './bootstrap';
import initPlayerSearch from './components/player-search';

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
});
