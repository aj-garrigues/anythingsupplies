<div class="my-account-header">
    <h2>My Security</h2>
    <div class="my-account-header-tags">Manage your security settings and preferences</div>
</div>

<div class="edit-account-fields">
    <?php
    // Ensure scripts are loaded for the strength meter
    wp_enqueue_script('password-strength-meter');
    ?>
    
    <div class="security-card">
        <form id="password-change-form" class="password-form" method="post" action="">
            <?php wp_nonce_field('update_security_action', 'security_nonce'); ?>
            
            <!-- Current Password -->
            <div class="password-form-group">
                <label class="password-form-label" for="current_password">Current Password</label>
                <div class="password-input-wrapper">
                    <input 
                        type="password" 
                        class="password-input" 
                        name="current_password" 
                        id="current_password" 
                        placeholder="Enter your current password"
                        required
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('current_password')">
                        <i class="ion-eye eye-icon"></i>
                    </button>
                </div>
            </div>

            <!-- New Password -->
            <div class="password-form-group">
                <label class="password-form-label" for="new_password">New Password</label>
                <div class="password-input-wrapper">
                    <input 
                        type="password" 
                        class="password-input" 
                        name="new_password" 
                        id="new_password" 
                        placeholder="Enter your new password"
                        required
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('new_password')">
                        <i class="ion-eye eye-icon"></i>
                    </button>
                </div>
                
                <!-- Strength Indicator -->
                <div class="strength-indicator-wrapper" id="strengthIndicatorWrapper">
                    <div class="strength-bar">
                        <div class="strength-bar-fill" id="strengthBarFill"></div>
                    </div>
                    <div class="strength-text" id="pass-strength-result" aria-live="polite">Strength indicator</div>
                </div>
                
                <!-- Password Requirements -->
                <div class="password-requirements-box">
                    <div class="requirements-title">It's recommended that password must contain:</div>
                    <div class="requirements-list">
                        <div class="requirement-item" id="req-length">
                            <span class="requirement-check"></span>
                            <span>At least 8 characters</span>
                        </div>
                        <div class="requirement-item" id="req-uppercase">
                            <span class="requirement-check"></span>
                            <span>One uppercase letter</span>
                        </div>
                        <div class="requirement-item" id="req-lowercase">
                            <span class="requirement-check"></span>
                            <span>One lowercase letter</span>
                        </div>
                        <div class="requirement-item" id="req-number">
                            <span class="requirement-check"></span>
                            <span>One number</span>
                        </div>
                        <div class="requirement-item" id="req-special">
                            <span class="requirement-check"></span>
                            <span>One special character</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="password-form-group">
                <label class="password-form-label" for="confirm_password">Confirm New Password</label>
                <div class="password-input-wrapper">
                    <input 
                        type="password" 
                        class="password-input" 
                        name="confirm_password" 
                        id="confirm_password" 
                        placeholder="Confirm your new password"
                        required
                    >
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('confirm_password')">
                        <i class="ion-eye eye-icon"></i>
                    </button>
                </div>
                <div class="error-message" id="confirmPasswordError">
                    ⚠️ Passwords do not match
                </div>
            </div>

            <!-- Submit Section -->
            <div class="password-submit-section">
                <button type="submit" name="submit_security" class="btn-password-update">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.security-card {
    border-radius: 12px;
    padding: 10px;
}

.password-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
    max-width: 500px;
}

.password-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.password-form-label {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.password-input-wrapper {
    position: relative;
    max-width: 100%;
}

.password-input {
    width: 100%;
    max-width: 500px;
    padding: 12px 48px 12px 16px !important;
    border: 1.5px solid #D1D5DB;
    border-radius: 4px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.2s;
}

.password-input:focus {
    outline: none;
    border-color: #0891B2;
    box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.1);
}

.password-input.error {
    border-color: #EF4444;
}

.password-input.success {
    border-color: #10B981;
}

.toggle-password-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    color: #6B7280;
    font-size: 18px;
    transition: color 0.2s;
}

.toggle-password-btn:hover {
    color: #374151;
}

.eye-icon {
    display: block;
    line-height: 1;
}

/* Strength Indicator */
.strength-indicator-wrapper {
    margin-top: 8px;
    opacity: 0;
    transition: opacity 0.3s;
}

.strength-indicator-wrapper.show {
    opacity: 1;
}

.strength-bar {
    height: 6px;
    background: #E5E7EB;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 8px;
}

.strength-bar-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s;
    border-radius: 3px;
}

#pass-strength-result {
    font-size: 13px;
    font-weight: 600;
    padding: 0;
    margin: 0;
    background: transparent !important;
}

/* WordPress strength classes */
#pass-strength-result.short,
#pass-strength-result.bad {
    color: #EF4444;
}

#pass-strength-result.short .strength-bar-fill,
#pass-strength-result.bad .strength-bar-fill {
    width: 33%;
    background: #EF4444;
}

#pass-strength-result.good {
    color: #F59E0B;
}

#pass-strength-result.good .strength-bar-fill {
    width: 66%;
    background: #F59E0B;
}

#pass-strength-result.strong {
    color: #10B981;
}

#pass-strength-result.strong .strength-bar-fill {
    width: 100%;
    background: #10B981;
}

/* Password Requirements */
.password-requirements-box {
    background: #F9FAFB;
    border-radius: 8px;
    padding: 12px;
}

.requirements-title {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 12px;
}

.requirements-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6B7280;
    transition: color 0.2s;
}

.requirement-check {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid #D1D5DB;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    flex-shrink: 0;
    transition: all 0.2s;
}

.requirement-item.met {
    color: #10B981;
}

.requirement-item.met .requirement-check {
    background: #10B981;
    border-color: #10B981;
    color: white;
}

.requirement-item.met .requirement-check::after {
    content: '✓';
    font-weight: bold;
}

/* Error Message */
.error-message {
    display: none;
    color: #EF4444;
    font-size: 13px;
    margin-top: 6px;
    align-items: center;
    gap: 6px;
}

.error-message.show {
    display: flex;
}

.btn-password-update {
    padding: 12px 32px;
    background: #ff4c00;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-password-update:hover {
    background: #be3600 ;
    box-shadow: 0 4px 12px rgba(8, 145, 178, 0.3);
    transform: translateY(-1px);
}

.btn-password-update:disabled {
    background: #D1D5DB;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}

/* Responsive */
@media (max-width: 640px) {
    .security-card {
        padding: 10px 6px;
    }
    
    .password-input {
        max-width: 100%;
    }
    
    .btn-password-update {
        width: 100%;
    }
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    const newPasswordInput = $('#new_password');
    const confirmPasswordInput = $('#confirm_password');
    const strengthIndicator = $('#strengthIndicatorWrapper');
    const strengthBarFill = $('#strengthBarFill');
    const strengthResult = $('#pass-strength-result');
    
    // Password requirements
    const requirements = {
        length: { regex: /.{8,}/, element: 'req-length' },
        uppercase: { regex: /[A-Z]/, element: 'req-uppercase' },
        lowercase: { regex: /[a-z]/, element: 'req-lowercase' },
        number: { regex: /[0-9]/, element: 'req-number' },
        special: { regex: /[!@#$%^&*(),.?":{}|<>]/, element: 'req-special' }
    };
    
    // Check individual requirements
    function checkRequirements(password) {
        if (!password) {
            // Reset all requirements
            for (let key in requirements) {
                $('#' + requirements[key].element).removeClass('met');
            }
            return;
        }
        
        for (let key in requirements) {
            const req = requirements[key];
            const isMet = req.regex.test(password);
            const element = $('#' + req.element);
            
            if (isMet) {
                element.addClass('met');
            } else {
                element.removeClass('met');
            }
        }
    }
    
    // WordPress password strength meter integration
    newPasswordInput.on('keyup', function() {
        const pass = $(this).val();
        
        if (!pass) {
            strengthIndicator.removeClass('show');
            strengthResult.removeClass('short bad good strong').text('Strength indicator');
            strengthBarFill.css('width', '0%');
            checkRequirements('');
            return;
        }
        
        strengthIndicator.addClass('show');
        
        // Check requirements
        checkRequirements(pass);
        
        // Use WordPress built-in zxcvbn logic
        var strength = wp.passwordStrength.meter(pass, wp.passwordStrength.userInputDisallowedList(), pass);
        
        strengthResult.removeClass('short bad good strong');
        strengthBarFill.removeClass('weak medium strong');
        
        switch (strength) {
            case 2:
                strengthResult.addClass('bad').text('Weak');
                strengthBarFill.css('width', '33%').css('background', '#EF4444');
                break;
            case 3:
                strengthResult.addClass('good').text('Medium');
                strengthBarFill.css('width', '66%').css('background', '#F59E0B');
                break;
            case 4:
                strengthResult.addClass('strong').text('Strong');
                strengthBarFill.css('width', '100%').css('background', '#10B981');
                break;
            default:
                strengthResult.addClass('short').text('Too short');
                strengthBarFill.css('width', '20%').css('background', '#EF4444');
                break;
        }
        
        // Check confirm password match if it has a value
        if (confirmPasswordInput.val()) {
            checkPasswordMatch();
        }
    });
    
    // Check password match
    function checkPasswordMatch() {
        const newPassword = newPasswordInput.val();
        const confirmPassword = confirmPasswordInput.val();
        const errorElement = $('#confirmPasswordError');
        
        if (confirmPassword && newPassword !== confirmPassword) {
            confirmPasswordInput.addClass('error').removeClass('success');
            errorElement.addClass('show');
            return false;
        } else if (confirmPassword && newPassword === confirmPassword) {
            confirmPasswordInput.removeClass('error').addClass('success');
            errorElement.removeClass('show');
            return true;
        } else {
            confirmPasswordInput.removeClass('error success');
            errorElement.removeClass('show');
            return false;
        }
    }
    
    confirmPasswordInput.on('keyup', checkPasswordMatch);
    
    // Form submission validation
    $('#password-change-form').on('submit', function(e) {
        const passwordsMatch = checkPasswordMatch();
        
        if (!passwordsMatch && confirmPasswordInput.val()) {
            e.preventDefault();
            alert('Please ensure both passwords match.');
            return false;
        }
    });
});

// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const btn = input.parentElement.querySelector('.toggle-password-btn');
    const icon = btn.querySelector('.eye-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ion-eye');
        icon.classList.add('ion-eye-disabled');
    } else {
        input.type = 'password';
        icon.classList.remove('ion-eye-disabled');
        icon.classList.add('ion-eye');
    }
}
</script>