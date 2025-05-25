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
  <title>PFRolex Responsive Watches Website</title>
  <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
</head>
<body>

<!-- HEADER -->
<header class="header" id="header">
  <nav class="nav container">
    <a href="#" class="nav__logo"><i class='bx bxs-watch nav__logo-icon'></i> PFRolex</a>
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
      <?php else: ?>
        <button class="login-btn nav__login-btn" id="nav-login-btn">Sign in</button>
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
  <button class="buttonorder" id="place-order">Place Order</button>
</div>

<!-- LOGIN MODAL -->
<div class="login-modal" id="login-modal">
  <div class="login-modal__content">
    <span class="login-modal__close" id="login-close">&times;</span>
    <img src="assets/img/logo.png" alt="Logo" class="login-modal__logo">
    <h2>Welcome to PFRolex</h2>
    <form class="login-form" action="login.php" method="post">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <a href="#">Forgot your password?</a>
      <button type="submit" class="login-btn">Log in</button>
      <div class="or-divider">OR</div>
      <button type="button" class="login-btn fb">Continue with Facebook</button>
      <button type="button" class="login-btn google">Continue with Google</button>
      <p class="signup-text">Not on PFRolex yet? <a href="#" id="switch-to-signup">Sign up</a></p>
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
        <div class="sketchfab-embed-wrapper">
          <iframe title="" frameborder="0" allowfullscreen mozallowfullscreen="true" webkitallowfullscreen="true" allow="autoplay; fullscreen; xr-spatial-tracking" xr-spatial-tracking execution-while-out-of-viewport execution-while-not-rendered web-share src="https://sketchfab.com/models/37c23be725984ce4a311a2782af8e00a/embed?ui_theme=dark"></iframe>
        </div>
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

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  document.getElementById('place-order').addEventListener('click', function () {
    const cartContainer = document.getElementById('cart-container');
    const cartItems = cartContainer.children.length;

    if (cartItems === 0) {
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "You don't have any items in your cart!",
        footer: '<a href="#">Why do I have this issue?</a>'
      });
      return;
    }

    if (window.isLoggedIn) {
      Swal.fire({
        title: 'Order Placed!',
        text: 'Thank you for shopping with PFRolex.',
        icon: 'success',
        confirmButtonText: 'OK'
      }).then(() => {
        // Clear cart UI
        cartContainer.innerHTML = '';
        document.getElementById('cart-items-count').textContent = 'Total Items: 0';
        document.getElementById('cart-total-price').textContent = '₱0';

        // TODO: Also clear cart data if you use localStorage or other storage
      });
    } else {
      Swal.fire({
        title: 'Please Log In',
        text: 'You must be logged in to place an order.',
        icon: 'warning',
        confirmButtonText: 'Log in now'
      }).then(() => {
        document.getElementById('login-modal').style.display = 'block';
      });
    }
  });
</script>

<!-- Make the PHP variable available to JavaScript -->
<script>
  window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
