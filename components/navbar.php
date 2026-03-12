<?php session_start(); ?>
<div id="navbar">
    <a id="logo-link" href="/index.html">
        <img id="bottle-logo" src="/images/logos/inkseeklogomain.png" alt="ink bottle dripping">
    </a>
    <div id="center-buttons">
        <div id="center-buttons-group">
            <a class="navbutton" href="/discover.html">Discover</a>
            <a class="navbutton" href="/about-us.html">About Us</a>
            <a class="navbutton" href="/guides.html">Guides</a>
        </div>
    </div>
    <div id="right-buttons">

        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <!-- LOGGED IN -->
            <div id="logged-in-profile" class="right-buttons-group">
                <div class="profile-container">
                    <div class="profile-dropdown" onclick="toggleDropdown();">

                        <!-- Link changes based on account type -->
                        <?php if ($_SESSION['logged_in_access_level'] === 'artist'): ?>
                            <a id="profile-link" href="/artist-profile.php?profile_id=<?= $_SESSION['artist_profile_id'] ?>">
                        <?php else: ?>
                            <a id="profile-link" href="/user-profile.php?account_id=<?= $_SESSION['logged_in_account_id'] ?>">
                        <?php endif; ?>
                            <img id="nav-profile-pic" class="nav-profile-pic"
                                src="<?= $_SESSION['logged_in_profile_image'] ?? '/images/profile photos/User/UP_4.jpg' ?>"
                                alt="Profile">
                            </a>

                        <div class="dropdown-arrow"></div>
                    </div>

                    <div id="dropdown-menu" class="dropdown-menu">
                        <div class="dropdown-header">
                            <p id="dropdown-username">@<?= htmlspecialchars($_SESSION['logged_in_username']) ?></p>
                            <p id="dropdown-account-type">
                                <?= $_SESSION['logged_in_access_level'] === 'artist' ? 'Artist Account' : 'Personal Account' ?>
                            </p>
                        </div>

                        <!-- Only show convert button if not already an artist -->
                        <?php if ($_SESSION['logged_in_access_level'] !== 'artist'): ?>
                            <button class="convert-btn" onclick="convertAccount()">
                                Convert to Artist Account
                            </button>
                        <?php endif; ?>

                        <a href="/artist-settings.php" class="dropdown-item">
                            <img src="/images/favicons/settings.png" class="dropdown-icon-img" alt="Settings">
                            <span>Account Settings</span>
                        </a>
                        <a href="/messages.html" class="dropdown-item">
                            <img src="/images/favicons/messages.png" class="dropdown-icon-img" alt="Messages">
                            <span>Messages</span>
                        </a>
                        <a href="/discover.html" class="dropdown-item">
                            <img src="/images/favicons/search.png" class="dropdown-icon-img" alt="Search">
                            <span>Search</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="/logout.php" class="dropdown-item">
                            <span>Log Out</span>
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- LOGGED OUT -->
            <div id="logged-out-buttons" class="right-buttons-group">
                <a class="navbutton" href="/login.php">Log In</a>
                <a class="navbutton" href="/create-account.php">Create Account</a>
            </div>
        <?php endif; ?>

    </div>
</div>
<div id="navbar">
    <a id="logo-link" href="index.html">
        <img id="bottle-logo" src="images/logos/inkseeklogomain.png" alt="ink bottle dripping">
    </a>
    <div id="center-buttons">
        <div id="center-buttons-group">
            <a class="navbutton" id="discover-button" href="discover.html">Discover</a>
            <a class="navbutton" id="about-us-button" href="about-us.html">About Us</a>
            <a class="navbutton" id="guides-button" href="guides.html">Guides</a>
        </div>
    </div>
    <div id="right-buttons">
        <div id="logged-out-buttons" class="right-buttons-group">
            <a class="navbutton" id="login" href="/login.php">Log In</a>
            <a class="navbutton" id="create-account" href="/create-account.php">Create Account</a>
        </div>
        <div id="logged-in-profile" class="right-buttons-group" style="display: none;">
            <div class="profile-container">
                <div class="profile-dropdown" onclick="toggleDropdown();">
                    <a id="profile-link" href="user-profile.html">
                        <img id="nav-profile-pic" class="nav-profile-pic" src="images/profile photos/User/UP_4.jpg"
                            alt="Profile">
                    </a>
                    <div class="dropdown-arrow"></div>
                </div>
                <div id="dropdown-menu" class="dropdown-menu">
                    <div class="dropdown-header">
                        <p id="dropdown-username" class="dropdown-username">@username</p>
                        <p id="dropdown-account-type" class="dropdown-account-type">Personal Account</p>
                    </div>
                    <button class="convert-btn" id="convert-account-btn" onclick="convertAccount()">Convert to Artist
                        Account</button>
                    <a href="#" class="dropdown-item">
                        <img src="images/favicons/settings.png" class="dropdown-icon-img" alt="Settings">
                        <span>Account Settings</span>
                    </a>
                    <a href="messages.html" class="dropdown-item">
                        <img src="images/favicons/messages.png" class="dropdown-icon-img" alt="Messages">
                        <span>Messages</span>
                    </a>
                    <a href="discover.html" class="dropdown-item">
                        <img src="images/favicons/search.png" class="dropdown-icon-img" alt="Search">
                        <span>Search</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" onclick="logout(); return false;">
                        <span>Log Out</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>