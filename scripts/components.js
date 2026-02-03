// Load navbar and footer components
async function loadComponent(elementId, filePath) {
    try {
        const response = await fetch(filePath);
        if (!response.ok) throw new Error(`Failed to load ${filePath}`);
        const html = await response.text();
        document.getElementById(elementId).innerHTML = html;
    } catch (error) {
        console.error('Error loading component:', error);
    }
}

// Load components when DOM is ready
async function loadComponents() {
    // Add loading class to body
    document.body.classList.add('loading');

    await loadComponent('navbar-placeholder', 'components/navbar.html');
    await loadComponent('footer-placeholder', 'components/footer.html');

    // After loading navbar, check login state
    if (typeof checkLoginState === 'function') {
        checkLoginState();
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
