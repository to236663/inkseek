// Login state management
function checkLoginState() {
    const isLoggedIn = localStorage.getItem('userLoggedIn') === 'true';
    const userType = localStorage.getItem('userType') || 'user'; // 'user' or 'artist'

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
            if (convertBtn) convertBtn.style.display = 'none';
        } else {
            profilePic.src = 'images/profile photos/User/UP_4.jpg';
            profileLink.href = 'user-profile.html';
            if (dropdownAccountType) dropdownAccountType.textContent = 'Personal Account';
            if (convertBtn) convertBtn.style.display = 'block';
        }

        // Set username in dropdown
        if (dropdownUsername) {
            dropdownUsername.textContent = '@username';
        }
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
function setLoginState(userType) {
    localStorage.setItem('userLoggedIn', 'true');
    localStorage.setItem('userType', userType);
}

// Logout function
function logout() {
    localStorage.removeItem('userLoggedIn');
    localStorage.removeItem('userType');
    window.location.href = 'index.html';
}

// Run on page load
document.addEventListener('DOMContentLoaded', checkLoginState);
