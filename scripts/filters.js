// Filter overlay functionality
document.addEventListener('DOMContentLoaded', function () {
    const filterButton = document.getElementById('filter-button');
    const filterOverlay = document.getElementById('filter-overlay');
    const closeFilter = document.getElementById('close-filter');
    const clearFilters = document.getElementById('clear-filters');
    const applyFilters = document.getElementById('apply-filters');

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
        // Uncheck all checkboxes
        const checkboxes = filterOverlay.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);

        // Clear text and number inputs
        const textInputs = filterOverlay.querySelectorAll('input[type="text"], input[type="number"]');
        textInputs.forEach(input => input.value = '');
    });

    // Apply filters
    applyFilters.addEventListener('click', function () {
        // Get all selected filters
        const selectedFilters = {
            styles: [],
            sizes: [],
            location: '',
            priceMin: '',
            priceMax: '',
            ratings: []
        };

        // Collect style filters
        const styleCheckboxes = filterOverlay.querySelectorAll('input[name="style"]:checked');
        styleCheckboxes.forEach(checkbox => {
            selectedFilters.styles.push(checkbox.value);
        });

        // Collect size filters
        const sizeCheckboxes = filterOverlay.querySelectorAll('input[name="size"]:checked');
        sizeCheckboxes.forEach(checkbox => {
            selectedFilters.sizes.push(checkbox.value);
        });

        // Get location
        const locationInput = document.getElementById('location-input');
        selectedFilters.location = locationInput.value;

        // Get price range
        const priceMin = document.getElementById('price-min');
        const priceMax = document.getElementById('price-max');
        selectedFilters.priceMin = priceMin.value;
        selectedFilters.priceMax = priceMax.value;

        // Collect rating filters
        const ratingCheckboxes = filterOverlay.querySelectorAll('input[name="rating"]:checked');
        ratingCheckboxes.forEach(checkbox => {
            selectedFilters.ratings.push(checkbox.value);
        });

        // Log filters
        console.log('Applied Filters:', selectedFilters);

        // Implement actual filtering logic to filter the grid items
        closeFilterOverlay();
    });

    // Close overlay with Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && filterOverlay.classList.contains('active')) {
            closeFilterOverlay();
        }
    });
});
