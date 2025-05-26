// This script fixes the issue with Add to Cart buttons not being clickable
document.addEventListener('DOMContentLoaded', function() {
    // First, ensure that all add to cart buttons have proper event listeners
    function fixAddToCartButtons() {
        // Target all add to cart buttons in the document
        document.querySelectorAll('.products__button, .featured__button, .new__button, .add-to-cart, .home__button').forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent the event from bubbling up to the card
                e.stopPropagation();
                
                // Extract product data
                const title = this.dataset.title || "Unknown Product";
                const price = parseFloat(this.dataset.price || 0);
                const image = this.dataset.image || "";
                
                // Check login status
                if (!window.isLoggedIn) {
                    if (typeof window.showLoginModal === 'function') {
                        window.showLoginModal();
                    } else {
                        alert('Please log in to add items to your cart');
                    }
                    return;
                }
                
                // Add to cart logic
                if (typeof window.cart !== 'undefined') {
                    const item = window.cart.find(i => i.title === title);
                    if (item) {
                        item.quantity++;
                        if (typeof window.showMessage === 'function') {
                            window.showMessage(`${title} quantity increased in cart`, '#4caf50');
                        }
                    } else {
                        window.cart.push({
                            title: title,
                            price: price,
                            image: image,
                            quantity: 1
                        });
                        if (typeof window.showMessage === 'function') {
                            window.showMessage(`${title} added to cart`, '#4caf50');
                        }
                    }
                    
                    // Save to localStorage
                    try {
                        localStorage.setItem('pfrolex_cart', JSON.stringify(window.cart));
                    } catch (e) {
                        console.error("Failed to save cart to localStorage:", e);
                    }
                    
                    // Update cart display
                    if (typeof window.updateCartDisplay === 'function') {
                        window.updateCartDisplay();
                    }
                }
            });
        });
    }
    
    // Run the fix immediately
    fixAddToCartButtons();
    
    // Run the fix again after any AJAX content update (with a slight delay)
    const originalFilterProducts = window.filterProducts;
    if (typeof originalFilterProducts === 'function') {
        window.filterProducts = function() {
            originalFilterProducts.apply(this, arguments);
            setTimeout(fixAddToCartButtons, 500);
        };
    }
    
    // Make this function available globally
    window.fixAddToCartButtons = fixAddToCartButtons;
    
    // Add a mutation observer to catch dynamically added buttons
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length) {
                setTimeout(fixAddToCartButtons, 300);
            }
        });
    });
    
    // Start observing the document with the configured parameters
    observer.observe(document.body, { childList: true, subtree: true });
});
