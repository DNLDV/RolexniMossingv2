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

// Load products and featured products into a unified array
$allProducts = [];
if (isset($xml->products->product)) {
    foreach ($xml->products->product as $prod) {
        $allProducts[] = $prod;
    }
}
if (isset($xml->featuredProducts->product)) {
    foreach ($xml->featuredProducts->product as $fprod) {
        $allProducts[] = $fprod;
    }
}

// Build a unique list of categories from the unified products
$categories = [];
foreach ($allProducts as $prod) {
    if (isset($prod->category->name)) {
        $n = (string)$prod->category->name;
        $d = (string)$prod->category->description;
        $categories[$n] = $d;
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
    <?php endif; ?>    <?php if (isset($_GET['removed'])): ?>
      <div class="alert alert-success">Category removed successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
      <?php if ($_GET['updated'] === 'success'): ?>
        <div class="alert alert-success">Product updated successfully.</div>
      <?php elseif ($_GET['updated'] === 'deleted'): ?>
        <div class="alert alert-success">Product deleted successfully.</div>
      <?php elseif ($_GET['updated'] === 'fail'): ?>
        <div class="alert alert-error">Failed to update product.</div>
      <?php endif; ?>
    <?php endif; ?>
    <section class="admin-container">
      <!-- All Products Table -->
      <table class="order-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Price</th>
            <th>Tag</th>
            <th>Category</th>
            <th>Description</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allProducts as $i => $prod): ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($prod->title); ?></td>
            <td>₱<?php echo htmlspecialchars($prod->price); ?></td>
            <td><?php echo htmlspecialchars($prod->tag); ?></td>
            <td>
              <form action="add_category.php" method="post">
                <input type="hidden" name="product_index" value="<?php echo $i; ?>">
                <select name="cat_name">
                  <?php foreach ($categories as $catName => $catDesc): ?>
                    <option value="<?php echo htmlspecialchars($catName); ?>" 
                            <?php echo (isset($prod->category->name) && (string)$prod->category->name === $catName) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($catName); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit">Update</button>
              </form>
            </td>
            <td>
              <div contenteditable="true" class="editable-description" data-product-index="<?php echo $i; ?>">
                <?php echo htmlspecialchars($prod->description ?? ''); ?>
              </div>
            </td>
            <td>
              <form action="update_product.php" method="post" enctype="multipart/form-data" style="display: inline;">
                <input type="hidden" name="product_index" value="<?php echo $i; ?>">
                <button type="submit" name="action" value="update">Update</button>
                <button type="submit" name="action" value="delete">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Product Management Forms -->
      <table class="product management table">
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
                <label for="manage-select">Select Product</label>                <select id="manage-select" name="product_index" required>
                  <option value="">-- Choose Product --</option>
                  <?php foreach ($allProducts as $i => $mprod): ?>
                    <option value="<?= $i ?>"
                            data-title="<?= htmlspecialchars($mprod->title) ?>"
                            data-price="<?= htmlspecialchars($mprod->price) ?>"
                            data-tag="<?= htmlspecialchars($mprod->tag) ?>"
                            data-quantity="<?= htmlspecialchars((string)$mprod->quantity ?: '') ?>"
                            data-desc="<?= htmlspecialchars((string)$mprod->description ?: '') ?>"
                            data-cat="<?= htmlspecialchars(isset($mprod->category->name)?$mprod->category->name:'') ?>"
                            data-catdesc="<?= htmlspecialchars(isset($mprod->category->description)?$mprod->category->description:'') ?>">
                      <?= htmlspecialchars($mprod->title) . (in_array($mprod, iterator_to_array($xml->featuredProducts->product ?? [], false)) ? ' (Featured)' : '') ?>
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
    </section>    <!-- Category Creation Table Section -->
    <section class="admin-container">
      <table class="order-table category-table">
        <thead>
          <tr>
            <th colspan="2">Create New Category</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <form action="add_category.php" method="post" class="category-creation-form">
              <td>
                <div class="form-field">
                  <label for="category_name">Category Name:</label>
                  <input type="text" id="category_name" name="category_name" required>
                </div>
              </td>
              <td>
                <div class="form-field">
                  <label for="category_description">Category Description:</label>
                  <textarea id="category_description" name="category_description" required></textarea>
                </div>
                <div class="form-action">
                  <button type="submit" class="btn-action">Create Category</button>
                </div>
              </td>
            </form>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</div>
<script>
        // Handle inline description editing
        document.querySelectorAll('.editable-description').forEach(desc => {
          desc.addEventListener('blur', function() {
            const productIndex = this.dataset.productIndex;
            const newDescription = this.innerText;
            
            // Create form data
            const formData = new FormData();
            formData.append('product_index', productIndex);
            formData.append('description', newDescription);
            formData.append('action', 'update');

            // Send AJAX request
            fetch('update_product.php', {
              method: 'POST',
              body: formData
            })
            .then(response => response.text())
            .then(result => {
              if (result.includes('success')) {
                this.style.backgroundColor = '#e8f5e9';
                setTimeout(() => this.style.backgroundColor = '', 1000);
              } else {
                this.style.backgroundColor = '#ffebee';
                setTimeout(() => this.style.backgroundColor = '', 1000);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              this.style.backgroundColor = '#ffebee';
              setTimeout(() => this.style.backgroundColor = '', 1000);
            });
          });
        });
      </script>
</body>
</html>
