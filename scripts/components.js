// Load navbar and footer components
async function loadComponent(elementId, filePaths) {
    const paths = Array.isArray(filePaths) ? filePaths : [filePaths];

    for (const filePath of paths) {
        try {
            const response = await fetch(filePath);
            if (!response.ok) {
                continue;
            }

            const html = await response.text();
            document.getElementById(elementId).innerHTML = html;
            return;
        } catch (error) {
            console.error('Error loading component:', error);
        }
    }

    console.error(`Unable to load component for ${elementId}`);
}

function getPathPrefix() {
    return window.location.pathname.includes('/guides/') ? '../' : '';
}

// Load components when DOM is ready
async function loadComponents() {
    // Add loading class to body
    document.body.classList.add('loading');

    const pathPrefix = getPathPrefix();
    await loadComponent('navbar-placeholder', [
        `${pathPrefix}components/navbar.php`,
        'components/navbar.php',
        '../components/navbar.php'
    ]);
    await loadComponent('footer-placeholder', [
        `${pathPrefix}components/footer.php`,
        'components/footer.php',
        '../components/footer.php',
        `${pathPrefix}components/footer.php`,
        'components/footer.php',
        '../components/footer.php'
    ]);

    // Initialize page-specific features
    if (typeof initializePageFeatures === 'function') {
        initializePageFeatures();
    }

    // Remove loading class and show content
    document.body.classList.remove('loading');
    document.body.classList.add('loaded');
}

// Call loadComponents before the existing DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadComponents);
} else {
    loadComponents();
}
