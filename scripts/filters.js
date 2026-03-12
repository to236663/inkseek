// Filter overlay functionality
document.addEventListener('DOMContentLoaded', function () {
    const filterButton = document.getElementById('filter-button');
    const filterOverlay = document.getElementById('filter-overlay');
    const closeFilter = document.getElementById('close-filter');
    const clearFilters = document.getElementById('clear-filters');
    const applyFilters = document.getElementById('apply-filters');
    const searchBar = document.getElementById('search-bar');
    const searchForm = document.getElementById('search-form');

    if (!filterButton || !filterOverlay || !closeFilter || !clearFilters || !applyFilters) {
        return;
    }

    // Open filter overlay
    filterButton.addEventListener('click', function () {
        filterOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    });

    // Close filter overlay
    function closeFilterOverlay() {
        filterOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
    }

    closeFilter.addEventListener('click', closeFilterOverlay);

    // Close overlay when clicking outside the filter content
    filterOverlay.addEventListener('click', function (e) {
        if (e.target === filterOverlay) {
            closeFilterOverlay();
        }
    });

    // Clear all filters
    clearFilters.addEventListener('click', function () {
        // Uncheck all checkboxes in the overlay (style + currently non-functional filters)
        const checkboxes = filterOverlay.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);

        // Clear text and number inputs (non-functional filters kept for visual parity)
        const textInputs = filterOverlay.querySelectorAll('input[type="text"], input[type="number"]');
        textInputs.forEach(input => input.value = '');
    });

    // Apply filters
    applyFilters.addEventListener('click', function () {
        const params = new URLSearchParams(window.location.search);
        const styleCheckboxes = filterOverlay.querySelectorAll('input[name="style"]:checked');

        params.delete('style');
        params.delete('style[]');

        styleCheckboxes.forEach(checkbox => {
            params.append('style[]', checkbox.value);
        });

        if (searchBar && searchBar.value.trim() !== '') {
            params.set('q', searchBar.value.trim());
        } else {
            params.delete('q');
        }

        const queryString = params.toString();
        window.location.href = queryString === '' ? 'discover.php' : 'discover.php?' + queryString;
    });

    // Keep Enter/search submit behavior in sync with current style checkbox state.
    if (searchForm) {
        searchForm.addEventListener('submit', function () {
            const existingHiddenStyleInputs = searchForm.querySelectorAll('input[type="hidden"][name="style[]"]');
            existingHiddenStyleInputs.forEach(input => input.remove());

            const styleCheckboxes = filterOverlay.querySelectorAll('input[name="style"]:checked');
            styleCheckboxes.forEach(checkbox => {
                const hiddenStyleInput = document.createElement('input');
                hiddenStyleInput.type = 'hidden';
                hiddenStyleInput.name = 'style[]';
                hiddenStyleInput.value = checkbox.value;
                searchForm.appendChild(hiddenStyleInput);
            });
        });
    }

    // Close overlay with Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && filterOverlay.classList.contains('active')) {
            closeFilterOverlay();
        }
    });
});
