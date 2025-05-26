/*=============== MENU SHOW AND HIDE ===============*/
const navMenu = document.getElementById('nav-menu'),
      navToggle = document.getElementById('nav-toggle'),
      navClose = document.getElementById('nav-close');

if(navToggle){
  navToggle.addEventListener('click', () => {
    navMenu.classList.add('show-menu');
  });
}

if(navClose){
  navClose.addEventListener('click', () => {
    navMenu.classList.remove('show-menu');
  });
}

/*=============== REMOVE MENU ON LINK CLICK ===============*/
const navLinks = document.querySelectorAll('.nav__link');

navLinks.forEach(n => n.addEventListener('click', (e) => {
  e.preventDefault();
  const href = n.getAttribute('href');
  const currentScroll = window.scrollY;

  // Close menu
  navMenu.classList.remove('show-menu');

  // Handle navigation
  if (href.startsWith('#')) {
    const targetElement = document.querySelector(href);
    if (targetElement) {
      // Smooth scroll to element
      targetElement.scrollIntoView({ behavior: 'smooth' });
    } else {
      // If no target found, restore current position
      window.scrollTo(0, currentScroll);
    }
  }
}));

/*=============== CHANGE BACKGROUND HEADER ON SCROLL ===============*/
function scrollHeader(){
  const header = document.getElementById('header');
  if(this.scrollY >= 80) header.classList.add('scroll-header');
  else header.classList.remove('scroll-header');
}
window.addEventListener('scroll', scrollHeader);

/*=============== SHOW SCROLL UP BUTTON ===============*/
function scrollUp(){
  const scrollUp = document.getElementById('scroll-up');
  if(this.scrollY >= 400) scrollUp.classList.add('show-scroll');
  else scrollUp.classList.remove('show-scroll');
}
window.addEventListener('scroll', scrollUp);

/*=============== DARK/LIGHT THEME TOGGLE ===============*/
const themeButton = document.getElementById('theme-button');
const darkTheme = 'dark-theme';
const iconTheme = 'bx-sun';

// Previously selected theme (if user selected)
const selectedTheme = localStorage.getItem('selected-theme');
const selectedIcon = localStorage.getItem('selected-icon');

// Get current theme
const getCurrentTheme = () => document.body.classList.contains(darkTheme) ? 'dark' : 'light';
const getCurrentIcon = () => themeButton.classList.contains(iconTheme) ? 'bx-moon' : 'bx-sun';

// Apply previously selected theme and icon
if(selectedTheme){
  if(selectedTheme === 'dark') document.body.classList.add(darkTheme);
  else document.body.classList.remove(darkTheme);

  if(selectedIcon === 'bx-moon') themeButton.classList.add(iconTheme);
  else themeButton.classList.remove(iconTheme);
}

// Toggle theme & save to localStorage
themeButton.addEventListener('click', () => {
  document.body.classList.toggle(darkTheme);
  themeButton.classList.toggle(iconTheme);
  
  localStorage.setItem('selected-theme', getCurrentTheme());
  localStorage.setItem('selected-icon', getCurrentIcon());
});

/*=============== SWIPER SLIDER ===============*/
const swiper = new Swiper('.new-swiper', {
  spaceBetween: 24,
  loop: true,
  grabCursor: true,
  centeredSlides: true,
  slidesPerView: 'auto',
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  breakpoints: {
    576: { slidesPerView: 2 },
    768: { slidesPerView: 3 },
    1024: { slidesPerView: 4 },
  }
});

/*=============== CART FUNCTIONALITY ===============*/
// Load cart from localStorage if available
window.cart = [];
try {
  const savedCart = localStorage.getItem('pfrolex_cart');
  if (savedCart) {
    window.cart = JSON.parse(savedCart);
  }
} catch (e) {
  console.error("Error loading cart from localStorage:", e);
  window.cart = [];
}
// For backward compatibility
let cart = window.cart;

const cartContainer = document.getElementById("cart-container");
const itemsCountElem = document.getElementById("cart-items-count");
const totalPriceElem = document.getElementById("cart-total-price");
const cartElement = document.getElementById("cart");
const cartShopBtn = document.getElementById("cart-shop");
const cartCloseBtn = document.getElementById("cart-close");
const loginModal = document.getElementById("login-modal");
const loginClose = document.getElementById("login-close");
const orderMessageElem = document.getElementById("order-message");

// Expose these functions to the global scope for use by other scripts
window.openCart = function() {
  cartElement.classList.add("show-cart");
};

window.closeCart = function() {
  cartElement.classList.remove("show-cart");
};

window.showLoginModal = function() {
  loginModal.style.display = "flex";
};

window.hideLoginModal = function() {
  loginModal.style.display = "none";
};

// Create local function references
function openCart() { window.openCart(); }
function closeCart() { window.closeCart(); }
function showLoginModal() { window.showLoginModal(); }
function hideLoginModal() { window.hideLoginModal(); }

loginClose.addEventListener("click", hideLoginModal);
window.addEventListener("keydown", (e) => { if (e.key === "Escape") hideLoginModal(); });

// Show toast message
window.showMessage = function(text, color = '#4caf50') {
  if (!orderMessageElem) return;
  
  orderMessageElem.textContent = text;
  orderMessageElem.style.backgroundColor = color;
  orderMessageElem.classList.remove('hidden');
  
  setTimeout(() => {
    orderMessageElem.classList.add('hidden');
  }, 3000);
};
function showMessage(text, color) { window.showMessage(text, color); }

window.updateCartDisplay = function() {
  cartContainer.innerHTML = "";
  let totalItems = 0;
  let totalPrice = 0;

  window.cart.forEach((item, index) => {
    cartContainer.innerHTML += `
      <div class="cart__card">
        <img src="${item.image}" class="cart__img" />
        <div class="cart__details">
          <h3 class="cart__title">${item.title}</h3>
          <span class="cart__price">₱${item.price}</span>
          <div class="cart__quantity-controls">
            <button class="quantity-btn minus" data-index="${index}">−</button>
            <span class="cart__quantity">${item.quantity}</span>
            <button class="quantity-btn plus" data-index="${index}">+</button>
          </div>
        </div>
        <button class="cart__remove" data-index="${index}"><i class='bx bx-trash'></i></button>
      </div>
    `;
    totalItems += item.quantity;
    totalPrice += item.quantity * item.price;
  });

  itemsCountElem.textContent = `Total Items: ${totalItems}`;
  totalPriceElem.textContent = `₱${totalPrice}`;
}

// This will be initialized from index.php
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll(".add-to-cart").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!window.isLoggedIn) {
        showLoginModal();
        return;
      }
      const title = btn.dataset.title;
      const price = parseFloat(btn.dataset.price);
      const image = btn.dataset.image;      const item = cart.find(i => i.title === title);
      if (item) {
        item.quantity++;
        showMessage(`${title} quantity increased in cart`, '#4caf50');
      } else {
        cart.push({ title, price, image, quantity: 1 });
        showMessage(`${title} added to cart`, '#4caf50');
      }
      
      // Save to localStorage
      localStorage.setItem('pfrolex_cart', JSON.stringify(cart));

      updateCartDisplay();
      openCart();
    });
  });
  cartContainer.addEventListener("click", (e) => {
    // Remove item
    if (e.target.closest('.cart__remove')) {
      const btn = e.target.closest('.cart__remove');
      const idx = parseInt(btn.dataset.index);
      const removed = cart.splice(idx, 1)[0];
      showMessage(`${removed.title} removed from cart`, '#f44336');
      // Save and update
      localStorage.setItem('pfrolex_cart', JSON.stringify(cart));
      updateCartDisplay();
      return;
    }
    
    // Increase quantity
    if (e.target.classList.contains('plus')) {
      const idx = parseInt(e.target.dataset.index);
      cart[idx].quantity++;
      showMessage(`${cart[idx].title} quantity increased`, '#4caf50');
      localStorage.setItem('pfrolex_cart', JSON.stringify(cart));
      updateCartDisplay();
      return;
    }
    
    // Decrease quantity
    if (e.target.classList.contains('minus')) {
      const idx = parseInt(e.target.dataset.index);
      if (cart[idx].quantity > 1) {
        cart[idx].quantity--;
        showMessage(`${cart[idx].title} quantity decreased`, '#ff9800');
      } else {
        const removed = cart.splice(idx, 1)[0];
        showMessage(`${removed.title} removed from cart`, '#f44336');
      }
      localStorage.setItem('pfrolex_cart', JSON.stringify(cart));
      updateCartDisplay();
      return;
    }
  });
  cartShopBtn.addEventListener("click", openCart);
  cartCloseBtn.addEventListener("click", closeCart);
  
  // Initialize the cart display on page load
  updateCartDisplay();
  
  // Add place order functionality
  const placeOrderBtn = document.getElementById('place-order');
  if (placeOrderBtn) {
    placeOrderBtn.addEventListener('click', () => {
      // Check if cart is empty
      if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
      }
      
      // Check if user is logged in
      if (!window.isLoggedIn) {
        alert("Please log in to place an order");
        showLoginModal();
        return;
      }
      
      // Place order via AJAX
      fetch('placeorder.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ items: cart })
      })
      .then(response => response.text())
      .then(result => {
        if (result === 'success') {
          // Clear cart
          cart = [];
          localStorage.removeItem('pfrolex_cart');
          updateCartDisplay();
          location.reload();
          alert("Order placed successfully!");
          closeCart();
        } else {
          alert("Error placing order: " + result);
        }
      })
      .catch(error => {
        console.error("Error:", error);
        alert("Error placing order. Please try again.");
      });
    });
  }
});

/*=============== LOGIN/SIGNUP MODAL FUNCTIONALITY ===============*/
document.addEventListener('DOMContentLoaded', () => {
  const signupModal = document.getElementById("signup-modal");
  const signupClose = document.getElementById("signup-close");
  const switchToSignup = document.getElementById("switch-to-signup");
  const switchToLogin = document.getElementById("switch-to-login");

  switchToSignup?.addEventListener("click", (e) => {
    e.preventDefault();
    hideLoginModal();
    signupModal.style.display = "flex";
  });

  switchToLogin?.addEventListener("click", (e) => {
    e.preventDefault();
    signupModal.style.display = "none";
    loginModal.style.display = "flex";
  });

  signupClose?.addEventListener("click", () => {
    signupModal.style.display = "none";
  });

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      signupModal.style.display = "none";
    }
  });
});

// Use event delegation for filter links or buttons
const filterContainer = document.body; // Adjust this to a more specific container if possible
filterContainer.addEventListener('click', function(e) {
  const filter = e.target.closest('.filter-link, .filter-button');
  if (!filter) return; // Ignore clicks outside filter elements

  e.preventDefault(); // Prevent default anchor or button behavior

  const targetFilter = filter.getAttribute('data-filter');

  // Save the current scroll position
  const currentScroll = window.scrollY;

  // Show loading indicator
  const loadingIndicator = document.getElementById('products-loading');
  if (loadingIndicator) {
    loadingIndicator.style.display = 'flex';
  }

  // Send AJAX request to fetch filtered products
  fetch(`get_products.php?filter=${encodeURIComponent(targetFilter)}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(data => {
      // Update the product container with the filtered products
      const productContainer = document.getElementById('product-container');
      if (productContainer) {
        productContainer.innerHTML = data;
      }

      // Restore the scroll position
      window.scrollTo({ top: currentScroll, behavior: 'instant' });

      // Hide loading indicator
      if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Error fetching filtered products:', error);

      // Hide loading indicator
      if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
      }

      // Show error message
      alert('Failed to load filtered products. Please try again.');
    });
});

// Handle product deletion via AJAX
document.addEventListener('click', function(e) {
  const deleteButton = e.target.closest('.btn-delete');
  if (!deleteButton) return;

  e.preventDefault();

  const productIndex = deleteButton.dataset.index;

  fetch('admin/update_product.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      action: 'delete',
      product_index: productIndex,
      ajax: true
    })
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        // Optionally, remove the product row from the table
        deleteButton.closest('tr').remove();
      } else {
        alert(data.message);
      }
    })
    .catch(error => {
      console.error('Error deleting product:', error);
      alert('Failed to delete product. Please try again.');
    });
});

// Add event listener to product cards to open the product details modal
const productModal = document.getElementById("product-modal");
const productClose = document.getElementById("product-close");

// Function to fetch and display product details
function showProductDetails(productId) {
  fetch(`get_product_details.php?id=${encodeURIComponent(productId)}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to fetch product details');
      }
      return response.json();
    })
    .then(data => {
      if (!data.success) {
        throw new Error(data.error || 'Failed to load product details');
      }
      
      // Populate modal with product details
      document.getElementById("product-name").textContent = data.name;
      document.getElementById("product-price").textContent = `₱${data.price}`;
      document.getElementById("product-description").textContent = data.description;
      document.getElementById("product-image").src = data.image;

      // Show the modal
      productModal.style.display = "flex";
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to load product details. Please try again.');
    });
}

// Close the product details modal
if (productClose) {
  productClose.addEventListener("click", () => {
    productModal.style.display = "none";
  });
}

// Close modal when clicking outside of it
window.addEventListener("click", (event) => {
  if (event.target === productModal) {
    productModal.style.display = "none";
  }
});

// Adjusting initProductCardHandlers to avoid conflicts with button-fix.js
function initProductCardHandlers() {
  document.querySelectorAll(".products__card, .featured__card, .new__card").forEach(card => {
    const addToCartBtn = card.querySelector('.products__button, .featured__button, .new__button, .add-to-cart');

    // Ensure the Add to Cart button has its own event listener
    if (addToCartBtn) {
      addToCartBtn.addEventListener("click", function(e) {
        e.stopPropagation(); // Prevent triggering the modal
        if (typeof window.updateCartDisplay === 'function') {
          window.updateCartDisplay();
        }
        if (typeof window.openCart === 'function') {
          window.openCart();
        }
      });
    }

    // Add click handler for product details modal
    card.addEventListener("click", function(event) {
      if (event.target.closest('.products__button, .featured__button, .new__button, .add-to-cart')) {
        event.stopPropagation();
        return; // Prevent modal from opening
      }

      const productTitle = card.querySelector(".products__title, .featured__title, .new__title").textContent;
      showProductDetails(productTitle);
    });
  });

  console.log("Product card handlers initialized");
}

document.addEventListener('DOMContentLoaded', () => {
  // Initialize product cards click handlers
  initProductCardHandlers();

  // Initialize pagination event listeners
  document.querySelectorAll("#product-pagination a").forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const page = this.dataset.page;
      if (page && typeof loadProducts === 'function') {
        loadProducts(page);
        // Re-initialize product card handlers after content is loaded
        setTimeout(initProductCardHandlers, 500);
      }
    });
  });
}); // Ensure proper closure of DOMContentLoaded event listener

// Fixing missing closing parenthesis in window.loadProducts
if (typeof window.loadProducts === 'function') {
  const originalLoadProducts = window.loadProducts;
  window.loadProducts = function(page) { // Added missing closing parenthesis
    originalLoadProducts(page);
    // Re-initialize product card handlers after content is loaded
    setTimeout(initProductCardHandlers, 500);
  };
}
