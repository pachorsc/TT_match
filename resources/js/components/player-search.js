export default function initPlayerSearch() {
    const containers = document.querySelectorAll('.player-search');

    containers.forEach((container) => {
        const input = container.querySelector('.player-search-input');
        const dropdown = container.querySelector('.player-search-dropdown');
        const hiddenInput = container.querySelector('.player-search-hidden');
        const items = container.querySelectorAll('.player-search-item');
        const clearBtn = container.querySelector('.player-search-clear');

        let open = false;
        let selectedIndex = -1;
        let currentHighlight = -1;

        const allItems = [...items];

        let noResultsEl = dropdown.querySelector('.player-search-no-results');
        if (!noResultsEl) {
            noResultsEl = document.createElement('div');
            noResultsEl.className = 'player-search-no-results px-4 py-3 text-sm text-gray-500 dark:text-white/30 text-center hidden';
            noResultsEl.textContent = 'No players found';
            dropdown.appendChild(noResultsEl);
        }

        function filterItems(query) {
            const lower = query.toLowerCase();
            let visible = 0;

            allItems.forEach((item) => {
                const searchData = item.dataset.search || '';
                const match = searchData.includes(lower);
                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (visible > 0 && open) {
                dropdown.classList.remove('hidden');
                noResultsEl.classList.add('hidden');
                highlightItem(0);
            } else if (open) {
                dropdown.classList.remove('hidden');
                noResultsEl.classList.toggle('hidden', visible > 0);
            } else {
                dropdown.classList.add('hidden');
                noResultsEl.classList.add('hidden');
            }

            return visible;
        }

        function highlightItem(index) {
            const visible = allItems.filter((el) => el.style.display !== 'none');
            if (visible.length === 0) return;

            if (index < 0) index = 0;
            if (index >= visible.length) index = visible.length - 1;

            currentHighlight = index;

            const isDark = document.documentElement.classList.contains('dark');
            visible.forEach((el, i) => {
                el.classList.toggle(isDark ? 'bg-white/[0.06]' : 'bg-gray-100/70', i === index);
                el.classList.toggle(isDark ? 'text-white' : 'text-gray-900', i === index);
            });

            if (visible[index]) {
                visible[index].scrollIntoView({ block: 'nearest' });
            }
        }

        function selectItem(item) {
            const value = item.dataset.value;
            const label = item.dataset.label;

            hiddenInput.value = value;
            input.value = label;
            input.dataset.selected = value;
            closeDropdown();
            input.classList.remove('text-white/50', 'text-gray-400');
            input.classList.add(document.documentElement.classList.contains('dark') ? 'text-white' : 'text-gray-900');

            if (clearBtn) clearBtn.classList.remove('hidden');

            container.dispatchEvent(new CustomEvent('player-selected', {
                detail: { value, label, name: input.name },
            }));

            updateSubmitButton();
        }

        function closeDropdown() {
            open = false;
            dropdown.classList.add('hidden');
            currentHighlight = -1;
        }

        function openDropdown() {
            if (allItems.length === 0) return;
            open = true;
            const visible = filterItems(input.value.toLowerCase());
            if (visible > 0) {
                dropdown.classList.remove('hidden');
            }
        }

        function clearSelection() {
            hiddenInput.value = '';
            input.value = '';
            input.dataset.selected = '';
            input.classList.remove('text-white', 'text-gray-900');
            input.classList.add(document.documentElement.classList.contains('dark') ? 'text-white/50' : 'text-gray-400');
            if (clearBtn) clearBtn.classList.add('hidden');
            closeDropdown();
            updateSubmitButton();
        }

        function updateSubmitButton() {
            const aValue = document.querySelector('.player-search-hidden[name="player_a"]')?.value;
            const bValue = document.querySelector('.player-search-hidden[name="player_b"]')?.value;
            const btn = document.querySelector('.predict-submit');
            const enabled = aValue && bValue && aValue !== bValue;

            if (btn) {
                btn.disabled = !enabled;

                btn.classList.toggle('bg-sport-500/20', enabled);
                btn.classList.toggle('text-sport-400', enabled);
                btn.classList.toggle('border-sport-500/30', enabled);
                btn.classList.toggle('hover:bg-sport-500/30', enabled);
                btn.classList.toggle('hover:border-sport-500/50', enabled);
                btn.classList.toggle('cursor-pointer', enabled);

                const isDark = document.documentElement.classList.contains('dark');
                btn.classList.toggle(isDark ? 'bg-white/[0.04]' : 'bg-gray-100/60', !enabled);
                btn.classList.toggle(isDark ? 'text-white/20' : 'text-gray-400', !enabled);
                btn.classList.toggle(isDark ? 'border-white/[0.06]' : 'border-gray-200/80', !enabled);
                btn.classList.toggle('cursor-not-allowed', !enabled);
                btn.classList.toggle('opacity-40', !enabled);
            }
        }

        input.addEventListener('focus', openDropdown);

        input.addEventListener('blur', () => {
            setTimeout(() => closeDropdown(), 200);
        });

        input.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            open = true;
            const visible = filterItems(q);
            if (visible > 0 && open) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        });

        input.addEventListener('keydown', (e) => {
            const visible = allItems.filter((el) => el.style.display !== 'none');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightItem(currentHighlight + 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightItem(currentHighlight - 1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (visible[currentHighlight]) {
                    selectItem(visible[currentHighlight]);
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
                input.blur();
            }
        });

        allItems.forEach((item) => {
            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                selectItem(item);
            });

            item.addEventListener('mouseenter', () => {
                const idx = allItems.indexOf(item);
                highlightItem(allItems.filter((el) => el.style.display !== 'none').indexOf(item));
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', clearSelection);
        }

        if (!hiddenInput.value) {
            input.classList.add(document.documentElement.classList.contains('dark') ? 'text-white/50' : 'text-gray-400');
            if (clearBtn) clearBtn.classList.add('hidden');
        } else {
            input.classList.remove('text-white/50', 'text-gray-400');
            input.classList.add(document.documentElement.classList.contains('dark') ? 'text-white' : 'text-gray-900');
        }

        updateSubmitButton();
    });
}
