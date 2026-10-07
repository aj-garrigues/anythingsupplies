jQuery(document).ready(function($) {
    // Attach the event to a button click, for example
    $('#bulk-trigger-button').on('click', function(e) {
        e.preventDefault();
		alert('sdfasd');
		
        // Data to send to the PHP function
        var dataToSend = {
            'action': 'my_ajax_action', // THIS MUST MATCH the PHP action hook suffix!
            'nonce': my_ajax_obj.nonce, // The security nonce from wp_localize_script
            'some_data': 'Hello from the frontend!' // Custom data
        };

        // Perform the AJAX request
        $.ajax({
            url: my_ajax_obj.ajax_url, // The AJAX URL from wp_localize_script
            type: 'POST', // Use POST for data manipulation
            data: dataToSend,
            dataType: 'json', // Expecting a JSON response (due to wp_send_json_...)

            success: function(response) {
                if (response.success) {
                    // Success response from wp_send_json_success()
                    console.log('✅ Success:', response.data.message);
                    console.log('Result:', response.data.result);
                    $('#ajax-response-area').html('<p>' + response.data.message + '</p><p>Details: ' + response.data.result + '</p>');
                } else {
                    // Error response from wp_send_json_error()
                    console.error('❌ Error:', response.data);
                    $('#ajax-response-area').html('<p>Error: ' + response.data + '</p>');
                }
            },
            error: function(xhr, status, error) {
                // Network or server error
                console.error('AJAX Error:', status, error);
                $('#ajax-response-area').html('<p>An AJAX error occurred.</p>');
            }
        });
    });

    console.log('test');
});