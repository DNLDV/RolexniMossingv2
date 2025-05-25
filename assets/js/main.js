/*=============== MENU SHOW AND HIDE ===============*/
const navMenu = document.getElementById('nav-menu'),
      navToggle = document.getElementById('nav-toggle'),
      navClose = document.getElementById('nav-close');

if (navToggle) {
  navToggle.addEventListener('click', () => navMenu.classList.add('show-menu'));
}
if (navClose) {
  navClose.addEventListener('click', () => navMenu.classList.remove('show-menu'));
}

/*=============== REMOVE MENU ON LINK CLICK ===============*/
document.querySelectorAll('.nav__link').forEach(link => {
  link.addEventListener('click', () => navMenu.classList.remove('show-menu'));
});

/*=============== CHANGE BACKGROUND HEADER ON SCROLL ===============*/
function scrollHeader() {
  const header = document.getElementById('header');
  if (window.scrollY >= 80) header.classList.add('scroll-header');
  else header.classList.remove('scroll-header');
}
window.addEventListener('scroll', scrollHeader);

/*=============== SHOW SCROLL UP BUTTON ===============*/
function scrollUp() {
  const scrollUp = document.getElementById('scroll-up');
  if (window.scrollY >= 400) scrollUp.classList.add('show-scroll');
  else scrollUp.classList.remove('show-scroll');
}
window.addEventListener('scroll', scrollUp);

/*=============== DARK/LIGHT THEME TOGGLE ===============*/
const themeButton = document.getElementById('theme-button');
const darkTheme = 'dark-theme';
const iconTheme = 'bx-sun';

const selectedTheme = localStorage.getItem('selected-theme');
const selectedIcon = localStorage.getItem('selected-icon');

const getCurrentTheme = () =>
  document.body.classList.contains(darkTheme) ? 'dark' : 'light';
const getCurrentIcon = () =>
  themeButton.classList.contains(iconTheme) ? 'bx-moon' : 'bx-sun';

if (selectedTheme) {
  document.body.classList.toggle(darkTheme, selectedTheme === 'dark');
  themeButton.classList.toggle(iconTheme, selectedIcon === 'bx-moon');
}

themeButton.addEventListener('click', () => {
  document.body.classList.toggle(darkTheme);
  themeButton.classList.toggle(iconTheme);
  localStorage.setItem('selected-theme', getCurrentTheme());
  localStorage.setItem('selected-icon', getCurrentIcon());
});

/*=============== SWIPER SLIDER ===============*/
// Make sure Swiper is available globally (include Swiper JS before this script)
const swiper = new window.Swiper('.new-swiper', {
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
let cart = [];

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
loginClose?.addEventListener("click", hideLoginModal);
window.addEventListener("keydown", (e) => {
  if (e.key === "Escape") hideLoginModal();
});

// Show alert message (toast style)
function showMessage(text, color = '#4caf50') {
  if (!orderMessageElem) return;
  orderMessageElem.textContent = text;
  orderMessageElem.style.backgroundColor = color;
  orderMessageElem.classList.remove('hidden');
  setTimeout(() => orderMessageElem.classList.add('hidden'), 3000);
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
  totalPriceElem.textContent = `₱${totalPrice.toFixed(2)}`;
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll(".add-to-cart").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!window.isLoggedIn) {
        showLoginModal();
        return;
      }

      const title = btn.dataset.title;
      const price = parseFloat(btn.dataset.price);
      const image = btn.dataset.image;

      const existing = cart.find(i => i.title === title);
      if (existing) existing.quantity++;
      else cart.push({ title, price, image, quantity: 1 });

      updateCartDisplay();
      openCart();
    });
  });

  cartContainer.addEventListener("click", (e) => {
    const index = e.target.dataset.index;
    if (e.target.closest(".cart__remove")) {
      cart.splice(index, 1);
    } else if (e.target.classList.contains("plus")) {
      cart[index].quantity++;
    } else if (e.target.classList.contains("minus")) {
      cart[index].quantity > 1 ? cart[index].quantity-- : cart.splice(index, 1);
    }
  });

  cartShopBtn.addEventListener("click", openCart);
  cartCloseBtn.addEventListener("click", closeCart);
  
  // Place Order button functionality
  document.getElementById('place-order').addEventListener('click', () => {
    if (!window.isLoggedIn) {
      alert("Please sign in first!");
      return;
    }
    if (cart.length === 0) {
      alert("Your cart is empty!");
      return;
    }
    fetch('placeorder.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items: cart })
    })
    .then(res => res.text())
    .then(res => {
      if (res === 'success') {
        alert("Order placed successfully!");
        cart = [];
        updateCartDisplay();
      } else {
        alert("Order failed: " + res);
      }
    });
  });
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
    if (e.key === "Escape") signupModal.style.display = "none";
  });

  const navLoginBtn = document.getElementById("nav-login-btn");
  navLoginBtn?.addEventListener("click", showLoginModal);
});
