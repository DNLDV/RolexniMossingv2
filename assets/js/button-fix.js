// This script fixes the issue with Add to Cart buttons not being clickable
document.addEventListener('DOMContentLoaded', function() {
    function handleAddToCart(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const title = this.dataset.title || "Unknown Product";
        const price = parseFloat(this.dataset.price || 0);
        const image = this.dataset.image || "";

        if (typeof window.addToCart === 'function') {
            window.addToCart(title, price, image);
        }
    }

    function fixAddToCartButtons() {
        document.querySelectorAll('.products__button, .featured__button, .new__button, .add-to-cart, .home__button').forEach(button => {
            if (!button.dataset.listenerAttached) {
                button.dataset.listenerAttached = true;
                // Remove any existing listeners by cloning
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);
                newButton.addEventListener('click', handleAddToCart);
            }
        });
    }

    // Run immediately
    fixAddToCartButtons();

    // Observer for dynamic content
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            if (mutation.addedNodes.length) {
                fixAddToCartButtons();
                break;
            }
        }
    });

    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
});
