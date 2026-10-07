jQuery(document).ready(function($) {

    var is_processing = false;
    var total_products_processed = 0;
    var total_pages = 0;

    // Main recursive function to handle the AJAX chain
    function processBatch(page) {
        if (!is_processing) {
            updateStatus('Canceled by user.', 'red');
            return;
        }

        // Update UI status
        updateStatus('Processing Page ' + page + ' of ' + total_pages + '...', '#f39c12');

        $.ajax({
            url: CjBatch.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: CjBatch.action_name,
                security: CjBatch.nonce,
                page: page
            },
            
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    total_pages = data.total_pages; // Update total pages on first run
                    total_products_processed += data.processed_count;

                    // Calculate and update progress bar
                    var progress_percent = (data.current_page / data.total_pages) * 100;
                    updateProgressBar(progress_percent, data.current_page, data.total_pages);
                    
                    if (data.finished) {
                        // Finished
                        is_processing = false;
                        updateStatus('✅ **Processing Complete!** Total products processed: ' + total_products_processed, 'green');
                        resetButtons();
                        return;
                    } else {
                        // Continue to next page immediately
                        processBatch(data.next_page);
                    }
                } else {
                    // Server-side error
                    is_processing = false;
                    updateStatus('❌ Server Error: ' + response.data, 'red');
                    resetButtons();
                }
            },
            
            error: function() {
                // Network error
                is_processing = false;
                updateStatus('⚠️ Network Error occurred. Try again.', 'red');
                resetButtons();
            }
        });
    }

    // --- Utility Functions ---

    function updateStatus(message, color) {
        var statusArea = $('#batch-status-area p');
        statusArea.html('Status: ' + message);
        statusArea.css('color', color);
    }
    
    function updateProgressBar(percent, currentPage, totalPages) {
        var innerBar = $('#batch-progress-inner');
        innerBar.css('width', percent + '%');
        innerBar.text('Page ' + currentPage + '/' + totalPages);
    }
    
    function setButtonsProcessing() {
        $('#start-batch-process-btn').hide();
        $('#cancel-batch-process-btn').show();
    }
    
    function resetButtons() {
        $('#start-batch-process-btn').show();
        $('#cancel-batch-process-btn').hide();
    }


    // --- Event Listeners ---

    $('#start-batch-process-btn').on('click', function() {
        if (is_processing) return;

        is_processing = true;
        total_products_processed = 0; // Reset counter
        total_pages = 1; // Start by assuming 1 page until the first request defines the total
        setButtonsProcessing();
        updateStatus('Starting batch process...', '#0073aa');
        updateProgressBar(0, 0, 0); // Reset progress visually
        
        processBatch(1); // Start the process on page 1
    });

    $('#cancel-batch-process-btn').on('click', function() {
        is_processing = false; // This will stop the recursive call after the current AJAX completes
        updateStatus('Canceling process. Finishing current batch...', 'orange');
    });
});