<?php
$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/components/footer.php'));
$appBasePath = rtrim(dirname(dirname($scriptName)), '/');

function footer_app_url(string $path): string
{
    global $appBasePath;

    if ($path === '') {
        return $appBasePath !== '' ? $appBasePath . '/' : '/';
    }

    if (preg_match('#^(?:https?:)?//#', $path)) {
        return $path;
    }

    if ($appBasePath !== '' && str_starts_with($path, $appBasePath . '/')) {
        return $path;
    }

    if (str_starts_with($path, '/')) {
        return ($appBasePath !== '' ? $appBasePath : '') . $path;
    }

    return ($appBasePath !== '' ? $appBasePath : '') . '/' . ltrim($path, '/');
}
?>
<footer>
    <img id="simple-logo" src="<?= htmlspecialchars(footer_app_url('images/logos/inkseeklogosimple.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Inkseek logo">
    <ul>
        <li>
            <a href="<?= htmlspecialchars(footer_app_url('discover.php'), ENT_QUOTES, 'UTF-8') ?>">Discover</a>
        </li>
        <li>
            <a href="<?= htmlspecialchars(footer_app_url('about-us.html'), ENT_QUOTES, 'UTF-8') ?>">About Us</a>
        </li>
        <li>
            <a href="<?= htmlspecialchars(footer_app_url('guides.html'), ENT_QUOTES, 'UTF-8') ?>">Guides</a>
        </li>
    </ul>
    <ul>
        <li>
            <a href="#">Instagram</a>
        </li>
        <li>
            <a href="#">TikTok</a>
        </li>
        <li>
            <a href="#">Facebook</a>
        </li>
    </ul>
    <ul>
        <li>
            <a href="<?= htmlspecialchars(footer_app_url('create-account.php'), ENT_QUOTES, 'UTF-8') ?>">Sign Up</a>
        </li>
    </ul>
</footer>