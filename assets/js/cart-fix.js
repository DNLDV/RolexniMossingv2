/**
 * Fixed cart logic to ensure quantities increment properly
 * This script resolves issues with quantities in the cart
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart from localStorage
    window.cart = window.cart || [];
    try {
        const savedCart = localStorage.getItem('pfrolex_cart');
        if (savedCart) {
            window.cart = JSON.parse(savedCart);
        }
    } catch (e) {
        console.error("Error loading cart:", e);
    }

    // Single source of truth for cart operations
    window.cartFunctions = {
        addToCart: function(title, price, image) {
            if (!window.isLoggedIn) {
                window.showLoginModal?.();
                return;
            }

            const existingIndex = window.cart.findIndex(i => i.title === title);
            if (existingIndex !== -1) {
                window.cart[existingIndex].quantity++;
            } else {
                window.cart.push({ title, price, image, quantity: 1 });
            }

            this.saveAndUpdateCart();
            window.showMessage?.(`${title} added to cart`, '#4caf50');
            window.openCart?.();
        },

        removeFromCart: function(title) {
            const index = window.cart.findIndex(i => i.title === title);
            if (index !== -1) {
                window.cart.splice(index, 1);
                this.saveAndUpdateCart();
                window.showMessage?.(`${title} removed from cart`, '#4caf50');
            }
        },

        changeQuantity: function(title, amount) {
            const item = window.cart.find(i => i.title === title);
            if (!item) return;

            if (amount === 0) {
                this.removeFromCart(title);
                return;
            }

            item.quantity = amount;
            if (item.quantity <= 0) {
                this.removeFromCart(title);
            } else {
                this.saveAndUpdateCart();
                window.showMessage?.(`${title} quantity updated`, '#4caf50');
            }
        },

        saveAndUpdateCart: function() {
            try {
                localStorage.setItem('pfrolex_cart', JSON.stringify(window.cart));
                this.updateDisplay();
            } catch (e) {
                console.error('Error updating cart:', e);
            }
        },

        updateDisplay: function() {
            if (!cartContainer) return;
            
            cartContainer.innerHTML = "";
            let totalItems = 0;
            let totalPrice = 0;

            window.cart.forEach((item) => {
                const itemTotal = item.price * item.quantity;
                totalItems += parseInt(item.quantity);
                totalPrice += itemTotal;

                const cartItem = document.createElement('article');
                cartItem.className = 'cart__card';
                cartItem.innerHTML = `
                    <div class="cart__box">
                        <img src="${item.image}" alt="" class="cart__img">
                    </div>

                    <div class="cart__details">
                        <h3 class="cart__title">${item.title}</h3>
                        <span class="cart__price">₱${item.price}</span>
                        
                        <div class="cart__amount">
                            <div class="cart__amount-content">
                                <span class="cart__amount-box minus">
                                    <i class='bx bx-minus'></i>
                                </span>
                                
                                <span class="cart__amount-number">${item.quantity}</span>
                                
                                <span class="cart__amount-box plus">
                                    <i class='bx bx-plus'></i>
                                </span>
                            </div>

                            <i class='bx bx-trash-alt cart__amount-trash'></i>
                        </div>
                    </div>
                `;

                // Add event listeners immediately for this item
                const plusBtn = cartItem.querySelector('.cart__amount-box.plus');
                plusBtn.addEventListener('click', () => {
                    this.changeQuantity(item.title, item.quantity + 1);
                });

                const minusBtn = cartItem.querySelector('.cart__amount-box.minus');
                minusBtn.addEventListener('click', () => {
                    this.changeQuantity(item.title, item.quantity - 1);
                });

                const trashBtn = cartItem.querySelector('.cart__amount-trash');
                trashBtn.addEventListener('click', () => {
                    this.removeFromCart(item.title);
                });

                cartContainer.appendChild(cartItem);
            });

            if (itemsCountElem) itemsCountElem.textContent = `${totalItems} items`;
            if (totalPriceElem) totalPriceElem.textContent = `₱${totalPrice.toFixed(2)}`;
        }
    };

    // Make functions globally available
    window.addToCart = window.cartFunctions.addToCart.bind(window.cartFunctions);
    window.updateCartDisplay = window.cartFunctions.updateDisplay.bind(window.cartFunctions);

    // Initialize cart display
    window.cartFunctions.updateDisplay();
});
