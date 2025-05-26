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

navLinks.forEach(n => n.addEventListener('click', () => {
  navMenu.classList.remove('show-menu');
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
let cart = [];
try {
  const savedCart = localStorage.getItem('pfrolex_cart');
  if (savedCart) {
    cart = JSON.parse(savedCart);
  }
} catch (e) {
  console.error("Error loading cart from localStorage:", e);
  cart = [];
}

const cartContainer = document.getElementById("cart-container");
const itemsCountElem = document.getElementById("cart-items-count");
const totalPriceElem = document.getElementById("cart-total-price");
const cartElement = document.getElementById("cart");
const cartShopBtn = document.getElementById("cart-shop");
const cartCloseBtn = document.getElementById("cart-close");
const loginModal = document.getElementById("login-modal");
const loginClose = document.getElementById("login-close");
const orderMessageElem = document.getElementById("order-message");

function openCart() {
  cartElement.classList.add("show-cart");
}

function closeCart() {
  cartElement.classList.remove("show-cart");
}

function showLoginModal() {
  loginModal.style.display = "flex";
}

function hideLoginModal() {
  loginModal.style.display = "none";
}

loginClose.addEventListener("click", hideLoginModal);
window.addEventListener("keydown", (e) => { if (e.key === "Escape") hideLoginModal(); });

// Show toast message
function showMessage(text, color = '#4caf50') {
  if (!orderMessageElem) return;
  
  orderMessageElem.textContent = text;
  orderMessageElem.style.backgroundColor = color;
  orderMessageElem.classList.remove('hidden');
  
  setTimeout(() => {
    orderMessageElem.classList.add('hidden');
  }, 3000);
}

function updateCartDisplay() {
  cartContainer.innerHTML = "";
  let totalItems = 0;
  let totalPrice = 0;

  cart.forEach((item, index) => {
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
