// Toggle dropdown menu in shared navbar.
function toggleDropdown() {
    const dropdown = document.getElementById('dropdown-menu');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

function toggleMobileNav() {
    const mobileNav = document.getElementById('mobile-nav-menu');
    const toggleButton = document.getElementById('mobile-nav-toggle');
    if (mobileNav && toggleButton) {
        const isOpen = mobileNav.classList.toggle('show');
        toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        mobileNav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }
}

// Clear all UI state and redirect to logout backend.
function logout() {
    // Close any open dropdowns or modals
    const dropdown = document.getElementById('dropdown-menu');
    if (dropdown) {
        dropdown.classList.remove('show');
    }

    // Clear form states and overlays
    const overlays = document.querySelectorAll('.overlay, [id*="overlay"]');
    overlays.forEach(overlay => {
        if (overlay.style) {
            overlay.style.display = 'none';
        }
    });

    // Reset any form inputs
    const forms = document.querySelectorAll('form');
    forms.forEach(form => form.reset());

    // Redirect to logout backend (destroys session) and stay on current page
    const pathPrefix = window.location.pathname.includes('/guides/') ? '../' : '';
    const currentPath = window.location.pathname.substring(1) || 'index.html';
    window.location.href = pathPrefix + 'logout.php?redirect=' + encodeURIComponent(currentPath);
}

// This app now uses PHP sessions for account type changes.
function convertAccount() {
    window.location.href = '/artist-settings.php';
}

// Close dropdown when clicking outside the profile area.
document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('dropdown-menu');
    const profileContainer = document.querySelector('.profile-container');
    const mobileNav = document.getElementById('mobile-nav-menu');
    const mobileToggle = document.getElementById('mobile-nav-toggle');

    if (dropdown && profileContainer && !profileContainer.contains(event.target)) {
        dropdown.classList.remove('show');
    }

    if (mobileNav && mobileToggle && !mobileNav.contains(event.target) && !mobileToggle.contains(event.target)) {
        mobileNav.classList.remove('show');
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileNav.setAttribute('aria-hidden', 'true');
    }
});

function initializePageFeatures() {
    if (!window.location.pathname.includes('artist-profile.php')) {
        return;
    }

    const editPortfolioBtn = document.getElementById('edit-portfolio-btn');
    const portfolioOverlay = document.getElementById('portfolio-overlay');
    const closeModalBtn = document.getElementById('close-portfolio-modal');
    const portfolioForm = document.getElementById('portfolio-form');

    if (editPortfolioBtn) {
        editPortfolioBtn.addEventListener('click', function () {
            if (portfolioOverlay) {
                portfolioOverlay.classList.add('active');
            }
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function () {
            if (portfolioOverlay) {
                portfolioOverlay.classList.remove('active');
            }
        });
    }

    if (portfolioOverlay) {
        portfolioOverlay.addEventListener('click', function (e) {
            if (e.target === portfolioOverlay) {
                portfolioOverlay.classList.remove('active');
            }
        });
    }

    if (portfolioForm) {
        portfolioForm.addEventListener('submit', function () {
            const uploadButton = document.getElementById('upload-portfolio-btn');
            if (uploadButton) {
                uploadButton.disabled = true;
                uploadButton.textContent = 'Uploading...';
            }
        });
    }

    const reviewArtistBtn = document.getElementById('review-artist-btn');
    const reviewOverlay = document.getElementById('review-overlay');
    const closeReviewBtn = document.getElementById('close-review-modal');
    const reviewForm = document.getElementById('review-form-content');
    const reviewRatingSelect = document.getElementById('review-rating-select');
    const reviewInput = document.getElementById('review-text-input');
    const reviewError = document.getElementById('review-error');
    const submitReviewBtn = document.getElementById('submit-review-btn');
    const invalidRatingClass = 'is-invalid-rating';

    function showReviewError(message) {
        if (reviewError) {
            reviewError.textContent = message;
            reviewError.style.display = '';
        }
    }

    function clearReviewError() {
        if (reviewError) {
            reviewError.textContent = '';
            reviewError.style.display = 'none';
        }
    }

    function setRatingValidationState(isInvalid) {
        if (!reviewRatingSelect) {
            return;
        }

        if (isInvalid) {
            reviewRatingSelect.classList.add(invalidRatingClass);
            reviewRatingSelect.setAttribute('aria-invalid', 'true');
        } else {
            reviewRatingSelect.classList.remove(invalidRatingClass);
            reviewRatingSelect.removeAttribute('aria-invalid');
        }
    }

    if (reviewArtistBtn) {
        reviewArtistBtn.addEventListener('click', function () {
            if (reviewOverlay) {
                reviewOverlay.classList.add('active');
            }
            clearReviewError();
            setRatingValidationState(false);
        });
    }

    if (closeReviewBtn) {
        closeReviewBtn.addEventListener('click', function () {
            if (reviewOverlay) {
                reviewOverlay.classList.remove('active');
            }
            clearReviewError();
            setRatingValidationState(false);
        });
    }

    if (reviewOverlay) {
        reviewOverlay.addEventListener('click', function (e) {
            if (e.target === reviewOverlay) {
                reviewOverlay.classList.remove('active');
                clearReviewError();
                setRatingValidationState(false);
            }
        });
    }

    if (reviewRatingSelect) {
        reviewRatingSelect.addEventListener('change', function () {
            const hasRating = Boolean(reviewRatingSelect.value);
            setRatingValidationState(!hasRating);
        });
    }

    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            const hasRating = Boolean(reviewRatingSelect && reviewRatingSelect.value);
            const hasText = Boolean(reviewInput && reviewInput.value.trim().length > 0);

            setRatingValidationState(!hasRating);

            if (!hasRating || !hasText) {
                e.preventDefault();
                showReviewError("Couldn't review artist");
                return;
            }

            clearReviewError();

            if (submitReviewBtn) {
                submitReviewBtn.disabled = true;
                submitReviewBtn.textContent = 'Submitting...';
            }
        });
    }
}
