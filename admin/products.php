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

// Load categories from categories.xml
$categoriesFile = __DIR__ . '/../categories.xml';
$categories = [];

if (file_exists($categoriesFile)) {
    $categoriesXml = simplexml_load_file($categoriesFile);
    if ($categoriesXml) {
        foreach ($categoriesXml->category as $category) {
            $categories[(string)$category->name] = (string)$category->description;
        }
    }
} else {
    // Create empty categories file if it doesn't exist
    $xml = new SimpleXMLElement('<categories></categories>');
    $xml->asXML($categoriesFile);
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
  <link rel="stylesheet" href="../css/loading.css">
  <script defer src="../assets/js/upload.js"></script>
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
            <td>              <div class="category-selector">
                <input type="hidden" name="product_index" value="<?php echo $i; ?>" class="product-index">
                <select name="cat_name" class="category-select" data-product-index="<?php echo $i; ?>">
                  <option value="">-- Select Category --</option>
                  <?php foreach ($categories as $catName => $catDesc): ?>
                    <option value="<?php echo htmlspecialchars($catName); ?>" 
                            data-desc="<?php echo htmlspecialchars($catDesc); ?>"
                            <?php echo (isset($prod->category->name) && (string)$prod->category->name === $catName) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($catName); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="update-category-btn">Update</button>
                <span class="category-status"></span>
              </div>
            </td>
            <td>
              <div contenteditable="true" class="editable-description" data-product-index="<?php echo $i; ?>">
                <?php echo htmlspecialchars($prod->description ?? ''); ?>
              </div>
            </td>            <td>              <form action="update_product.php" method="post" enctype="multipart/form-data" style="display: inline;">
                <input type="hidden" name="product_index" value="<?php echo $i; ?>">
                <button type="submit" name="action" value="update">Update</button>
              </form>
              <?php
              $section = ($prod->getName() === 'product' && $prod->xpath('..')[0]->getName() === 'featuredProducts') ? 'featuredProducts' : 'products';
              $title = htmlspecialchars($prod->title);
              ?>
              <button type="button" class="delete-link" data-index="<?php echo $i; ?>" data-title="<?php echo htmlspecialchars($title); ?>" data-section="<?php echo $section; ?>" style="margin-left: 5px; display: inline-block; padding: 2px 8px; background-color: #f44336; color: white; text-decoration: none; border: none; border-radius: 4px; cursor: pointer;">Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Product Management Forms -->
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
              <form id="manage-product-form" class="manage-product-form">
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
                <input type="file" name="image" id="manage-image" accept="image/*">                <button type="button" id="update-product-btn" class="btn-action">Update Product</button>
                <a href="#" id="delete-selected-product" class="btn-delete-category" style="margin-left:10px; display: inline-block; padding: 8px 12px; background-color: #f44336; color: white; text-decoration: none; border-radius: 4px;">Delete Product</a>
              </form>              <script>
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
                });                // Handle product update with AJAX
                document.getElementById('update-product-btn').addEventListener('click', function(e) {
                  e.preventDefault();
                  
                  const selectedOption = document.getElementById('manage-select').selectedOptions[0];
                  if (!selectedOption || !selectedOption.value) {
                    alert('Please select a product to update.');
                    return;
                  }
                  
                  // Show updating indicator
                  const btn = this;
                  const originalText = btn.textContent;
                  btn.textContent = 'Updating...';
                  btn.disabled = true;
                  
                  // Get form data
                  const productIndex = selectedOption.value;
                  const title = document.getElementById('manage-title').value;
                  const price = document.getElementById('manage-price').value;
                  const tag = document.getElementById('manage-tag').value;
                  const quantity = document.getElementById('manage-quantity').value;
                  const description = document.getElementById('manage-desc').value;
                  const category = document.getElementById('manage-category').value;
                  const categoryDesc = document.getElementById('manage-category-desc').value;
                  const imageFile = document.getElementById('manage-image').files[0];
                  
                  // Create FormData object
                  const formData = new FormData();
                  formData.append('product_index', productIndex);
                  formData.append('title', title);
                  formData.append('price', price);
                  formData.append('tag', tag);
                  formData.append('quantity', quantity);
                  formData.append('description', description);
                  formData.append('category', category);
                  formData.append('category_desc', categoryDesc);
                  formData.append('action', 'update');
                  formData.append('ajax', '1');
                  
                  // Add image file if selected
                  if (imageFile) {
                    formData.append('image', imageFile);
                  }
                  
                  // Send AJAX request
                  fetch('update_product.php', {
                    method: 'POST',
                    body: formData
                  })
                  .then(response => response.json())
                  .then(data => {
                    if (data.status === 'success') {
                      // Show success message
                      const messageDiv = document.createElement('div');
                      messageDiv.className = 'alert alert-success';
                      messageDiv.textContent = data.message;
                      
                      const table = document.querySelector('.product-management-table');
                      table.parentNode.insertBefore(messageDiv, table);
                      
                      // Update the product in the table
                      const tableRows = document.querySelectorAll('.order-table tbody tr');
                      tableRows.forEach(row => {
                        const indexCell = row.querySelector('td:first-child');
                        if (indexCell && parseInt(indexCell.textContent) - 1 == productIndex) {
                          // Update the row data
                          row.querySelector('td:nth-child(2)').textContent = title;
                          row.querySelector('td:nth-child(3)').textContent = '₱' + price;
                          row.querySelector('td:nth-child(4)').textContent = tag;
                          
                          // Highlight row briefly to indicate update
                          row.style.backgroundColor = '#e8f5e9';
                          setTimeout(() => {
                            row.style.backgroundColor = '';
                            row.style.transition = 'background-color 1s ease';
                          }, 1000);
                        }
                      });
                      
                      // Update the option in the select dropdown
                      selectedOption.textContent = title;
                      selectedOption.dataset.title = title;
                      selectedOption.dataset.price = price;
                      selectedOption.dataset.tag = tag;
                      selectedOption.dataset.quantity = quantity;
                      selectedOption.dataset.desc = description;
                      selectedOption.dataset.cat = category;
                      selectedOption.dataset.catdesc = categoryDesc;
                      
                      // Reset the image input
                      document.getElementById('manage-image').value = '';
                      
                      // Auto-hide message after 3 seconds
                      setTimeout(() => {
                        messageDiv.style.opacity = '0';
                        messageDiv.style.transition = 'opacity 0.5s';
                        setTimeout(() => {
                          messageDiv.remove();
                        }, 500);
                      }, 3000);
                    } else {
                      alert('Error: ' + data.message);
                    }
                    btn.textContent = originalText;
                    btn.disabled = false;
                  })
                  .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the product.');
                    btn.textContent = originalText;
                    btn.disabled = false;
                  });
                });
                
                // Handle delete button click in manage section
                document.getElementById('delete-selected-product').addEventListener('click', function(e) {
                  e.preventDefault();
                  const selectedOption = document.getElementById('manage-select').selectedOptions[0];
                  
                  if (!selectedOption || !selectedOption.value) {
                    alert('Please select a product to delete.');
                    return;
                  }
                  
                  if (confirm('Are you sure you want to delete this product?')) {
                    const productTitle = selectedOption.dataset.title;
                    const isFeatured = selectedOption.textContent.includes('(Featured)');
                    const section = isFeatured ? 'featuredProducts' : 'products';
                    const productIndex = selectedOption.value;
                    
                    // Show loading indicator
                    const btn = this;
                    btn.innerHTML = 'Deleting...';
                    btn.disabled = true;
                    
                    // Send AJAX request to delete the product
                    const formData = new FormData();
                    formData.append('title', productTitle);
                    formData.append('section', section);
                    formData.append('product_index', productIndex);
                    
                    fetch('ajax_delete_product.php', {
                      method: 'POST',
                      body: formData
                    })
                    .then(response => response.json())                    .then(data => {
                      if (data.status === 'success') {
                        // Show success message
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'alert alert-success';
                        messageDiv.textContent = data.message;
                        
                        // Insert the message before the table
                        const table = document.querySelector('.product-management-table');
                        table.parentNode.insertBefore(messageDiv, table);
                        
                        // Reset the form and reload the product list after a short delay
                        document.getElementById('manage-title').value = '';
                        document.getElementById('manage-price').value = '';
                        document.getElementById('manage-tag').value = '';
                        document.getElementById('manage-quantity').value = '';
                        document.getElementById('manage-desc').value = '';
                        document.getElementById('manage-category').value = '';
                        document.getElementById('manage-category-desc').value = '';
                        
                        // Remove the option from the select
                        selectedOption.remove();
                        
                        // Update the main product table without a page reload
                        refreshProductTable(productIndex);
                            
                        // Reset the button
                        btn.innerHTML = 'Delete Product';
                        btn.disabled = false;                        // Find and remove the corresponding row in the main table using our refreshProductTable function
                        console.log(`Deleting product from main table, index: ${productIndex}`);
                        refreshProductTable(productIndex);
                        
                        // Auto-hide message after 3 seconds
                        setTimeout(() => {
                          messageDiv.remove();
                        }, 3000);
                      } else {
                        alert('Error: ' + data.message);
                        btn.innerHTML = 'Delete Product';
                        btn.disabled = false;
                      }
                    })
                    .catch(error => {
                      console.error('Error:', error);
                      alert('An error occurred while deleting the product.');
                      btn.innerHTML = 'Delete Product';
                      btn.disabled = false;
                    });
                  }
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
          <tr>            <form id="category-creation-form" class="category-creation-form">
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
                  <button type="submit" class="btn-action" id="create-category-btn">Create Category</button>
                  <span id="category-loading" style="display:none;">
                    <div class="spinner" style="display:inline-block; width:20px; height:20px; margin-left:10px;"></div>
                  </span>
                </div>
              </td>
            </form>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</div>
<script>        // Function to refresh the product table
        function refreshProductTable(productIndex = null) {
          if (productIndex !== null) {
            // If a specific product index is provided, just remove that row
            const tableRows = document.querySelectorAll('.order-table tbody tr');
            let foundRow = null;
            
            // Try multiple methods to find the correct row
            tableRows.forEach(row => {
              // Method 1: Check input with name="product_index"
              const indexInputs = row.querySelectorAll('input[name="product_index"]');
              indexInputs.forEach(input => {
                if (input.value == productIndex) {
                  foundRow = row;
                }
              });
              
              // Method 2: Check delete button with data-index attribute
              const deleteBtn = row.querySelector('button.delete-link[data-index]');
              if (deleteBtn && deleteBtn.dataset.index == productIndex) {
                foundRow = row;
              }
              
              // Method 3: Check description with data-product-index
              const description = row.querySelector('.editable-description[data-product-index]');
              if (description && description.dataset.productIndex == productIndex) {
                foundRow = row;
              }
            });
            
            if (foundRow) {
              // Remove with animation
              foundRow.style.backgroundColor = '#ffebee';
              setTimeout(() => {
                foundRow.style.opacity = '0';
                foundRow.style.transition = 'opacity 0.5s';
                setTimeout(() => {
                  foundRow.remove();
                  
                  // Update indexes of remaining rows
                  const remainingRows = document.querySelectorAll('.order-table tbody tr');
                  remainingRows.forEach((r, index) => {
                    r.querySelector('td:first-child').textContent = index + 1;
                  });
                  
                  // Show success message
                  const messageDiv = document.createElement('div');
                  messageDiv.className = 'alert alert-success';
                  messageDiv.textContent = 'Product deleted successfully';
                  const table = document.querySelector('.order-table');
                  table.parentNode.insertBefore(messageDiv, table);
                  
                  // Auto-hide message after 3 seconds
                  setTimeout(() => {
                    messageDiv.style.opacity = '0';
                    messageDiv.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                      messageDiv.remove();
                    }, 500);
                  }, 3000);
                }, 500);
              }, 300);
            } else {
              console.error(`Row with product index ${productIndex} not found in table`);
            }
            return;
          }
          
          // For a full refresh, we would use AJAX to fetch the latest data
          // This would be implemented if needed in the future
          console.log('Full table refresh would be implemented here if needed');
        }
        
        // Handle product deletion from main table
        document.querySelectorAll('.delete-link').forEach(deleteBtn => {
          deleteBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this product?')) {
              const btn = this;
              const productIndex = btn.dataset.index;
              const productTitle = btn.dataset.title;
              const section = btn.dataset.section;
              
              // Update button state
              btn.textContent = 'Deleting...';
              btn.disabled = true;
              
              // Send AJAX request
              const formData = new FormData();
              formData.append('product_index', productIndex);
              formData.append('title', productTitle);
              formData.append('section', section);
              
              fetch('ajax_delete_product.php', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(data => {
                if (data.status === 'success') {
                  // Remove the row from the table
                  const row = btn.closest('tr');
                  row.style.backgroundColor = '#e8f5e9';
                  
                  // Show a success message
                  const messageDiv = document.createElement('div');
                  messageDiv.className = 'alert alert-success';
                  messageDiv.textContent = data.message;
                  const table = document.querySelector('.order-table');
                  table.parentNode.insertBefore(messageDiv, table);
                  
                  // Remove row with animation
                  setTimeout(() => {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                      row.remove();
                      
                      // Update indexes of remaining rows
                      const rows = document.querySelectorAll('.order-table tbody tr');
                      rows.forEach((r, index) => {
                        r.querySelector('td:first-child').textContent = index + 1;
                      });
                        // Also remove from the dropdown in the manage form
                      const option = document.querySelector(`#manage-select option[value="${productIndex}"]`);
                      if (option) {
                        option.remove();
                        console.log(`Removed option with value ${productIndex} from management dropdown`);
                      } else {
                        console.log(`Option with value ${productIndex} not found in management dropdown`);
                      }
                    }, 500);
                  }, 300);
                  
                  // Auto-hide message after 3 seconds
                  setTimeout(() => {
                    messageDiv.style.opacity = '0';
                    messageDiv.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                      messageDiv.remove();
                    }, 500);
                  }, 3000);
                } else {
                  alert('Error: ' + data.message);
                  btn.textContent = 'Delete';
                  btn.disabled = false;
                }
              })
              .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the product.');
                btn.textContent = 'Delete';
                btn.disabled = false;
              });
            }
          });
        });
          // Handle category updates with AJAX
        document.querySelectorAll('.update-category-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            const container = this.closest('.category-selector');
            const select = container.querySelector('select.category-select');
            const productIndex = select.dataset.productIndex;
            const selectedOption = select.options[select.selectedIndex];
            
            if (!selectedOption.value) {
              alert('Please select a category');
              return;
            }
            
            const categoryName = selectedOption.value;
            const categoryDescription = selectedOption.dataset.desc || '';
            const statusSpan = container.querySelector('.category-status');
            
            // Update button state
            const originalText = this.textContent;
            this.textContent = 'Updating...';
            this.disabled = true;
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('product_index', productIndex);
            formData.append('category_name', categoryName);
            formData.append('category_description', categoryDescription);
            
            fetch('ajax_assign_category.php', {
              method: 'POST',
              body: formData
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                // Show success status
                statusSpan.textContent = '✓';
                statusSpan.style.color = 'green';
                
                setTimeout(() => {
                  statusSpan.textContent = '';
                }, 3000);
              } else {
                alert('Error: ' + data.message);
                statusSpan.textContent = '✗';
                statusSpan.style.color = 'red';
                
                setTimeout(() => {
                  statusSpan.textContent = '';
                }, 3000);
              }
              
              // Reset button
              this.textContent = originalText;
              this.disabled = false;
            })
            .catch(error => {
              console.error('Error:', error);
              alert('An error occurred while updating the category.');
              this.textContent = originalText;
              this.disabled = false;
              
              statusSpan.textContent = '✗';
              statusSpan.style.color = 'red';
              
              setTimeout(() => {
                statusSpan.textContent = '';
              }, 3000);
            });
          });
        });
        
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
        });        // Handle category form submission with AJAX
        document.getElementById('category-creation-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const categoryName = document.getElementById('category_name').value.trim();
            const categoryDescription = document.getElementById('category_description').value.trim();
            
            if (!categoryName || !categoryDescription) {
                alert('Please fill in all category fields');
                return;
            }
            
            // Show loading spinner
            const submitBtn = document.getElementById('create-category-btn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            document.getElementById('category-loading').style.display = 'inline-block';
            
            // Create form data
            const formData = new FormData();
            formData.append('category_name', categoryName);
            formData.append('category_description', categoryDescription);
            
            // Send AJAX request
            fetch('ajax_create_category.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading spinner
                submitBtn.disabled = false;
                document.getElementById('category-loading').style.display = 'none';
                
                if (data.status === 'success') {
                    // Show success message
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'alert alert-success';
                    messageDiv.textContent = data.message;
                    
                    const categoryTable = document.querySelector('.category-table');
                    categoryTable.parentNode.insertBefore(messageDiv, categoryTable);
                    
                    // Add the new category to all category dropdowns
                    const dropdowns = document.querySelectorAll('select[name="cat_name"], #manage-category, #category');
                    
                    dropdowns.forEach(dropdown => {
                        if (dropdown) {
                            const option = document.createElement('option');
                            option.value = data.category.name;
                            option.textContent = data.category.name;
                            if (dropdown.dataset) {
                                option.dataset.desc = data.category.description;
                            }
                            dropdown.appendChild(option);
                        }
                    });
                    
                    // Clear the form
                    document.getElementById('category_name').value = '';
                    document.getElementById('category_description').value = '';
                    
                    // Auto-hide message after 3 seconds
                    setTimeout(() => {
                        messageDiv.style.opacity = '0';
                        messageDiv.style.transition = 'opacity 0.5s';
                        setTimeout(() => {
                            messageDiv.remove();
                        }, 500);
                    }, 3000);
                } else {
                    alert('Error: ' + data.message);
                }
                
                // Reset button
                submitBtn.textContent = originalText;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the category.');
                submitBtn.disabled = false;
                document.getElementById('category-loading').style.display = 'none';
            });
        });
      </script>
</body>
</html>
