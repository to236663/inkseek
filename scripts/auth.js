// Toggle dropdown menu in shared navbar.
function toggleDropdown() {
    const dropdown = document.getElementById('dropdown-menu');
    if (dropdown) {
        dropdown.classList.toggle('show');
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

    if (dropdown && profileContainer && !profileContainer.contains(event.target)) {
        dropdown.classList.remove('show');
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
    const submitReviewBtn = document.getElementById('submit-review-btn');
    const ratingStars = document.querySelectorAll('.rating-star');
    let selectedRating = 0;

    if (reviewArtistBtn) {
        reviewArtistBtn.addEventListener('click', function () {
            if (reviewOverlay) {
                reviewOverlay.classList.add('active');
                selectedRating = 0;
                ratingStars.forEach(star => star.classList.remove('active'));
            }
        });
    }

    if (closeReviewBtn) {
        closeReviewBtn.addEventListener('click', function () {
            if (reviewOverlay) {
                reviewOverlay.classList.remove('active');
            }
            const reviewInput = document.getElementById('review-text-input');
            if (reviewInput) {
                reviewInput.value = '';
            }
            selectedRating = 0;
            ratingStars.forEach(star => star.classList.remove('active'));
        });
    }

    if (reviewOverlay) {
        reviewOverlay.addEventListener('click', function (e) {
            if (e.target === reviewOverlay) {
                reviewOverlay.classList.remove('active');
                const reviewInput = document.getElementById('review-text-input');
                if (reviewInput) {
                    reviewInput.value = '';
                }
                selectedRating = 0;
                ratingStars.forEach(star => star.classList.remove('active'));
            }
        });
    }

    ratingStars.forEach(star => {
        star.addEventListener('click', function () {
            selectedRating = parseInt(this.getAttribute('data-value'), 10);
            ratingStars.forEach((s, index) => {
                if (index < selectedRating) {
                    s.classList.add('active');
                    s.style.color = '#FFD700';
                } else {
                    s.classList.remove('active');
                    s.style.color = '#D7D7D7';
                }
            });
        });

        star.addEventListener('mouseenter', function () {
            const hoverValue = parseInt(this.getAttribute('data-value'), 10);
            ratingStars.forEach((s, index) => {
                s.style.color = index < hoverValue ? '#FFD700' : '#D7D7D7';
            });
        });
    });

    const starRating = document.querySelector('.star-rating');
    if (starRating) {
        starRating.addEventListener('mouseleave', function () {
            ratingStars.forEach((s, index) => {
                s.style.color = index < selectedRating ? '#FFD700' : '#D7D7D7';
            });
        });
    }

    if (submitReviewBtn) {
        submitReviewBtn.addEventListener('click', function () {
            if (selectedRating === 0) {
                alert('Please select a star rating');
                return;
            }

            if (reviewOverlay) {
                reviewOverlay.classList.remove('active');
            }

            const reviewInput = document.getElementById('review-text-input');
            if (reviewInput) {
                reviewInput.value = '';
            }

            selectedRating = 0;
            ratingStars.forEach(star => {
                star.classList.remove('active');
                star.style.color = '#D7D7D7';
            });
        });
    }
}
