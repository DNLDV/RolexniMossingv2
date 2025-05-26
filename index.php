<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
$xml = simplexml_load_file("data.xml") or die("Error: Cannot load XML file");

// Load categories from XML for the main menu
$categoriesXml = false;
if (file_exists("categories.xml")) {
    $categoriesXml = simplexml_load_file("categories.xml");
    if ($categoriesXml === false) {
        echo "<!-- Error parsing categories.xml -->";
    }
}

// Debug XML structure
// echo "<!-- "; print_r($categoriesXml); echo " -->";

// Get selected category and view mode
$selectedCategory = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'gallery'; // 'gallery' or 'list'
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PFRolex Responsive Watches Website</title>
  <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/toast.css" />
  <link rel="stylesheet" href="css/loading.css" />
  <link rel="stylesheet" href="css/search.css" />
    <style>
    /* Dropdown Menu Styles */
    .nav__dropdown {
      position: relative;
    }
    
    .nav__dropdown:hover .nav__dropdown-menu {
      display: block;
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
      .nav__dropdown-menu {
      position: absolute;
      top: 100%;
      left: 0;
      min-width: 180px;
      background: var(--container-color);
      box-shadow: 0 4px 16px hsla(0, 0%, 0%, 0.1);
      border-radius: 0.5rem;
      padding: 0.5rem 0;
      margin-top: 0.5rem;
      z-index: 100;
      display: none;
      opacity: 0;
      visibility: hidden;
      transform: translateY(10px);
      transition: all 0.3s ease;
      max-height: 300px;
      overflow-y: auto;
    }
    
    .nav__dropdown-link {
      display: block;
      padding: 0.5rem 1.5rem;
      color: var(--title-color);
      font-size: var(--normal-font-size);
      transition: all 0.3s;
    }
    
    .nav__dropdown-link:hover {
      background: var(--first-color-lighten);
      color: var(--first-color);
      padding-left: 2rem;
    }
    
    /* Category Filter Styles */
    .category-filter {
      background: var(--container-color);
      padding: 1.5rem;
      margin: 2rem 0;
      border-radius: 0.75rem;
      box-shadow: 0 4px 16px hsla(0, 0%, 0%, 0.1);
    }
    
    .filter-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .filter-title {
      font-size: var(--h3-font-size);
      font-weight: var(--font-semi-bold);
      color: var(--title-color);
    }
    
    .view-toggle {
      display: flex;
      gap: 0.5rem;
    }
    
    .view-btn {
      padding: 0.5rem 1rem;
      border: 1px solid var(--first-color);
      background: transparent;
      color: var(--first-color);
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.3s;
      font-size: var(--small-font-size);
    }
    
    .view-btn.active,
    .view-btn:hover {
      background: var(--first-color);
      color: var(--white-color);
    }
    
    .category-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }
    
    .category-tag {
      padding: 0.5rem 1rem;
      background: var(--body-color);
      border: 1px solid var(--border-color);
      border-radius: 2rem;
      cursor: pointer;
      transition: all 0.3s;
      font-size: var(--small-font-size);
      color: var(--text-color);
      text-transform: capitalize;
    }
    
    .category-tag:hover {
      border-color: var(--first-color);
      color: var(--first-color);
    }
    
    .category-tag.active {
      background: var(--first-color);
      border-color: var(--first-color);
      color: var(--white-color);
    }
    
    .clear-filter {
      background: var(--first-color-alt);
      color: var(--white-color);
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      font-size: var(--small-font-size);
    }
    
    /* List View Styles */
    .products__container.list-view {
      display: block;
    }
    
    .products__card.list-item {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      padding: 1.5rem;
      margin-bottom: 1rem;
      background: var(--container-color);
      border-radius: 0.75rem;
      box-shadow: 0 4px 16px hsla(0, 0%, 0%, 0.1);
    }
    
    .products__card.list-item .products__img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 0.5rem;
      flex-shrink: 0;
    }
    
    .products__card.list-item .card-content {
      flex: 1;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .products__card.list-item .product-info {
      flex: 1;
    }
    
    .products__card.list-item .products__title {
      font-size: var(--h3-font-size);
      margin-bottom: 0.5rem;
    }
    
    .products__card.list-item .products__price {
      font-size: var(--h3-font-size);
      font-weight: var(--font-semi-bold);
      color: var(--first-color);
      margin-bottom: 1rem;
    }
    
    .product-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin: 0.5rem 0;
    }
    
    .product-tag {
      background: var(--first-color-lighten);
      color: var(--first-color);
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: var(--smaller-font-size);
      text-transform: uppercase;
      font-weight: var(--font-medium);
    }
      .filter-results {
      text-align: center;
      margin: 2rem 0;
      color: var(--text-color-light);
      transition: opacity 0.3s ease;
    }
    
    .no-results {
      text-align: center;
      padding: 3rem;
      color: var(--text-color-light);
    }
    
    .no-results i {
      font-size: 3rem;
      margin-bottom: 1rem;
      display: block;
    }
    
    @media screen and (max-width: 768px) {
      .filter-header {
        flex-direction: column;
        align-items: stretch;
      }
      
      .view-toggle {
        justify-content: center;
      }
      
      .products__card.list-item {
        flex-direction: column;
        text-align: center;
      }
      
      .products__card.list-item .card-content {
        flex-direction: column;
        gap: 1rem;
      }
    }
  </style>

  <script>
    $(document).ready(function() {
      $("#btn").click(function() {
        $("#test").load("data.txt");
      });
    });
    
    // Global variable to store login status for JavaScript access
    window.isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
  </script>
</head>
<body>

<?php if (isset($_SESSION['error'])): ?>
  <div style="background:#ffdddd;color:#a00;padding:10px;text-align:center;"> <?= $_SESSION['error']; unset($_SESSION['error']); ?> </div>
<?php endif; ?>
<?php if (isset($_SESSION['success'])): ?>
  <div style="background:#ddffdd;color:#080;padding:10px;text-align:center;"> <?= $_SESSION['success']; unset($_SESSION['success']); ?> </div>
<?php endif; ?>


<!-- HEADER -->
<header class="header" id="header">
  <nav class="nav container">
    <a href="#" class="nav__logo"><i class='bx bxs-watch nav__logo-icon'></i> PFRolex</a>
    <div class="nav__menu" id="nav-menu">
      <ul class="nav__list">
        <li class="nav__item"><a href="#home" class="nav__link active-link">Home</a></li>
        <li class="nav__item"><a href="#featured" class="nav__link">Featured</a></li>
        <li class="nav__item"><a href="#products" class="nav__link">Products</a></li>
        
        <?php if ($categoriesXml && isset($categoriesXml->category)): ?>          <!-- Dynamic Categories Menu -->
          <li class="nav__item nav__dropdown">            <a href="#" class="nav__link">Categories <i class='bx bx-chevron-down'></i></a>
            <ul class="nav__dropdown-menu">
              <?php foreach ($categoriesXml->category as $category): 
                // Get category name, check both 'name' and 'n' tags
                $categoryName = isset($category->name) ? (string)$category->name : (isset($category->n) ? (string)$category->n : '');
                if (!empty($categoryName)):
              ?>
                <li>
                  <a href="index.php?category=<?= strtolower(trim($categoryName)) ?>" class="nav__dropdown-link">
                    <?= htmlspecialchars($categoryName) ?>
                  </a>
                </li>
              <?php endif; endforeach; ?>
            </ul>
          </li>
        <?php endif; ?>
        
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
        <button class="logout-btn" onclick="document.getElementById('login-modal').style.display='flex'">Log in</button>
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

<!-- Order Message Toast -->
<div id="order-message" class="hidden"></div>

<!-- LOGIN MODAL -->
<div class="login-modal" id="login-modal">
  <div class="login-modal__content">
    <span class="login-modal__close" id="login-close">&times;</span>
    <img src="assets/img/logo.png" alt="Logo" class="login-modal__logo">
    <h2>Welcome to PFRolex</h2>
    <form class="login-form" action="login.php" method="post">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <a href="forgot_password.php" style="float:right;font-size:0.95em;">Forgot your password?</a>
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
      <div class="g-recaptcha" data-sitekey="6Lctn0krAAAAAIUruw-mqL-Iy95og1ofnILHqgvS"></div>
      <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
      <?php
        $searchTag = isset($_GET['tag']) ? strtolower(trim($_GET['tag'])) : '';
        $hasResults = false;

        foreach ($xml->featuredProducts->product as $product):
          $productTag = strtolower(trim((string)$product->tag));

          // If no search or the tag matches the search query, show it
          if ($searchTag === '' || strpos($productTag, $searchTag) !== false):
            $hasResults = true;
      ?>
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
      <?php
          endif;
        endforeach;

        if (!$hasResults):
      ?>
        <p style="text-align: center; margin-top: 1rem;">No products found with tag: <strong><?= htmlspecialchars($_GET['tag']) ?></strong></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- PRODUCTS -->
  <section class="products section container" id="products">
    <h2 class="section__title">Products</h2>
    
    <!-- Category Filter -->
    <div class="category-filter">
      <div class="filter-header">
        <h3 class="filter-title">Filter by Category</h3>
        <div class="view-toggle">
          <button class="view-btn <?= $viewMode === 'gallery' ? 'active' : '' ?>" data-view="gallery">
            <i class='bx bx-grid-alt'></i> Gallery
          </button>
          <button class="view-btn <?= $viewMode === 'list' ? 'active' : '' ?>" data-view="list">
            <i class='bx bx-list-ul'></i> List
          </button>
        </div>
      </div>
        <div class="category-tags">
        <?php
          // Original hardcoded tags
          $availableTags = ['sale', 'limited', 'classic', 'new', 'premium', 'sport', 'casual', 'minimal','legend', 'light', 'luxury', 'bold', 'military', 'dress', 'swiss', 'reliable'];
          
          // Add categories from categories.xml
          if ($categoriesXml && isset($categoriesXml->category)) {
            foreach ($categoriesXml->category as $category) {
              // Check both 'name' and 'n' tags for compatibility
              $categoryName = isset($category->name) ? (string)$category->name : (isset($category->n) ? (string)$category->n : '');
              if (!empty($categoryName) && !in_array(strtolower($categoryName), $availableTags)) {
                $availableTags[] = strtolower($categoryName);
              }
            }
          }
          
          // Display all tags
          foreach ($availableTags as $tag):
        ?>
          <span class="category-tag <?= $selectedCategory === $tag ? 'active' : '' ?>" data-category="<?= $tag ?>">
            <?= ucfirst($tag) ?>
          </span>
        <?php endforeach; ?>
      </div>
        <?php if ($selectedCategory): ?>
        <button class="clear-filter" onclick="clearCategoryFilter(); document.getElementById('filter-results-container')?.remove();">
          <i class='bx bx-x'></i> Clear Filter
        </button>
      <?php endif; ?>
    </div>

    <!-- Product Search Bar -->
    <div class="search-bar container">
      <input type="text" id="product-search" placeholder="Search by product name, tag, or category..." />
      <div id="search-results" class="search-results container" style="display: none;">
        <!-- Search results will be displayed here -->
      </div>
    </div>

    <?php
      $products = $xml->products->product;
      $filteredProducts = [];
      
      // Filter products by category if selected
      foreach ($products as $product) {
        $productTags = isset($product->tag) ? strtolower(trim((string)$product->tag)) : '';
        
        if ($selectedCategory === '' || strpos($productTags, $selectedCategory) !== false) {
          $filteredProducts[] = $product;
        }
      }
      
      $totalProducts = count($filteredProducts);
      $perPage = 6;
      $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $totalPages = ceil($totalProducts / $perPage);
      $startIndex = ($currentPage - 1) * $perPage;
    ?>      <?php if ($selectedCategory): ?>
      <div class="filter-results" id="filter-results-container">
        <p>Showing <?= $totalProducts ?> products for category: <strong><?= ucfirst($selectedCategory) ?></strong></p>
      </div>    <?php endif; ?>

    <!-- Loading indicator for AJAX -->
    <div id="products-loading" class="loading-indicator" style="display:none;">
      <div class="spinner"></div>
      <p>Loading products...</p>
    </div>

    <div class="products__container <?= $viewMode === 'list' ? 'list-view' : 'grid' ?>" id="product-container">
      <?php
        if ($totalProducts > 0):
          for ($i = $startIndex; $i < $startIndex + $perPage && $i < $totalProducts; $i++):
            $product = $filteredProducts[$i];
            $productTags = isset($product->tag) ? explode(',', (string)$product->tag) : [];
      ?>
        <article class="products__card <?= $viewMode === 'list' ? 'list-item' : '' ?>">
          <img src="<?= $product->image ?>" class="products__img" alt="">
          
          <?php if ($viewMode === 'list'): ?>
            <div class="card-content">
              <div class="product-info">
                <h3 class="products__title"><?= $product->title ?></h3>
                <?php if (!empty($productTags)): ?>
                  <div class="product-tags">
                    <?php foreach ($productTags as $tag): ?>
                      <span class="product-tag"><?= trim($tag) ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <span class="products__price">₱<?= $product->price ?></span>
              </div>
              <button class="button products__button add-to-cart"
                      data-title="<?= $product->title ?>" data-price="<?= $product->price ?>" data-image="<?= $product->image ?>">
                Add to Cart
              </button>
            </div>
          <?php else: ?>
            <h3 class="products__title"><?= $product->title ?></h3>
            <?php if (!empty($productTags)): ?>
              <div class="product-tags">
                <?php foreach (array_slice($productTags, 0, 2) as $tag): ?>
                  <span class="product-tag"><?= trim($tag) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <span class="products__price">₱<?= $product->price ?></span>
            <button class="button products__button add-to-cart"
                    data-title="<?= $product->title ?>" data-price="<?= $product->price ?>" data-image="<?= $product->image ?>">
              Add to Cart
            </button>          <?php endif; ?>
        </article>
      <?php 
          endfor;
        else:
      ?>
        <div class="no-results">
          <i class='bx bx-search-alt-2'></i>
          <h3>No products found</h3>
          <p>No products match the selected category. Try selecting a different category or clear the filter.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <div class="pagination" id="product-pagination">
      <?php if ($totalPages > 1): ?>
        <?php if ($currentPage > 1): ?>
          <a href="javascript:void(0)" data-page="<?= $currentPage - 1 ?>" class="pagination__prev">Previous</a>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
          <a href="javascript:void(0)" data-page="<?= $page ?>" class="pagination__link <?= $page == $currentPage ? 'active' : '' ?>">
            <?= $page ?>
          </a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
          <a href="javascript:void(0)" data-page="<?= $currentPage + 1 ?>" class="pagination__next">Next</a>
        <?php endif; ?>
      <?php endif; ?>
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
<script>  // Make the PHP variable available to JavaScript
  window.isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    // Handle dropdown menu for mobile and desktop devices
  document.addEventListener('DOMContentLoaded', function() {
    const dropdownItems = document.querySelectorAll('.nav__dropdown');
    
    // For all devices - ensure dropdowns are working properly
    dropdownItems.forEach(item => {
      const dropdownLink = item.querySelector('.nav__link');
      const dropdown = item.querySelector('.nav__dropdown-menu');
      
      // Force show for debugging - comment out in production
      // dropdown.style.display = 'block';
      // dropdown.style.opacity = '1';
      // dropdown.style.visibility = 'visible';
      // dropdown.style.transform = 'translateY(0)';
      
      // Touch device handling
      dropdownLink.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Close all other dropdowns
        dropdownItems.forEach(otherItem => {
          if (otherItem !== item) {
            const otherDropdown = otherItem.querySelector('.nav__dropdown-menu');
            if (otherDropdown) {
              otherDropdown.style.display = 'none';
              otherDropdown.style.opacity = '0';
              otherDropdown.style.visibility = 'hidden';
              otherDropdown.style.transform = 'translateY(10px)';
            }
          }
        });
        
        // Toggle current dropdown
        if (dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
          dropdown.style.opacity = '0';
          dropdown.style.visibility = 'hidden';
          dropdown.style.transform = 'translateY(10px)';
        } else {
          dropdown.style.display = 'block';
          dropdown.style.opacity = '1';
          dropdown.style.visibility = 'visible';
          dropdown.style.transform = 'translateY(0)';
        }
      });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.nav__dropdown')) {
        dropdownItems.forEach(item => {
          const dropdown = item.querySelector('.nav__dropdown-menu');
          if (dropdown) {
            dropdown.style.display = 'none';
            dropdown.style.opacity = '0';
            dropdown.style.visibility = 'hidden';
            dropdown.style.transform = 'translateY(10px)';
          }
        });
      }
    });
  });
  
  // Category filtering functionality is now handled by filter.js
  
  // Enhanced search functionality
  document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("product-search");
    const cards = document.querySelectorAll(".products__card, .featured__card");

    if (searchInput) {
      searchInput.addEventListener("input", () => {
        const query = searchInput.value.trim().toLowerCase();

        cards.forEach(card => {
          const title = card.querySelector(".products__title, .featured__title")?.textContent.toLowerCase() || '';
          const tags = Array.from(card.querySelectorAll(".product-tag, .featured__tag"))
            .map(tag => tag.textContent.toLowerCase())
            .join(' ');
          
          if (title.includes(query) || tags.includes(query)) {
            card.style.display = "block";
          } else {
            card.style.display = query === "" ? "block" : "none";
          }
        });
      });
    }
  });
</script>

<!-- AJAX Scripts -->
<script src="assets/js/filter.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/search.js"></script>
</body>
</html>