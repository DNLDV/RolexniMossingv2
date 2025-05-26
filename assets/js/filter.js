/**
 * AJAX Category Filtering for PFRolex
 * This file handles loading filtered products dynamically using AJAX
 */

// Global variables
let productContainer, filterResultsContainer, loadingIndicator;
let lastScrollPosition = 0;
let currentCategory = '';
let currentViewMode = 'gallery';
let currentPage = 1;

/**
 * Save the current scroll position
 */
function saveScrollPosition() {
  lastScrollPosition = window.scrollY;
}

/**
 * Restore the scroll position
 */
function restoreScrollPosition() {
  if (lastScrollPosition > 0) {
    window.scrollTo({
      top: lastScrollPosition,
      behavior: 'instant'
    });
  }
}

/**
 * Update the active state of category tags
 * @param {string} selectedCategory - The selected category
 */
function updateCategoryTags(selectedCategory) {
  document.querySelectorAll('.category-tag').forEach(tag => {
    if (tag.dataset.category === selectedCategory) {
      tag.classList.add('active');
    } else {
      tag.classList.remove('active');
    }
  });
  
  // Show or hide the clear filter button
  const clearFilterBtn = document.querySelector('.clear-filter');
  if (clearFilterBtn) {
    if (selectedCategory) {
      clearFilterBtn.style.display = 'flex';
    } else {
      clearFilterBtn.style.display = 'none';
    }
  }
    // Handle filter results container visibility - look for all possible containers
  const filterResultsContainer = document.querySelector('.filter-results');
  const filterResultsById = document.getElementById('filter-results-container');
  
  // If no category is selected, hide all filter results containers
  if (!selectedCategory) {
    // Hide container from index.php
    if (filterResultsById) {
      filterResultsById.style.display = 'none';
    }
    
    // Hide any other dynamically added container
    if (filterResultsContainer && filterResultsContainer !== filterResultsById) {
      filterResultsContainer.style.display = 'none';
    }
  } else {
    // Show appropriate containers if they exist
    if (filterResultsContainer) {
      filterResultsContainer.style.display = 'block';
    }
    if (filterResultsById) {
      filterResultsById.style.display = 'block';
    }
  }
}

/**
 * Update the pagination links after loading new products
 * @param {number} currentPage - The current page number
 * @param {number} totalPages - The total number of pages
 */
function updatePaginationLinks(currentPage, totalPages) {
  const paginationContainer = document.getElementById('product-pagination');
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
  
  // Attach event listeners to pagination links
  attachPaginationListeners();
}

/**
 * Attach event listeners to pagination links
 */
function attachPaginationListeners() {
  const paginationLinks = document.querySelectorAll('#product-pagination a');
  
  paginationLinks.forEach(link => {
    // Remove any existing event listeners by cloning
    const newLink = link.cloneNode(true);
    link.parentNode.replaceChild(newLink, link);
    
    newLink.addEventListener('click', function(e) {
      e.preventDefault();
      saveScrollPosition();
      const page = parseInt(this.getAttribute('data-page'));
      if (page) {
        currentPage = page;
        filterProducts(currentCategory, currentViewMode, currentPage);
        
        // Re-initialize product card handlers after a short delay
        setTimeout(function() {
          if (typeof window.initProductCardHandlers === 'function') {
            window.initProductCardHandlers();
          }
        }, 500);
      }
    });
  });
}

/**
 * Initialize add-to-cart buttons after content is loaded via AJAX
 */
function initProductButtons() {
  document.querySelectorAll("#product-container .add-to-cart").forEach((btn) => {
    // Remove any existing event listeners
    btn.replaceWith(btn.cloneNode(true));
    
    // Get the fresh element after replacement
    const freshBtn = document.querySelector(`#product-container .add-to-cart[data-title="${btn.dataset.title}"]`);
    if (!freshBtn) return;
    
    freshBtn.addEventListener("click", () => {
      if (!window.isLoggedIn) {
        if (typeof window.showLoginModal === 'function') {
          window.showLoginModal();
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
          if (typeof window.showMessage === 'function') {
            window.showMessage(`${title} quantity increased in cart`, '#4caf50');
          }
        } else {
          window.cart.push({
            title,
            price,
            image,
            quantity: 1
          });
          if (typeof window.showMessage === 'function') {
            window.showMessage(`${title} added to cart`, '#4caf50');
          }
        }
        
        // Save cart to localStorage
        localStorage.setItem('pfrolex_cart', JSON.stringify(window.cart));
        
        // Update cart display
        if (typeof window.updateCartDisplay === 'function') {
          window.updateCartDisplay();
        }
      }
    });
  });
}

/**
 * Main filter products function - fetches and displays filtered products via AJAX
 * @param {string} category - The category to filter by
 * @param {string} viewMode - The view mode (gallery or list)
 * @param {number} page - The page number to load
 */
function filterProducts(category, viewMode, page) {
  // Update global variables
  currentCategory = category;
  currentViewMode = viewMode;
  currentPage = page;
  
  // Get containers
  productContainer = document.getElementById('product-container');
  filterResultsContainer = document.querySelector('.filter-results');
  const filterResultsById = document.getElementById('filter-results-container');
  loadingIndicator = document.getElementById('products-loading');
  
  // Immediately hide filter results if no category is selected
  if (!category || category === '') {
    if (filterResultsById) {
      filterResultsById.style.display = 'none';
      filterResultsById.innerHTML = '';
    }
    
    if (filterResultsContainer && filterResultsContainer !== filterResultsById) {
      filterResultsContainer.style.display = 'none';
      filterResultsContainer.innerHTML = '';
    }
  }
  
  if (!productContainer) return;
  
  // Show loading indicator
  if (loadingIndicator) loadingIndicator.style.display = 'flex';
  
  // Update URL without triggering page reload
  const url = new URL(window.location);
  if (category) {
    url.searchParams.set('category', category);
  } else {
    url.searchParams.delete('category');
  }
  url.searchParams.set('view', viewMode);
  url.searchParams.set('page', page);
  history.pushState({ category: category, view: viewMode, page: page, scrollPosition: lastScrollPosition }, '', url);
  
  // Update active state of view toggle buttons
  document.querySelectorAll('.view-btn').forEach(btn => {
    if (btn.dataset.view === viewMode) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });
  
  // Update product container class based on view mode
  productContainer.className = `products__container ${viewMode === 'list' ? 'list-view' : 'grid'}`;
  
  // Fetch filtered products
  fetch(`filter_products.php?category=${encodeURIComponent(category)}&view=${viewMode}&page=${page}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Update category tags
      updateCategoryTags(data.selectedCategory);
        // Update filter info
      if (data.selectedCategory) {
        // We have a selected category, show filter results
        if (filterResultsContainer) {
          // Update existing container
          filterResultsContainer.innerHTML = data.filterInfo;
          filterResultsContainer.style.display = 'block';
        } else if (data.filterInfo) {
          // Create new container if it doesn't exist
          const filterInfoDiv = document.createElement('div');
          filterInfoDiv.className = 'filter-results';
          filterInfoDiv.id = 'filter-results-container';
          filterInfoDiv.innerHTML = data.filterInfo;
          
          const categoryFilterDiv = document.querySelector('.category-filter');
          if (categoryFilterDiv) {
            categoryFilterDiv.insertAdjacentElement('afterend', filterInfoDiv);
          }
        }
      } else {
        // No category selected, remove filter results display
        if (filterResultsContainer) {
          filterResultsContainer.innerHTML = '';
          filterResultsContainer.style.display = 'none';
        }
      }
        // Update product container with new HTML
      productContainer.innerHTML = data.html;
      
      // Update pagination
      updatePaginationLinks(data.currentPage, data.totalPages);
      
      // Reinitialize add-to-cart buttons
      initProductButtons();
      
      // Re-initialize product card handlers for modal display
      setTimeout(function() {
        if (typeof window.initProductCardHandlers === 'function') {
          window.initProductCardHandlers();
        }
      }, 300);
      
      // Hide loading indicator
      if (loadingIndicator) loadingIndicator.style.display = 'none';
      
      // Restore scroll position after content is loaded
      setTimeout(restoreScrollPosition, 0);
    })
    .catch(error => {
      console.error('Error loading products:', error);
      if (loadingIndicator) loadingIndicator.style.display = 'none';
      
      // Show error message
      productContainer.innerHTML = `
        <div class="no-results">
          <i class='bx bx-error-circle'></i>
          <p>Failed to load products. Please try again.</p>
        </div>
      `;
    });
}

/**
 * Clear the category filter
 */
function clearCategoryFilter() {
  saveScrollPosition();
  currentCategory = '';
  currentPage = 1;
  
  // Immediately update category tags to show none are selected
  updateCategoryTags('');
  
  // Remove the filter results text - find all containers
  const filterResultsContainer = document.querySelector('.filter-results');
  const filterResultsById = document.getElementById('filter-results-container');
  
  // Hide the container from index.php
  if (filterResultsById) {
    filterResultsById.innerHTML = '';
    filterResultsById.style.display = 'none';
  }
  
  // Hide any dynamically added container
  if (filterResultsContainer && filterResultsContainer !== filterResultsById) {
    filterResultsContainer.innerHTML = '';
    filterResultsContainer.style.display = 'none';
  }
  
  filterProducts('', currentViewMode, 1);
}

// Handle browser back/forward buttons
window.addEventListener('popstate', function(event) {
  if (event.state) {
    if (event.state.scrollPosition) {
      lastScrollPosition = event.state.scrollPosition;
    }
    
    const category = event.state.category || '';
    const viewMode = event.state.view || 'gallery';
    const page = event.state.page || 1;
    
    // Update without pushing state
    currentCategory = category;
    currentViewMode = viewMode;
    currentPage = page;
    
    // Load products with these parameters
    filterProducts(category, viewMode, page);
  } else {
    // If no state (first load), get parameters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category') || '';
    const viewMode = urlParams.get('view') || 'gallery';
    const page = parseInt(urlParams.get('page')) || 1;
    
    filterProducts(category, viewMode, page);
  }
});

// Initialize everything when the DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  // Initialize event listeners for category tags
  document.querySelectorAll('.category-tag').forEach(tag => {
    tag.addEventListener('click', function(event) {
      event.preventDefault();
      saveScrollPosition();
      const category = this.dataset.category;
      if (this.classList.contains('active')) {
        clearCategoryFilter();
        return;
      } else {
        currentCategory = category;
        document.querySelectorAll('.category-tag').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      }
      currentPage = 1;
      filterProducts(currentCategory, currentViewMode, currentPage);
    });
  });

  // Initialize event listeners for view toggle
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      saveScrollPosition();
      currentViewMode = this.dataset.view;
      filterProducts(currentCategory, currentViewMode, currentPage);
    });
  });

  // Make functions global
  window.clearCategoryFilter = clearCategoryFilter;
  window.filterProducts = filterProducts;

  // Initialize from URL parameters
  const urlParams = new URLSearchParams(window.location.search);
  currentCategory = urlParams.get('category') || '';
  currentViewMode = urlParams.get('view') || 'gallery';
  currentPage = parseInt(urlParams.get('page')) || 1;

  productContainer = document.getElementById('product-container');
  loadingIndicator = document.getElementById('products-loading');
  paginationContainer = document.getElementById('product-pagination');

  document.addEventListener('click', function(event) {
    if (!event.target.closest('.category-tag') && !event.target.closest('.filter-results')) {
      const filterResultsContainer = document.querySelector('.filter-results');
      if (filterResultsContainer && currentCategory === '') {
        filterResultsContainer.style.display = 'none';
      }
    }
  });
});
