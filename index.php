<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
$xml = simplexml_load_file("data.xml") or die("Error: Cannot load XML file");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>DVRolex Responsive Watches Website</title>
  <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body>


<!-- HEADER -->
<header class="header" id="header">
  <nav class="nav container">
    <a href="#" class="nav__logo"><i class='bx bxs-watch nav__logo-icon'></i> DvRolex</a>
    <div class="nav__menu" id="nav-menu">
      <ul class="nav__list">
        <li class="nav__item"><a href="#home" class="nav__link active-link">Home</a></li>
        <li class="nav__item"><a href="#featured" class="nav__link">Featured</a></li>
        <li class="nav__item"><a href="#products" class="nav__link">Products</a></li>
        <li class="nav__item"><a href="#new" class="nav__link">New</a></li>
      </ul>
      <div class="nav__close" id="nav-close"><i class='bx bx-x'></i></div>
    </div>

    <div class="nav__btns">
      <i class='bx bx-moon change-theme' id="theme-button"></i>
      <div class="nav__shop" id="cart-shop"><i class='bx bx-shopping-bag'></i></div>

      <?php if ($isLoggedIn): ?>
        <form action="logout.php" method="post" class="logout-form">
          <button type="submit" class="logout-btn">Log out</button>
        </form>
      <?php endif; ?>

      <div class="nav__toggle" id="nav-toggle"><i class='bx bx-grid-alt'></i></div>
    </div>
  </nav>
</header>


<!-- CART -->
<div class="cart" id="cart">
  <i class='bx bx-x cart__close' id="cart-close"></i>
  <h2 class="cart__title-center">My Cart</h2>
  <div class="cart__container" id="cart-container"></div>
  <div class="cart__prices">
    <span class="cart__price-item" id="cart-items-count">Total Items: 0</span>
    <span class="cart__price-total" id="cart-total-price">₱0</span>
  </div>
</div>

<!-- LOGIN MODAL -->
<div class="login-modal" id="login-modal">
  <div class="login-modal__content">
    <span class="login-modal__close" id="login-close">&times;</span>
    <img src="assets/img/logo.png" alt="Logo" class="login-modal__logo">
    <h2>Welcome to DVRolex</h2>
    <form class="login-form" action="login.php" method="post">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <a href="#">Forgot your password?</a>
      <button type="submit" class="login-btn">Log in</button>
      <div class="or-divider">OR</div>
      <button type="button" class="login-btn fb">Continue with Facebook</button>
      <button type="button" class="login-btn google">Continue with Google</button>
      <p class="signup-text">Not on DVRolex yet? <a href="#" id="switch-to-signup">Sign up</a></p>
    </form>
  </div>
</div>


<!-- SIGN UP MODAL -->
<div class="login-modal" id="signup-modal" style="display: none;">
  <div class="login-modal__content">
    <span class="login-modal__close" id="signup-close">&times;</span>
    <img src="assets/img/logo.png" alt="Logo" class="login-modal__logo">
    <h2>Create an account</h2>
    <form class="login-form" action="signup.php" method="post">
      <input type="text" name="fullname" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit" class="login-btn">Sign up</button>
      <p class="signup-text">Already have an account? <a href="#" id="switch-to-login">Log in</a></p>
    </form>
  </div>
</div>



<!-- MAIN -->
<main class="main">
  <!-- HOME -->
  <section class="home" id="home">
    <div class="home__container container grid">
      <div class="home__img-bg">
        <img src="assets/img/home.png" alt="" class="home__img">
      </div>
      <div class="home__data">
        <h1 class="home__title">NEW WATCH <br> COLLECTIONS B720</h1>
        <p class="home__description">Latest arrival of the new imported watches of the B720 series, with a modern and resistant design.</p>
        <span class="home__price">₱1236</span>
        <div class="home__btns">
          <a href="#products" class="button button--gray buttons-small">Discover</a>
          <button class="button home__button add-to-cart"
                  data-title="B720 Collection" data-price="1236" data-image="assets/img/home.png">
            ADD TO CART
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURED -->
  <section class="featured section container" id="featured">
    <h2 class="section__title">Featured</h2>
    <div class="featured__container grid">
      <?php foreach ($xml->featuredProducts->product as $product): ?>
        <article class="featured__card">
          <img src="<?= $product->image ?>" class="featured__img" alt="">
          <span class="featured__tag"><?= $product->tag ?></span>
          <h3 class="featured__title"><?= $product->title ?></h3>
          <span class="featured__price">₱<?= $product->price ?></span>
          <button class="button featured__button add-to-cart"
                  data-title="<?= $product->title ?>" data-price="<?= $product->price ?>" data-image="<?= $product->image ?>">
            Add to Cart
          </button>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- PRODUCTS -->
  <section class="products section container" id="products">
    <h2 class="section__title">Products</h2>
    <div class="products__container grid">
      <?php foreach ($xml->products->product as $product): ?>
        <article class="products__card">
          <img src="<?= $product->image ?>" class="products__img" alt="">
          <h3 class="products__title"><?= $product->title ?></h3>
          <span class="products__price">₱<?= $product->price ?></span>
          <button class="button products__button add-to-cart"
                  data-title="<?= $product->title ?>" data-price="<?= $product->price ?>" data-image="<?= $product->image ?>">
            Add to Cart
          </button>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- NEW -->
  <section class="new section container" id="new">
    <h2 class="section__title">New Arrivals</h2>
    <div class="new__container">
      <div class="swiper new-swiper">
        <div class="swiper-wrapper">
          <?php foreach ($xml->cart->item as $item): ?>
            <div class="new__card swiper-slide">
              <img src="<?= $item->image ?>" class="new__img" alt="">
              <h3 class="new__title"><?= $item->title ?></h3>
              <span class="new__price">₱<?= $item->price ?></span>
              <button class="button new__button add-to-cart"
                      data-title="<?= $item->title ?>" data-price="<?= $item->price ?>" data-image="<?= $item->image ?>">
                Add to Cart
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- FOOTER -->
<footer class="footer section">
  <div class="footer__container container grid">
    <div class="footer__content">
      <h3 class="footer__title">Our information</h3>
      <ul class="footer__list">
        <li>Asia - Philippines </li>
        <li>Bulacan State University</li>
        <li>(044) 919-7800 </li>
      </ul>
    </div>

    <div class="footer__content">
      <h3 class="footer__title">About Us</h3>
      <ul class="footer__list">
        <li><a href="#" class="footer__link">Support Center</a></li>
        <li><a href="#" class="footer__link">Customer Support</a></li>
        <li><a href="#" class="footer__link">About Us</a></li>
        <li><a href="#" class="footer__link">Copy Right</a></li>
      </ul>
    </div>

    <div class="footer__content">
      <h3 class="footer__title">Social Media</h3>
      <div class="footer__social">
        <a href="#" class="footer__social-link"><i class='bx bxl-facebook'></i></a>
        <a href="#" class="footer__social-link"><i class='bx bxl-instagram'></i></a>
      </div>
    </div>
  </div>
  <span class="footer__copy">&#169; 3D-G1. All rights reserved</span>
</footer>

<a href="#" class="scrollup" id="scroll-up">
  <i class='bx bx-up-arrow-alt scrollup__icon'></i>
</a>

<!-- SCRIPTS -->
<script>
  let isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
  let cart = [];

  const cartContainer = document.getElementById("cart-container");
  const itemsCountElem = document.getElementById("cart-items-count");
  const totalPriceElem = document.getElementById("cart-total-price");
  const cartElement = document.getElementById("cart");
  const cartShopBtn = document.getElementById("cart-shop");
  const cartCloseBtn = document.getElementById("cart-close");
  const loginModal = document.getElementById("login-modal");
  const loginClose = document.getElementById("login-close");

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

  document.querySelectorAll(".add-to-cart").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!isLoggedIn) {
        showLoginModal();
        return;
      }
      const title = btn.dataset.title;
      const price = parseFloat(btn.dataset.price);
      const image = btn.dataset.image;

      const item = cart.find(i => i.title === title);
      if (item) item.quantity++;
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
    updateCartDisplay();
  });

  cartShopBtn.addEventListener("click", openCart);
  cartCloseBtn.addEventListener("click", closeCart);
</script>

<script>
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


</script>


<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
