<script type="module">

    import { initializeApp } from "https://www.gstatic.com/firebasejs/12.8.0/firebase-app.js";
    import { getDatabase, ref, onChildAdded, push } from "https://www.gstatic.com/firebasejs/12.8.0/firebase-database.js";

    const firebaseConfig = {
        apiKey: "AIzaSyBfoxJJE3-R2LOsPP0crSoiBP24JJyp1Sw",
        authDomain: "anything-supplies-project.firebaseapp.com",
        projectId: "anything-supplies-project",
        databaseURL: "https://anything-supplies-project-default-rtdb.asia-southeast1.firebasedatabase.app",
        storageBucket: "anything-supplies-project.firebasestorage.app",
        messagingSenderId: "466892390730",
        appId: "1:466892390730:web:41d7f029be71dbe6dc41b3",
        measurementId: "G-0BREJ5JNTD"
    };

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);

    jQuery(function($){
        const ajax_url = '<?php echo admin_url('admin-ajax.php') ?>';    
        const chatSpinner = $('.chat-spinner');
        const sendIcon = $('.send-icon');
        const inquiry_id = $('.send-button').data('inquiry-id');

        listenMessage(inquiry_id);
        
        function sendMessage(inquiryId, sender_id, message){

            const messageRef = ref(db, 'inquiry_chats/' + inquiryId);
            console.log(messageRef);
            push(messageRef, {

                inquiry_id: inquiryId,
                user_id: sender_id,
                message: message,
                timestamp: Date.now()
            });

        }

        function listenMessage(inquiryId){

            const messageRef = ref(db, 'inquiry_chats/' + inquiryId);
            const currentUserId = $('.send-button').data('user-id');
            onChildAdded(messageRef, (snapshot) => {

                const data = snapshot.val();
                const messageClass = data.user_id == currentUserId ? "mine" : "yours";

                $('.chat-container').append(`
                    <div class="message ${messageClass}">
                        <div class="bubble">${data.message}</div>
                    </div>
                `);

                if($('.chat-wrapper').length > 0){

                    $('.chat-container').scrollTop($('.chat-container')[0].scrollHeight);

                    chatSpinner.hide();
                    sendIcon.show();
                    $('.chat-input').val('');

                }

            });

        }

        $('.send-button').on('click', function(e){

            e.preventDefault();

            const sender_id = $(this).data('user-id');
            const message = $('.chat-input').val().trim();
        
            if(!message) return;

            chatSpinner.show();
            sendIcon.hide();

            let form_data = new FormData();
            form_data.append('inquiry_id', inquiry_id);
            form_data.append('sender_id', sender_id);
            form_data.append('chat_input', message);
            form_data.append('action', 'inquiry_send_chat');

            sendMessage(inquiry_id, sender_id, message);

        });

        $('.btn-update-status').on('click', function(e){

            const inquiry_id = $(this).data('inquiry-id');
            const status = $('#inquiry-status').val();

            let form_data = new FormData();
            form_data.append('inquiry_id', inquiry_id);
            form_data.append('inquiry_status', status);
            form_data.append('action', 'sls_inquiry_update_status');

            $.ajax({

                type: 'POST',
                url: ajax_url,
                data: form_data,
                contentType: false,
                processData: false,
                success: function(response){

                    const results = JSON.parse(response);
                    let alert_class = results.status == true ? 'alert-success' : 'alert-danger';

                    $('.update-alert').addClass(alert_class);
                    $('.update-alert').empty().html(results.message);

                    setTimeout(() => {
                        
                        $('.update-alert').removeClass(alert_class);
                        $('.update-alert').empty();

                    }, 5000);
                    
                    if(status == 'closed'){

                        $('.chat-input').attr('disabled', 'true');
                        $('.send-button').attr('disabled', 'true');
                        $('.inquiry-status-container').empty().html(`
                            <p>Inquiry Status: <strong> Closed </strong></p>
                        `)

                    }

                    $('#inquiry-status').data('current-status', status);
                    $('.btn-update-status').attr('disabled', 'true');
                    

                }

            });

        })

        $('#inquiry-status').on('change', function(e){

            const current_status = $(this).data('current-status');
            let element_disabled = $('.btn-update-status').attr('disabled');

            console.log(element_disabled);

            if(typeof element_disabled !== 'undefined'){

                if(current_status != $(this).val()){

                    $('.btn-update-status').removeAttr('disabled');

                }else{

                    $('.btn-update-status').attr('disabled', 'true');

                }

            }else{
                console.log(current_status);
                console.log('exists');
                if(current_status == $(this).val()){

                    $('.btn-update-status').attr('disabled', 'true');

                }else{

                    $('.btn-update-status').removeAttr('disabled');

                }

            }

        });

    });
</script>