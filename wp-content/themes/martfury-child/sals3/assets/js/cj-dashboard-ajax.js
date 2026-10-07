

jQuery(document).ready(function($) {

    // 1. Target the button
    $('#fetch-random-product-btn').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var resultArea = $('#product-result-area');
        
        // Disable button and show loading state
        button.prop('disabled', true).text('Fetching...');
        resultArea.html('<p>Loading product data...</p>');

        // 2. Perform the AJAX request
        $.ajax({
            url: CjAjax.ajax_url, // URL passed from wp_localize_script
            type: 'POST',
            data: {
                action: 'fetch_jc_product',  // Must match the wp_ajax_ hook
                security: CjAjax.nonce           // Security nonce
            },
            dataType: 'json',
            
            // 3. Handle success response
            success: function(response) {
                if (response.success) {
                    var product = response.data;
                    
                    var html = '<h3></h3>';
                 
                    console.log(product);
                    
                    resultArea.html(html);
                } else {
                    // Handle errors from wp_send_json_error()
                    resultArea.html('<p style="color: red;">Error: ' + response.data + '</p>');
                }
            },
            
            // 4. Handle connection or network errors
            error: function(xhr, status, error) {
                resultArea.html('<p style="color: red;">An unknown network error occurred.</p>');
            },
            
            // 5. Clean up
            complete: function() {
                button.prop('disabled', false).text('Fetch Product');
            }
        });
    });

    $('#start-batch-btn').on('click', function(e){
        e.preventDefault();
        $.post(CjAjax.ajax_url, {
            action: 'cj_start_batch',
            security: CjAjax.nonce
        }, function(response){
            alert(response.success ? response.data.message : response.data.message);
        }, 'json');
    });

    $('#stop-batch-btn').on('click', function(e){
        e.preventDefault();
        $.post(CjAjax.ajax_url, {
            action: 'cj_stop_batch',
            security: CjAjax.nonce
        }, function(response){
            alert(response.success ? response.data.message : response.data.message);
        }, 'json');
    });

});