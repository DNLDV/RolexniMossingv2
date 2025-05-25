<?php
session_start();
// Security: redirect if not logged in as admin
if (!isset($_SESSION['admin_user'])) {
    header('Location: index.php');
    exit;
}
// Load products XML
$productsFile = __DIR__ . '/../data.xml';
if (!file_exists($productsFile)) {
    die('Products data file not found.');
}
$xml = simplexml_load_file($productsFile);
if (!$xml) {
    die('Failed to load products data.');
}

// Build a unique list of categories from both products & featuredProducts
$categories = [];
if (isset($xml->products->product)) {
  foreach ($xml->products->product as $prod) {
    if (isset($prod->category->name)) {
      $n = (string)$prod->category->name;
      $d = (string)$prod->category->description;
      $categories[$n] = $d;
    }
  }
}
if (isset($xml->featuredProducts->product)) {
  foreach ($xml->featuredProducts->product as $prod) {
    if (isset($prod->category->name)) {
      $n = (string)$prod->category->name;
      $d = (string)$prod->category->description;
      $categories[$n] = $d;
    }
  }
}

// Feedback message
$added = $_GET['added'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Products</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="dashboard-wrapper">
  <aside class="sidebar">
    <h2>Admin Panel</h2>
    <nav>
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="products.php">Products</a>
      <a href="users.php">Users</a>
      <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
    </nav>
    <form id="logout-form" action="logout.php" method="post" style="display:none;"></form>
  </aside>
  <div class="main-content">
    <header class="admin-header">
      <h1>Product Management</h1>
      <form action="logout.php" method="post" style="display:inline;"><button class="btn-logout">Log Out</button></form>
    </header>
    <?php if ($added === 'success'): ?>
      <div class="alert alert-success">Category added successfully.</div>
    <?php elseif ($added === 'fail'): ?>
      <div class="alert alert-error">Failed to add category.</div>
    <?php endif; ?>
    <?php if (isset($_GET['removed'])): ?>
      <div class="alert alert-success">Category removed successfully.</div>
    <?php endif; ?>
    <section class="admin-container">
      <!-- Featured Products Section -->
      <section class="admin-container featured-section">
        <h2>Featured Products</h2>
        <table class="featured-table">
          <thead>
            <tr><th>#</th><th>Title</th><th>Price</th><th>Tag</th><th>Category</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php if (isset($xml->featuredProducts->product)): ?>
              <?php foreach ($xml->featuredProducts->product as $j => $fprod): ?>
              <tr>
                <td><?php echo ((int)$j) + 1; ?></td>
                <td><?php echo htmlspecialchars($fprod->title); ?></td>
                <td>₱<?php echo htmlspecialchars($fprod->price); ?></td>
                <td><?php echo htmlspecialchars($fprod->tag); ?></td>
                <td><?php echo isset($fprod->category->name) ? htmlspecialchars($fprod->category->name) : '-'; ?></td>
                <td>
                  <!-- Add/Edit Category for Featured -->
                  <form action="add_category.php" method="post" class="category-form">
                    <input type="hidden" name="type" value="featured">
                    <input type="hidden" name="product_index" value="<?php echo $j; ?>">
                    <input type="text" name="cat_name" placeholder="Category name" value="<?php echo isset($fprod->category->name) ? htmlspecialchars($fprod->category->name) : ''; ?>" required>
                    <input type="text" name="cat_desc" placeholder="Description" value="<?php echo isset($fprod->category->description) ? htmlspecialchars($fprod->category->description) : ''; ?>" required>
                    <button type="submit" class="btn-action"><?php echo isset($fprod->category) ? 'Update' : 'Add'; ?></button>
                  </form>
                  <?php if (isset($fprod->category)): ?>
                  <form action="delete_category.php" method="post" style="display:inline; margin-left:8px;">
                    <input type="hidden" name="type" value="featured">
                    <input type="hidden" name="product_index" value="<?php echo $j; ?>">
                    <button type="submit" class="btn-delete-category">Remove</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6">No featured products found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
      <!-- End Featured Products -->
      <table class="order-table">
        <thead>
          <tr><th>#</th><th>Title</th><th>Price</th><th>Tag</th><th>Category</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if (isset($xml->products->product)): ?>
            <?php foreach ($xml->products->product as $i => $prod): ?>
          <tr>
            <td><?php echo ((int)$i) + 1; ?></td>
            <td><?php echo htmlspecialchars($prod->title); ?></td>
            <td>₱<?php echo htmlspecialchars($prod->price); ?></td>
            <td><?php echo htmlspecialchars($prod->tag); ?></td>
            <td><?php echo isset($prod->category->name) ? htmlspecialchars($prod->category->name) : '-'; ?></td>
            <td>
              <!-- Add/Edit Category Form -->
              <form action="add_category.php" method="post" class="category-form">
                <input type="hidden" name="product_index" value="<?php echo $i; ?>">
                <input type="text" name="cat_name" placeholder="Category name" value="<?php echo isset($prod->category->name) ? htmlspecialchars($prod->category->name) : ''; ?>" required>
                <input type="text" name="cat_desc" placeholder="Description" value="<?php echo isset($prod->category->description) ? htmlspecialchars($prod->category->description) : ''; ?>" required>
                <button type="submit" class="btn-action"><?php echo isset($prod->category) ? 'Update' : 'Add'; ?></button>
              </form>
              <?php if (isset($prod->category)): ?>
              <!-- Remove Category Button -->
              <form action="delete_category.php" method="post" style="display:inline; margin-left:8px;">
                <input type="hidden" name="product_index" value="<?php echo $i; ?>">
                <button type="submit" class="btn-delete-category">Remove</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6">No products found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>
    <!-- Unified Product Management Table -->
    <section class="admin-container">
      <table class="product-management-table">
        <thead>
          <tr><th>Add New Product</th><th>Manage Existing Products</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <!-- Add New Product Section -->
              <h2>Add New Product</h2>
              <form action="upload_product.php" method="post" enctype="multipart/form-data" class="add-product-form">
                <input type="text" name="title" placeholder="Product Title" required>
                <input type="number" name="price" placeholder="Price" step="0.01" required>
                <input type="text" name="tag" placeholder="Tag (e.g., Sale, New)" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <textarea name="description" placeholder="Brief Description" rows="3" required></textarea>
                <label for="category">Category</label>
                <select name="category" id="category" required>
                  <option value="">-- Select Category --</option>
                  <?php foreach ($categories as $catName => $catDesc): ?>
                    <option value="<?= htmlspecialchars($catName) ?>"
                            data-desc="<?= htmlspecialchars($catDesc) ?>">
                      <?= htmlspecialchars($catName) ?>
                    </option>
                  <?php endforeach; ?>
                </select>

                <label for="category-desc">Category Description</label>
                <textarea name="category_desc" id="category-desc" rows="2" placeholder="Description" required></textarea>

                <input type="file" name="image" accept="image/*" required>
                <button type="submit" class="btn-action">Upload Product</button>
              </form>
              <script>
              // When you pick a category, auto-fill its description
              document.getElementById('category').addEventListener('change', function() {
                var opt = this.selectedOptions[0];
                document.getElementById('category-desc').value = opt.dataset.desc || '';
              });
              </script>
              <!-- End Add New Product -->
            </td>
            <td>
              <!-- Manage Existing Products -->
              <h2>Manage Existing Products</h2>
              <form id="manage-product-form" action="update_product.php" method="post" enctype="multipart/form-data" class="manage-product-form">
                <label for="manage-select">Select Product</label>
                <select id="manage-select" name="product_index" required>
                  <option value="">-- Choose Product --</option>
                  <?php foreach ($xml->products->product as $i => $mprod): ?>
                    <option value="<?= $i ?>"
                            data-title="<?= htmlspecialchars($mprod->title) ?>"
                            data-price="<?= htmlspecialchars($mprod->price) ?>"
                            data-tag="<?= htmlspecialchars($mprod->tag) ?>"
                            data-quantity="<?= htmlspecialchars((string)$mprod->quantity ?: '') ?>"
                            data-desc="<?= htmlspecialchars((string)$mprod->description ?: '') ?>"
                            data-cat="<?= htmlspecialchars(isset($mprod->category->name)?$mprod->category->name:'') ?>"
                            data-catdesc="<?= htmlspecialchars(isset($mprod->category->description)?$mprod->category->description:'') ?>">
                      <?= htmlspecialchars($mprod->title) ?>
                    </option>
                  <?php endforeach; ?>
                </select>

                <input type="text" name="title" id="manage-title" placeholder="Product Title" required>
                <input type="number" name="price" id="manage-price" placeholder="Price" step="0.01" required>
                <input type="text" name="tag" id="manage-tag" placeholder="Tag" required>
                <input type="number" name="quantity" id="manage-quantity" placeholder="Quantity">
                <textarea name="description" id="manage-desc" placeholder="Description" rows="2"></textarea>

                <label for="manage-category">Category</label>
                <select name="category" id="manage-category">
                  <option value="">-- None --</option>
                  <?php foreach ($categories as $catName => $catDesc): ?>
                    <option value="<?= htmlspecialchars($catName) ?>" data-desc="<?= htmlspecialchars($catDesc) ?>"><?= htmlspecialchars($catName) ?></option>
                  <?php endforeach; ?>
                </select>
                <textarea name="category_desc" id="manage-category-desc" rows="1" placeholder="Category Description"></textarea>

                <label for="manage-image">Replace Image (optional)</label>
                <input type="file" name="image" id="manage-image" accept="image/*">

                <button type="submit" name="action" value="update" class="btn-action">Update Product</button>
                <button type="submit" name="action" value="delete" class="btn-delete-category" style="margin-left:10px;">Delete Product</button>
              </form>
              <script>
                const manageSelect = document.getElementById('manage-select');
                manageSelect.addEventListener('change', function() {
                  const opt = this.selectedOptions[0];
                  document.getElementById('manage-title').value = opt.dataset.title || '';
                  document.getElementById('manage-price').value = opt.dataset.price || '';
                  document.getElementById('manage-tag').value = opt.dataset.tag || '';
                  document.getElementById('manage-quantity').value = opt.dataset.quantity || '';
                  document.getElementById('manage-desc').value = opt.dataset.desc || '';
                  // set category
                  const cat = opt.dataset.cat || '';
                  const catSelect = document.getElementById('manage-category');
                  catSelect.value = cat;
                  document.getElementById('manage-category-desc').value = opt.dataset.catdesc || '';
                });
              </script>
              <!-- End Manage Products -->
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</div>
</body>
</html>
