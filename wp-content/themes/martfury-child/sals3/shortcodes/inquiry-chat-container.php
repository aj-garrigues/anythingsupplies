<div class="row">
    <div class="col-md-2">
        <img src="<?php echo $product_image_url; ?>" height="75" width="75" alt="product-img">
    </div>
    <div class="col-md-10"><?php echo $product_title; ?></div>
</div>
<div class="product-description">
    <p> <?php echo $product_short_description; ?> </p>
</div>
<h2>Agent Details:</h2>
<div class="row">
    <div class="col-md-2">
        <?php if($user_image): ?>
            <img src="<?php echo $agent_image; ?>" alt="agent_img" height="75" width="75">
        <?php else: ?>
            <img src="<?php echo $default_avatar_url; ?>" alt="agent_img" height="75" width="75">
        <?php endif;?>
    </div>
    <div class="col-md-10">
        <strong>Agent Name: </strong><?php echo $agent_display_name; ?>
        <br>
        <strong>Agent Email: </strong><?php echo $agent_email; ?>
    </div>
</div>

<div class="mb-5 w-50 mt-5" class="inquiry-status-container">
    <?php if(wp_get_current_user()->roles[0] == 'sls_agent' && $inquiry['status'] != 'closed'): ?>
        <label class="form-label">Inquiry Status: </label>
        <div class="d-flex">
            <div>
                <select name="inquiry-status" id="inquiry-status" class="form-control-custom" data-current-status="<?php echo $inquiry['status']; ?>">
                    <option value="open" <?php echo $inquiry['status'] == 'open' ? "selected": ""?>>open</option>
                    <option value="pending" <?php echo $inquiry['status'] == 'pending' ? "selected": ""?>>pending</option>
                    <option value="closed">closed</option>
                </select>
            </div>
            <button type="button" class="btn-update-status btn-sm" data-inquiry-id="<?php echo $inquiry['id']; ?>" disabled="true">update status</button>
        </div>
        <small class="update-alert"></small>
    <?php else: ?>
        <p>Inquiry Status: <strong><?php echo $inquiry['status']; ?></strong></p>
    <?php endif; ?>
</div>
<hr>
<div class="chat-wrapper">
    <div class="chat-container">

        <div class="message <?php echo $inquiry['agent_id'] != get_current_user_id() ? 'mine' : 'yours'; ?>">
            <div class="bubble"><?php echo $inquiry['message']; ?></div>
        </div>

    </div>
    <div class="input-area">
        <form >
            <input type="text" placeholder="Type a message..." class="chat-input" <?php echo $inquiry['status'] == 'closed' ? 'disabled' : ''; ?>>
            <button type="submit" class="send-button" 
                data-inquiry-id="<?php echo $inquiry['id']; ?>" 
                data-user-id="<?php echo get_current_user_id(); ?>"
                <?php echo $inquiry['status'] == 'closed' ? 'disabled' : ''; ?>>
                <svg viewBox="0 0 24 24" width="24" height="24" class="send-icon" style="display: block; ">
                    <path fill="currentColor" d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                </svg>
                <div class="chat-spinner" style="display: none;"></div>
            </button>
        </form>
    </div>
    <small> <?php echo $inquiry['status'] == 'closed' ? 'this inquiry is closed, sending message is disabled.' : ''; ?> </small>
</div>

<?php require_once get_stylesheet_directory() . '/sals3/includes/firebase-chat-config.php' ?>