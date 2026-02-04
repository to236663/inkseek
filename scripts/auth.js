// Login state management
function checkLoginState() {
    const isLoggedIn = sessionStorage.getItem('userLoggedIn') === 'true';
    const userType = sessionStorage.getItem('userType') || 'user'; // 'user' or 'artist'

    const loggedOutButtons = document.getElementById('logged-out-buttons');
    const loggedInProfile = document.getElementById('logged-in-profile');
    const profilePic = document.getElementById('nav-profile-pic');
    const profileLink = document.getElementById('profile-link');
    const dropdownUsername = document.getElementById('dropdown-username');
    const dropdownAccountType = document.getElementById('dropdown-account-type');
    const convertBtn = document.getElementById('convert-account-btn');

    if (isLoggedIn && loggedOutButtons && loggedInProfile) {
        loggedOutButtons.style.display = 'none';
        loggedInProfile.style.display = 'flex';

        // Set profile picture and link based on user type
        if (userType === 'artist') {
            profilePic.src = 'images/profile photos/Artist/Profile_1.jpg';
            profileLink.href = 'artist-profile.html';
            if (dropdownAccountType) dropdownAccountType.textContent = 'Artist Account';
            if (convertBtn) {
                convertBtn.style.display = 'block';
                convertBtn.textContent = 'Convert to User Account';
            }
        } else {
            profilePic.src = 'images/profile photos/User/UP_4.jpg';
            profileLink.href = 'user-profile.html';
            if (dropdownAccountType) dropdownAccountType.textContent = 'Personal Account';
            if (convertBtn) {
                convertBtn.style.display = 'block';
                convertBtn.textContent = 'Convert to Artist Account';
            }
        }

        // Set username in dropdown
        if (dropdownUsername) {
            const storedUsername = sessionStorage.getItem('username') || '@username';
            dropdownUsername.textContent = storedUsername;
        }
    } else if (loggedOutButtons && loggedInProfile) {
        // User is logged out - show login/create account buttons
        loggedOutButtons.style.display = 'flex';
        loggedInProfile.style.display = 'none';
    }
}

// Toggle dropdown menu
function toggleDropdown() {
    const dropdown = document.getElementById('dropdown-menu');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('dropdown-menu');
    const profileContainer = document.querySelector('.profile-container');

    if (dropdown && profileContainer && !profileContainer.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

// Set login state
function setLoginState(userType, username = null) {
    sessionStorage.setItem('userLoggedIn', 'true');
    sessionStorage.setItem('userType', userType);
    if (username) {
        sessionStorage.setItem('username', username);
    }
}

// Logout function
function logout() {
    sessionStorage.removeItem('userLoggedIn');
    sessionStorage.removeItem('userType');
    sessionStorage.removeItem('username');
    window.location.href = 'index.html';
}

// Convert account function
function convertAccount() {
    const currentUserType = sessionStorage.getItem('userType') || 'user';

    if (currentUserType === 'user') {
        // Convert to artist
        sessionStorage.setItem('userType', 'artist');
        sessionStorage.setItem('username', '@SilverSpire_Ink');
        window.location.href = 'artist-profile.html';
    } else {
        // Convert to user
        sessionStorage.setItem('userType', 'user');
        sessionStorage.setItem('username', '@Thewanderingquill');
        window.location.href = 'user-profile.html';
    }
}

// Initialize page-specific features
function initializePageFeatures() {
    // Artist profile page - show Edit Profile if artist is viewing their own profile
    if (window.location.pathname.includes('artist-profile.html')) {
        const userType = sessionStorage.getItem('userType') || 'user';
        const followBtn = document.getElementById('follow-btn');
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const reviewArtistBtn = document.getElementById('review-artist-btn');
        const editPortfolioBtn = document.getElementById('edit-portfolio-btn');

        if (userType === 'artist') {
            // Artist viewing their own profile
            if (followBtn) followBtn.style.display = 'none';
            if (editProfileBtn) editProfileBtn.style.display = 'inline-block';
            if (reviewArtistBtn) reviewArtistBtn.style.display = 'none';
            if (editPortfolioBtn) editPortfolioBtn.style.display = 'block';
        } else {
            // User viewing artist profile
            if (followBtn) followBtn.style.display = 'inline-block';
            if (editProfileBtn) editProfileBtn.style.display = 'none';
            if (reviewArtistBtn) reviewArtistBtn.style.display = 'block';
            if (editPortfolioBtn) editPortfolioBtn.style.display = 'none';
        }
    }

    // Image view page - bookmark toggle functionality
    if (window.location.pathname.includes('image-view.html')) {
        const bookmarkIcon = document.getElementById('bookmark-icon');
        if (bookmarkIcon) {
            // Check if this image is already bookmarked
            const isBookmarked = sessionStorage.getItem('bookmark-current') === 'true';
            if (isBookmarked) {
                bookmarkIcon.src = 'images/favicons/bookmark-clicked.png';
            }

            bookmarkIcon.addEventListener('click', function () {
                // Toggle between bookmark images
                const nowBookmarked = this.src.includes('bookmark-clicked');
                if (nowBookmarked) {
                    this.src = 'images/favicons/bookmark.png';
                    sessionStorage.setItem('bookmark-current', 'false');
                } else {
                    this.src = 'images/favicons/bookmark-clicked.png';
                    sessionStorage.setItem('bookmark-current', 'true');
                }
            });
        }
    }

    // Portfolio edit overlay functionality
    if (window.location.pathname.includes('artist-profile.html')) {
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

        // Close overlay when clicking outside modal
        if (portfolioOverlay) {
            portfolioOverlay.addEventListener('click', function (e) {
                if (e.target === portfolioOverlay) {
                    portfolioOverlay.classList.remove('active');
                }
            });
        }

        // Handle form submission
        if (portfolioForm) {
            portfolioForm.addEventListener('submit', function (e) {
                e.preventDefault();
                // Form submission logic would go here
                alert('Portfolio image upload functionality would be implemented here');
                portfolioOverlay.classList.remove('active');
                portfolioForm.reset();
            });
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    checkLoginState();
    initializePageFeatures();
});