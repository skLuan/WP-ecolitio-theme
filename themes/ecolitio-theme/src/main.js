import "../styles/tailwind.css"
import "iconify-icon"

console.log('sisaaa');

// Mobile Navigation Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const header = document.querySelector('header');

    if (mobileMenuToggle && mobileMenu) {
        // Create overlay element
        const overlay = document.createElement('div');
        overlay.className = 'mobile-menu-overlay';
        header.appendChild(overlay);

        // Toggle menu function
        function toggleMenu() {
            const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';

            // Toggle button state
            mobileMenuToggle.setAttribute('aria-expanded', !isExpanded);

            // Toggle menu visibility
            mobileMenu.setAttribute('aria-hidden', isExpanded);

            // Toggle overlay
            if (!isExpanded) {
                overlay.classList.add('active');
                header.style.overflow = 'hidden'; // Prevent background scrolling
            } else {
                overlay.classList.remove('active');
                header.style.overflow = ''; // Restore scrolling
            }
        }

        // Event listeners
        mobileMenuToggle.addEventListener('click', toggleMenu);

        // Close menu when clicking overlay
        overlay.addEventListener('click', toggleMenu);

        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenuToggle.getAttribute('aria-expanded') === 'true') {
                toggleMenu();
            }
        });

        // Close menu when clicking menu links (optional)
        const menuLinks = mobileMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (mobileMenuToggle.getAttribute('aria-expanded') === 'true') {
                    toggleMenu();
                }
            });
        });
    }
});
