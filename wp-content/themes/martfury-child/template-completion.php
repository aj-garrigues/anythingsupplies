<?php

/**
 * Template Name: Template Account Completion
 * Template Post Type: page
 */

// Disabling the standard header/footer to keep the landing page clean.
// If you want your site's menu, change this to get_header();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Account Completion | <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
    <style>
        .card{
            margin-bottom: 20px;
        }
        .bg-blue-light{
background: #D6EFFF;
background: linear-gradient(300deg, rgba(214, 239, 255, 1) 0%, rgba(214, 239, 255, 1) 0%, rgba(247, 252, 255, 1) 79%);
        }
        .container-fluid{
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
            border-bottom: 1px solid #e0e0e0;
        }
         .completion-container {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    margin: 60px auto;
    border:1px solid #eee;
    width: 600px;
}
.account-type-grid{
    text-align: center;
}
/* Progress Bar */
.step-progress {
    display: flex;
    justify-content: space-between;
    list-style: none;
    padding: 0;
    margin-bottom: 30px;
}
.step-progress li {
    flex: 1;
    text-align: center;
    padding: 10px;
    color: #ccc;
    font-weight: bold;
}
.step-progress li.active {
    color: #cc0001;
}

/* Tabs */
.tab-content { display: none; }
.tab-content.active { 
    display: flex; 
    flex-direction: column; 
    gap: 15px; 
    animation: fadeIn 0.4s ease;
}

/* Inputs & Buttons */
.input-grid { display: flex; grid-template-columns: 1fr 1fr; gap: 10px; }
.input-grid .input-label{
    align-items: right;
    font-weight: 500;
    text-align: right; min-width: 150px;
}
.input-grid .input-group {
    width:55%
}
input[type="text"], input[type="password"], input[type="tel"] {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px; width: 100%;
}
.next-btn, .submit-btn {
    background: #cc0001;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 6px;
    cursor: pointer;
}
.prev-btn {
    background: #eee;
    border: none;
    padding: 12px;
    border-radius: 6px;
    cursor: pointer;
}
.btn-group { margin-top:40px; text-align:center }

.stepper-wrapper {
    padding: 20px 0 0px;
}

.step-progress {
    display: flex;
    justify-content: space-between;
    position: relative;
    padding: 0;
    margin: 0;
    list-style: none;
}

/* The Connecting Line */
.step-progress::before {
    content: "";
    position: absolute;
    top: 25px; /* Centers line behind the 50px circles */
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #cc0001;
    z-index: 1;
}

.step-item {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

/* The Circle */
.step-counter {
    width: 34px;
    height: 34px;
    background-color: #e9e9e9;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    color: #525252;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.step-name {
    font-size: 14px;
    color: #b5b5b5;
    font-weight: 500;
}

/* Active State - Green like your image */
.step-item.active .step-counter {
    background-color: #cc0001; /* Green shade from your image */
    border-color: #cc0001;
    color: #fff;
    box-shadow: 0 4px 10px rgba(88, 214, 141, 0.3);
}

.step-item.active .step-name {
    color: #cc0001;
}

/* Completed State Line (Optional: if you want the line to turn green as you go) */
.step-item.active ~ .step-item::before {
    background-color: #e0e0e0;
}
/* Completed State Line (Optional: if you want the line to turn green as you go) */
.step-item.active ~ .step-item::before {
    background-color: #e0e0e0;
}
.type-card{
    text-align: center;
}
.type-card img {
    width: 150px;
    object-fit: contain;
    margin-bottom: 10px; filter: grayscale(100%);
}
.type-card input[type="checkbox"]:checked + img {
  filter: grayscale(0%);
  transform: scale(1.05); /* Optional: slight zoom to show it's active */
  border: 3px solid #4CAF50; /* Optional: green border */
}
.type-card input[type="checkbox"] {
    display: none;  
}

.type-card.selected-effect {
  background-color: #f0fdf4;
  border-color: #22c55e;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
} 
 
.type-card.selected-effect img {
  filter: grayscale(0%); /* Combine with your previous grayscale logic */
  transform: scale(1.02);
}

.completion-password-wrapper .pass-contain{
width: 50%;
}
.completion-password-wrapper .pass-contain input{
width: 100%;
} 
.completion-password-wrapper .pass-contain #password-meter-bar, .completion-password-wrapper .pass-contain #password-match  {
    height: 5px; width: 100%; background: rgb(153, 255, 153); transition: 0.3s; margin-top: 5px; vertical-align: top;
}
.upload-box {
    border: 2px dashed #ccc;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    background: #f9f9f9;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.upload-box.highlight {
    border-color: #2eb82e;
    background-color: #e7f7e7;
}

.browse-link {
    color: #0073aa;
    text-decoration: underline;
    font-weight: bold;
}

#image-preview img {
    max-width: 150px;
    height: auto;
    border-radius: 50%;
    margin-top: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.circular-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover; /* Important: prevents stretching */
    border: 3px solid #2eb82e;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

/* Ensure the upload box can fit the 150px preview */
.upload-box {
    min-height: 200px;
}
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
<script>
    let phoneInput;
    let phoneError;
    let iti;
    jQuery(document).ready(function($) {

        $('.toggle-check').on('change', function() {
            if ($(this).is(':checked')) {
                console.log('Checkbox checked:', $(this).val());
                $(this).closest('.type-card').addClass('selected-effect');
            } else {
                $(this).closest('.type-card').removeClass('selected-effect');
            }
        });

        let dropArea = document.getElementById('drop-area');
        let fileInput = document.getElementById('profile_photo');
        let preview = document.getElementById('image-preview');

        // Prevent default behaviors for drag/drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Visual highlights
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('highlight'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('highlight'), false);
        });

        // Handle dropped files
        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            let dt = e.dataTransfer;
            let files = dt.files;
            handleFiles(files);
        }

        // Also allow clicking to select file
        dropArea.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            const status = document.getElementById('image-preview'); // Use preview area for messages
            const fileInput = document.getElementById('profile_photo');
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes

            if (files.length > 0) {
                let file = files[0];

                // 1. Check File Type
                if (!file.type.startsWith('image/')) {
                    status.innerHTML = '<span class="error-msg">Error: Please upload an image file.</span>';
                    return;
                }

                // 2. Check File Size (HCI Best Practice: Immediate feedback)
                if (file.size > maxSize) {
                    status.innerHTML = '<span class="error-msg">Error: File is too large (Max 5MB).</span>';
                    fileInput.value = ""; // Clear the input
                    return;
                }

                // 3. If passed, assign to input and show preview
                fileInput.files = files;
                displayPreview(file);
            }
        }

        function displayPreview(file) {
            let reader = new FileReader();
            const previewContainer = document.getElementById('image-preview');
            
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                let img = new Image();
                img.src = event.target.result;

                img.onload = function() {
                    // Create a canvas to perform the crop
                    let canvas = document.createElement('canvas');
                    let ctx = canvas.getContext('2d');

                    // Set the canvas to exactly 350x350
                    canvas.width = 350;
                    canvas.height = 350;

                    // Calculate ratios to center-crop (HCI best practice)
                    let ratio = Math.max(canvas.width / img.width, canvas.height / img.height);
                    let newWidth = img.width * ratio;
                    let newHeight = img.height * ratio;
                    let x = (canvas.width - newWidth) / 2;
                    let y = (canvas.height - newHeight) / 2;

                    // Draw and crop
                    ctx.drawImage(img, x, y, newWidth, newHeight);

                    // Display the 350x350 result
                    let croppedImageUrl = canvas.toDataURL("image/jpeg", 0.9);
                    previewContainer.innerHTML = `<img src="${croppedImageUrl}" class="circular-preview" alt="Profile Preview">`;
                    
                    // OPTIONAL: If you want to upload the cropped version instead of the original, 
                    // you would convert the canvas to a blob here.
                }
            }
        }

        // Initialize intl-tel-input
        phoneInput = document.querySelector("#completion_phone_number");
        phoneError = document.querySelector("#phone-error");

        iti = window.intlTelInput(phoneInput, {
            initialCountry: "auto",
            nationalMode: false,
            geoIpLookup: function(callback) {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("US"));
            },
            loadUtils: () => import("https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.11/build/js/utils.js")
        });

    });

    function validatePhone() {
        const errorMap = [
            "Invalid number",
            "Invalid country code",
            "Too short",
            "Too long",
            "Invalid number"
        ];

        if (phoneInput.value.trim() === "") {
            phoneError.innerHTML = "Phone number is required.";
            return false;
        }

        if (!iti.isValidNumber()) {
            const errorCode = iti.getValidationError();
            phoneError.innerHTML = errorMap[errorCode] || "Invalid phone number.";
            return false;
        }

        phoneError.innerHTML = "";
        phoneInput.value = iti.getNumber(); // convert to +63 format
        return true;
    }

    function checkPasswordStrength() {
        const pass = document.getElementById('main_password').value;
        const meter = document.getElementById('password-meter-bar');
        const text = document.getElementById('password-text');
        let strength = 0;

        if (pass.length >= 8) strength++;
        if (pass.match(/[a-z]/) && pass.match(/[A-Z]/)) strength++;
        if (pass.match(/\d/)) strength++;
        if (pass.match(/[^a-zA-Z\d]/)) strength++;

        const colors = ['#eee', '#ff4d4d', '#ffa64d', '#99ff99', '#2eb82e'];
        const labels = ['Too Short', 'Weak', 'Fair', 'Good', 'Strong'];
        
        meter.style.width = (strength * 25) + '%';
        meter.style.background = colors[strength];
        text.innerText = "Strength: " + labels[strength];
    }

    function validatePasswordMatch() {
        const pass = document.getElementById('main_password').value;
        const confirm = document.getElementById('confirm_password').value;
        const msg = document.getElementById('match-text');
        const btn = document.getElementById('next-step-1');

        if (pass === confirm && pass.length >= 8) {
            msg.innerText = "Passwords match!";
            msg.style.color = "green";
            btn.disabled = false;
        } else {
            msg.innerText = "Passwords do not match.";
            msg.style.color = "red";
            btn.disabled = true;
        }
    }

    function nextStep(step) {
    console.log('Navigating to step: ' + step);

    // 1. VALIDATION LOGIC
    if (step == 2) {
        // Step 1 -> 2 Validation: Checkboxes
        const checkboxes = document.querySelectorAll('input[name="account_types[]"]');
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);

        if (!anyChecked) {
            document.querySelector('.action-type-status').innerHTML = 'Please select at least one account type.';
            return; // Stop here
        }
    } 
    else if (step == 3) {
        // Step 2 -> 3 Validation: Profile & Password
        const firstName = document.querySelector('input[name="first_name"]').value.trim();
        const lastName = document.querySelector('input[name="last_name"]').value.trim();
        const password = document.getElementById('main_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const statusElement = document.querySelector('.profile-action-type-status');

        if (firstName === '' || lastName === '') {
            statusElement.innerHTML = 'Please fill in all required profile information.';
            return;
        }
        if (password.length < 8) {
            statusElement.innerHTML = 'Password must be at least 8 characters long.';
            return;
        }
        if (password !== confirmPassword) {
            statusElement.innerHTML = 'Passwords do not match.';
            return;
        }
        if (!validatePhone()) {
            statusElement.innerHTML = 'Please enter a valid phone number.';
            phoneInput.focus();
            return;
        }
    }

    // 2. NAVIGATION LOGIC (Only runs if validation passes)
    // Hide all tabs and progress indicators
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.step-progress li').forEach(li => li.classList.remove('active'));

    // Show target tab and update progress bar
    const targetTab = document.getElementById('tab-' + step);
    const targetStep = document.getElementById('step-' + step);

    if (targetTab && targetStep) {
        targetTab.classList.add('active');
        targetStep.classList.add('active');
    } else {
        console.error('Target step or tab does not exist: ' + step);
    }
}

    </script>
</head>
<body <?php body_class(); ?>>
<div class="container-fluid">
    <div class="container registration-header">
    <div class="row">
        <div class="col-lg-6">
            <a class="myaccount-header-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img class="logo-register" alt="Anything Supplies" src="<?php echo esc_url( get_theme_file_uri() ); ?>/images/SALS3-03-scaled.webp">
                <span class="logo-myaccount-text"> | My Account</span>
            </a>
        
        </div>
        <div class="col-lg-6">
            <div class="myaccount-header-help">
            <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class=" has-icon font-black"><i class="ion-bag"></i> Shop</a> |  
            <a href="<?php echo esc_url( home_url( '/support/' ) ); ?>" class=" has-icon  font-black"><i class="ion-help"></i> Help</a>
            </div>
        </div>
    </div>
    </div>
</div>

<div class="  bg-blue-light">
   <div class="container ">

 <?php
if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		the_content();
	endwhile;

endif;
?>
</div>
</div>
<?php wp_footer();
get_footer();
?>
</body>
</html>