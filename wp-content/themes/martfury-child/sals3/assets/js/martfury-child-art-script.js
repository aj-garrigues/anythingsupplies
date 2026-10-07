jQuery(function($){

    const popup = $('#popup');
    const closeBtn = $('#closeBtn');
    const displayId = $('#display-id');

    $(document).on('click', '.trigger-btn', function(e){

        const inquiry_id = $(this).data('inquiry-id');

        displayId.text(inquiry_id);

        popup.addClass('show');

        let form_data = new FormData();
        form_data.append('inquiry_id', inquiry_id);
        form_data.append('action', 'view_inquiry_action');

        $.ajax({

            type: 'POST',
            url: ajax_obj.ajax_url,
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response){

                $('#inquiry-details').html(response);

            }

        })
    });

    closeBtn.on('click', function(e){

        popup.removeClass('show');
        $('#inquiry-details').empty();
    });

    // Agent column click — opens centered conversation popup
    const conversationPopup = $('#conversation-popup');

    $(document).on('click', '.agent-trigger', function(e){

        const inquiry_id = $(this).data('inquiry-id');

        conversationPopup.addClass('show');
        $('#conversation-details').html('<p style="text-align:center;padding:20px;">Loading conversation...</p>');

        let form_data = new FormData();
        form_data.append('inquiry_id', inquiry_id);
        form_data.append('action', 'view_inquiry_action');

        $.ajax({

            type: 'POST',
            url: ajax_obj.ajax_url,
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response){

                $('#conversation-details').html(response);

            }

        });
    });

    // Close conversation popup — button or overlay click
    $(document).on('click', '#closeConversationBtn, .conversation-popup-overlay', function(){

        conversationPopup.removeClass('show');
        $('#conversation-details').empty();
    });

})