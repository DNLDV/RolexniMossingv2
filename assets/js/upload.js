// This file handles AJAX for product uploading
document.addEventListener('DOMContentLoaded', function() {
    // Get the product upload form
    const uploadForm = document.querySelector('.add-product-form');
    
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            const title = this.querySelector('input[name="title"]').value;
            const price = this.querySelector('input[name="price"]').value;
            const tag = this.querySelector('input[name="tag"]').value;
            const image = this.querySelector('input[name="image"]').files[0];
            
            if (!title || !price || !tag || !image) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Uploading...';
            submitBtn.disabled = true;
            
            // Create form data
            const formData = new FormData(this);
            formData.append('ajax', '1');
            
            // Send AJAX request
            fetch('upload_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                if (data.status === 'success') {
                    // Show success message
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'alert alert-success';
                    messageDiv.textContent = data.message;
                    
                    uploadForm.insertAdjacentElement('beforebegin', messageDiv);
                    
                    // Reset form
                    uploadForm.reset();
                    
                    // Auto-hide message after 3 seconds
                    setTimeout(() => {
                        messageDiv.style.opacity = '0';
                        messageDiv.style.transition = 'opacity 0.5s';
                        setTimeout(() => {
                            messageDiv.remove();
                        }, 500);
                    }, 3000);
                    
                    // Reload the page after a short delay to show the new product
                    setTimeout(() => {
                        location.reload();
                    }, 3500);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the product.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});
