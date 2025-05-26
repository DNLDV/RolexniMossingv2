/**
 * AJAX Pagination for PFRolex
 * This file handles loading products dynamically using AJAX
 */

// Define these functions in the global scope
let productContainer, paginationContainer, loadingIndicator;

/**
 * Update the pagination links after loading new products
 * @param {number} currentPage - The current page number
 * @param {number} totalPages - The total number of pages
 */
function updatePaginationLinks(currentPage, totalPages) {
  if (!paginationContainer) return;
  
  let html = '';
  
  // Previous button
  if (currentPage > 1) {
    html += `<a href="javascript:void(0)" data-page="${currentPage - 1}" class="pagination__prev">Previous</a>`;
  }
  
  // Page links
  for (let page = 1; page <= totalPages; page++) {
    html += `<a href="javascript:void(0)" data-page="${page}" class="pagination__link ${page == currentPage ? 'active' : ''}">${page}</a>`;
  }
  
  // Next button
  if (currentPage < totalPages) {
    html += `<a href="javascript:void(0)" data-page="${currentPage + 1}" class="pagination__next">Next</a>`;
  }
  
  paginationContainer.innerHTML = html;
  
  // Re-attach event listeners to new pagination links
  attachPaginationListeners();
}

/**
 * Attach event listeners to pagination links
 */
function attachPaginationListeners() {
  const paginationLinks = document.querySelectorAll('#product-pagination a');
  
  paginationLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const page = parseInt(this.getAttribute('data-page'));
      if (page) {
        loadProducts(page);
      }
    });
  });
}

/**
 * Initialize add-to-cart buttons after content is loaded via AJAX
 * This function rebinds event handlers to the newly loaded product buttons
 */
function initProductButtons() {
  document.querySelectorAll("#product-container .add-to-cart").forEach((btn) => {
    // Remove any existing event listeners
    btn.replaceWith(btn.cloneNode(true));
    
    // Get the fresh element after replacement
    const freshBtn = document.querySelector(`#product-container .add-to-cart[data-title="${btn.dataset.title}"]`);
    
    freshBtn.addEventListener("click", () => {
      if (!window.isLoggedIn) {
        if (typeof showLoginModal === 'function') {
          showLoginModal();
        } else {
          alert('Please log in to add items to your cart');
        }
        return;
      }
      
      const title = freshBtn.dataset.title;
      const price = parseFloat(freshBtn.dataset.price);
      const image = freshBtn.dataset.image;
      
      // Access the cart from window global scope
      if (typeof window.cart !== 'undefined') {
        const item = window.cart.find(i => i.title === title);
        if (item) {
          item.quantity++;
          if (typeof showMessage === 'function') {
            showMessage(`${title} quantity increased in cart`, '#4caf50');
          }
        } else {
          window.cart.push({ title, price, image, quantity: 1 });
          if (typeof showMessage === 'function') {
            showMessage(`${title} added to cart`, '#4caf50');
          }
        }
        
        // Save to localStorage
        localStorage.setItem('pfrolex_cart', JSON.stringify(window.cart));

        if (typeof updateCartDisplay === 'function') {
          updateCartDisplay();
        }
        
        if (typeof openCart === 'function') {
          openCart();
        }
      } else {
        console.error('Cart is not defined in the global scope');
      }
    });
  });
}

/**
 * Main load products function - fetches and displays products via AJAX
 * @param {number} page - The page number to load
 */
function loadProducts(page) {
  // Show loading indicator
  if (loadingIndicator) loadingIndicator.style.display = 'flex';
  
  // Fetch products
  fetch(`get_products.php?page=${page}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Update product container with new HTML
      if (productContainer) {
        productContainer.innerHTML = data.html;
      }
      
      // Update URL without triggering a scroll to the #products section
      const url = new URL(window.location);
      url.searchParams.set('page', page);
      history.pushState({ page: page }, '', url);
      
      // Update active pagination link
      updatePaginationLinks(page, data.totalPages);
      
      // Reinitialize add-to-cart buttons
      initProductButtons();
      
      // Hide loading indicator
      if (loadingIndicator) loadingIndicator.style.display = 'none';
    })
    .catch(error => {
      console.error('Error loading products:', error);
      if (loadingIndicator) loadingIndicator.style.display = 'none';
      
      // Show error message
      const errorMsg = document.createElement('div');
      errorMsg.className = 'error-message';
      errorMsg.textContent = 'Failed to load products. Please try again.';
      errorMsg.style.color = '#f44336';
      errorMsg.style.textAlign = 'center';
      errorMsg.style.margin = '2rem 0';
      
      if (productContainer) {
        productContainer.innerHTML = '';
        productContainer.appendChild(errorMsg);
      }
    });
}

// Handle back/forward buttons in browser
window.addEventListener('popstate', function(event) {
  if (event.state && event.state.page) {
    loadProducts(event.state.page);
  } else {
    // If no state (first load), get page from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = parseInt(urlParams.get('page')) || 1;
    loadProducts(currentPage);
  }
});

// Initialize everything when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Initialize global elements
  productContainer = document.getElementById('product-container');
  paginationContainer = document.getElementById('product-pagination');
  loadingIndicator = document.getElementById('products-loading');
  
  // If needed elements don't exist, exit
  if (!productContainer || !paginationContainer) return;
  
  // Make loadProducts function available globally
  window.loadProducts = loadProducts;
  
  // Make cart globally available from main.js if it exists
  if (typeof cart !== 'undefined') {
    window.cart = cart;
  }
  
  // Initialize: attach event listeners
  attachPaginationListeners();
});

// Make loadProducts function available globally
window.loadProducts = function(page) {
  const productContainer = document.getElementById('product-container');
  const loadingIndicator = document.getElementById('products-loading');
  
  // Show loading indicator
  if (loadingIndicator) loadingIndicator.style.display = 'flex';
  
  // Fetch products
  fetch(`get_products.php?page=${page}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Update product container with new HTML
      productContainer.innerHTML = data.html;
      
      // Update URL without triggering a scroll to the #products section
      const url = new URL(window.location);
      url.searchParams.set('page', page);
      history.pushState({}, '', url);
      
      // Update active pagination link
      updatePaginationLinks(page, data.totalPages);
      
      // Reinitialize add-to-cart buttons
      initProductButtons();
      
      // Hide loading indicator
      if (loadingIndicator) loadingIndicator.style.display = 'none';
    })
    .catch(error => {
      console.error('Error loading products:', error);
      alert('Failed to load products. Please try again.');
      if (loadingIndicator) loadingIndicator.style.display = 'none';
    });
};

// Handle back/forward buttons in browser
window.addEventListener('popstate', function() {
  // Get page from URL
  const urlParams = new URLSearchParams(window.location.search);
  const currentPage = parseInt(urlParams.get('page')) || 1;
  
  // Load that page
  const productContainer = document.getElementById('product-container');
  const paginationContainer = document.getElementById('product-pagination');
  if (productContainer && paginationContainer) {
    window.loadProducts(currentPage);
  }
});
