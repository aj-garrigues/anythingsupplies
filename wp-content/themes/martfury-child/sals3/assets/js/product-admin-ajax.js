console.log('AJAX starting');
jQuery(document).ready(function($) {

    // 1. Listen for clicks on the sync button
    $('.toggle-synccj-btn').on('click', function(e) {
        e.preventDefault();
        console.log('clicked'); 
        
        var button = $(this);
        var product_id = button.data('product-id');
        var icon_element = button.find('span'); // Get the icon span inside button
        
        // Get the current status from data attribute or default to 'no'
        var current_status = button.data('status') || 'no';
        
        // 2. Add temporary visual feedback (spinning icon)
        var original_icon_html = icon_element.html();
        icon_element.replaceWith('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span>');
        
        // Add CSS for spinning animation if not already present
        if (!$('#sync-spinner-css').length) {
            $('head').append('<style id="sync-spinner-css">@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>');
        }
        
        // Disable the button to prevent multiple clicks
        button.prop('disabled', true);
        
        // 3. Perform the AJAX request
        $.ajax({
            url: ProductAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cjds_sync_product_ajax', // New action for CJDS sync
                security: ProductAjax.nonce,
                product_id: product_id,
                current_status: current_status
            },
            
            // 4. Handle success response
            success: function(response) {
                console.log('AJAX Response:', response);
                
                if (response.success) {
                    var data = response.data;
                    
                    // Update the icon based on status
                    var newIcon;
                    if (data.status === 'success') {
                        newIcon = '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>';
                        
                        // Update the last processed date if provided
                        if (data.last_processed) {
                            button.closest('td').find('div:contains("Last Update:")').html('Last Update: ' + data.last_processed);
                        }
                        
                        // Update status text
                        button.closest('td').find('div:contains("Status:")').html('Status: success');
                        
                        // Show success message
                        showNotification('Product synced successfully!', 'success');
                    } else {
                        newIcon = '<span class="dashicons dashicons-dismiss" style="color:#dc3232; font-weight:600;"></span>';
                        
                        // Update status text
                        button.closest('td').find('div:contains("Status:")').html('Status: ' + data.status);
                        
                        // Show error message
                        showNotification(data.message || 'Sync failed', 'error');
                    }
                    
                    // Replace the spinner with the appropriate icon
                    button.find('.dashicons-update').replaceWith(newIcon);
                    
                    // Update button data
                    button.data('status', data.status === 'success' ? 'yes' : 'no');
                    
                } else {
                    // Server returned error
                    button.find('.dashicons-update').replaceWith(
                        '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
                    );
                    showNotification(response.data || 'An error occurred', 'error');
                }
            },
            
            // 5. Handle error response
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                button.find('.dashicons-update').replaceWith(
                    '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>'
                );
                showNotification('Network error occurred', 'error');
            },
            
            // 6. Always re-enable button after completion
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
    
    // Bulk sync functionality with proper sequential processing
    $('.dobulkaction').on('click', function(e) {
        e.preventDefault();
        
        const CHECKBOX_SELECTOR = 'input[name="post[]"]';
        var selectedProducts = [];
        
        // Collect all selected product IDs
        $(CHECKBOX_SELECTOR + ':checked').each(function() {
            selectedProducts.push($(this).val());
        });
        
        if (selectedProducts.length === 0) {
            showNotification('Please select at least one product', 'warning');
            return;
        }
        
        // Confirm bulk action
        if (!confirm('Sync ' + selectedProducts.length + ' selected products?')) {
            return;
        }
        
        // Process products sequentially
        var currentIndex = 0;
        var successCount = 0;
        var failCount = 0;
        
        function processNextProduct() {
            if (currentIndex >= selectedProducts.length) {
                // All done
                showNotification('Bulk sync complete! Success: ' + successCount + ', Failed: ' + failCount, 'success');
                return;
            }
            
            var productId = selectedProducts[currentIndex];
            const $targetButton = $('a.toggle-synccj-btn[data-product-id="' + productId + '"]');
            
            if ($targetButton.length) {
                // Show progress
                showNotification('Processing ' + (currentIndex + 1) + ' of ' + selectedProducts.length + '...', 'info');
                
                // Get current icon element
                var icon_element = $targetButton.find('span');
                var original_icon_html = icon_element.html();
                
                // Show spinner
                icon_element.replaceWith('<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span>');
                $targetButton.prop('disabled', true);
                
                // Make AJAX call
                $.ajax({
                    url: ProductAjax.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'cjds_sync_product_ajax',
                        security: ProductAjax.nonce,
                        product_id: productId,
                        current_status: $targetButton.data('status') || 'no',
                        force_sync: true // Add flag to force sync even if recently updated
                    },
                    success: function(response) {
                        if (response.success && response.data.status === 'success') {
                            successCount++;
                            var newIcon = '<span class="dashicons dashicons-saved" style="color:#7ad03a; font-weight:600;"></span>';
                            $targetButton.find('.dashicons-update').replaceWith(newIcon);
                            $targetButton.data('status', 'yes');
                            
                            // Update display info
                            if (response.data.last_processed) {
                                $targetButton.closest('td').find('div:contains("Last Update:")').html('Last Update:<br>' + response.data.last_processed);
                            }
                            $targetButton.closest('td').find('div:contains("Status:")').html('Status: <span style="color:#7ad03a; font-weight:600;">success</span>');
                        } else {
                            failCount++;
                            var errorIcon = '<span class="dashicons dashicons-dismiss" style="color:#dc3232; font-weight:600;"></span>';
                            $targetButton.find('.dashicons-update').replaceWith(errorIcon);
                            $targetButton.data('status', 'no');
                        }
                    },
                    error: function() {
                        failCount++;
                        var errorIcon = '<span class="dashicons dashicons-dismiss" style="color:#dc3232;"></span>';
                        $targetButton.find('.dashicons-update').replaceWith(errorIcon);
                    },
                    complete: function() {
                        $targetButton.prop('disabled', false);
                        currentIndex++;
                        
                        // Wait 2 seconds before processing next product to avoid server overload
                        setTimeout(processNextProduct, 2000);
                    }
                });
            } else {
                // Button not found, skip to next
                currentIndex++;
                processNextProduct();
            }
        }
        
        // Start processing
        processNextProduct();
    });
    
    // Helper function to show notifications
    function showNotification(message, type) {
        // Remove any existing notifications
        $('.cjds-notification').remove();
        
        var bgColor = '#7ad03a'; // success
        if (type === 'error') bgColor = '#dc3232';
        if (type === 'warning') bgColor = '#ffb900';
        if (type === 'info') bgColor = '#00a0d2';
        
        var notification = $('<div class="cjds-notification" style="position:fixed; top:32px; right:20px; background:' + bgColor + '; color:#fff; padding:15px 20px; border-radius:3px; box-shadow:0 2px 5px rgba(0,0,0,0.2); z-index:9999; max-width:350px;">' + message + '</div>');
        
        $('body').append(notification);
        
        // Auto-remove after 4 seconds
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 4000);
    }
});