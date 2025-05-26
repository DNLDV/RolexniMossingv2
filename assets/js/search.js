document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('product-search');
    const searchResults = document.getElementById('search-results');
    let searchTimeout;

    // Function to escape HTML to prevent XSS
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Function to format price with PHP currency symbol
    function formatPrice(price) {
        return '₱' + parseFloat(price).toLocaleString();
    }

    // Function to render search results
    function renderSearchResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-result-item">No products found</div>';
            return;
        }

        const html = results.map(product => `
            <div class="search-result-item">
                <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.title)}" class="search-result-image">
                <div class="search-result-info">
                    <div class="search-result-title">${escapeHtml(product.title)}</div>
                    <div>
                        <span class="search-result-price">${formatPrice(product.price)}</span>
                        <span class="search-result-tag">${escapeHtml(product.tag)}</span>
                    </div>
                </div>
            </div>
        `).join('');

        searchResults.innerHTML = html;
    }

    // Function to perform search
    function performSearch(query) {
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        fetch(`search_products.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    searchResults.style.display = 'block';
                    renderSearchResults(data.results);
                } else {
                    console.error('Search failed:', data.message);
                }
            })
            .catch(error => {
                console.error('Error performing search:', error);
            });
    }

    // Add event listener for search input
    searchInput.addEventListener('input', function(e) {
        // Clear previous timeout
        clearTimeout(searchTimeout);

        // Set new timeout for search
        searchTimeout = setTimeout(() => {
            performSearch(e.target.value.trim());
        }, 300); // Wait 300ms after user stops typing
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchResults.contains(e.target) && e.target !== searchInput) {
            searchResults.style.display = 'none';
        }
    });

    // Show search results when focusing on search input
    searchInput.addEventListener('focus', function() {
        if (this.value.length >= 2) {
            searchResults.style.display = 'block';
        }
    });
});
