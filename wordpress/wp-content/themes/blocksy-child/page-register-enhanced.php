<?php
/**
 * Template Name: Register Enhanced
 * Description: Enhanced registration page for the portal system.
 */

// At the top of page-profile.php, page-resources.php, etc.
portal_debug("TEMPLATE LOADED", [
    'template' => basename(__FILE__),
    'user_logged_in' => is_user_logged_in(),
    'request_uri' => $_SERVER['REQUEST_URI']
]);

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['portal_register'])) {
    portal_debug("REGISTRATION FORM SUBMITTED", ['post_data' => $_POST]);
    // Your registration logic here
}

// One-time token for registration when nonce fails (e.g. cache/session on live)
$portal_register_page_token = wp_generate_password(32, false, false);
set_transient('portal_reg_' . hash('sha256', $portal_register_page_token), '1', 600);

get_header(); ?>
<div id="portal-page-main">
<div class="portal-container" style="display: flex; justify-content: center;">
    <div class="portal-content-area" style="max-width: 600px; width: 100%;">
        <?php if (is_user_logged_in()) : ?>
        <p style="text-align: center; margin-bottom: 20px; padding: 12px; background: #e7f3ff; border-radius: 8px;">
            You are already logged in. <a href="<?php echo esc_url(home_url('/dashboard/')); ?>">Go to Dashboard</a>
        </p>
        <?php endif; ?>
        <h1 style="text-align: center; margin-bottom: 30px;">Create Your Portal Account</h1>
        <div id="portal-register-messages"><?php
            if (!empty($_GET['error']) && get_transient('portal_register_error')) {
                echo '<div class="portal-error">' . esc_html(get_transient('portal_register_error')) . '</div>';
                delete_transient('portal_register_error');
            }
        ?></div>
        <form id="portal-register-form" class="portal-form" method="post">
            <input type="hidden" name="action" value="portal_register">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('portal_register_nonce'); ?>">
            <input type="hidden" name="portal_register_token" value="<?php echo esc_attr($portal_register_page_token); ?>">
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required placeholder="Enter your first name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required placeholder="Enter your last name">
                </div>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                    <small>We'll send a verification email to this address</small>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="(555) 123-4567" maxlength="20">
                </div>
            </div>
            <div class="form-section">
                <h3>Account Credentials</h3>
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required placeholder="Choose a unique username">
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" required minlength="8" placeholder="Enter your password">
                        <button type="button" id="toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 18px;">👁️</button>
                    </div>
                    <small>Minimum 8 characters, include letters and numbers</small>
                    <small id="password-strength"></small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                        <button type="button" id="toggle-confirm-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 18px;">👁️</button>
                    </div>
                </div>
            </div>
            <div class="form-section">
                <h3>Professional & Location Information</h3>
                <div class="form-group">
                    <label for="role">Your Role *</label>
                    <select id="role" name="role" required style="height: 50px; padding: 12px; font-size: 16px;">
                        <option value="">Select Your Role</option>
                        <option value="RN">Registered Nurse (RN)</option>
                        <option value="LVN">Licensed Vocational Nurse (LVN)</option>
                        <option value="PT">Physical Therapist (PT)</option>
                        <option value="OT">Occupational Therapist (OT)</option>
                        <option value="ST">Speech Therapist (ST)</option>
                        <option value="MSW">Medical Social Worker (MSW)</option>
                        <option value="CNA">Certified Nursing Assistant (CNA)</option>
                        <option value="HHA">Home Health Aide (HHA)</option>
                        <option value="Admin">Administrative Staff</option>
                        <option value="Other">Other Healthcare Professional</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="specialty">Medical Specialty</label>
                    <input type="text" id="specialty" name="specialty" placeholder="e.g., Home Health, Wound Care">
                </div>
                <div class="form-group">
                    <label for="license_number">License Number</label>
                    <input type="text" id="license_number" name="license_number" placeholder="Professional license number">
                </div>
                <div class="form-group">
                    <label for="practice_name">Practice/Organization Name</label>
                    <input type="text" id="practice_name" name="practice_name" placeholder="Mobile Medical LA" value="Mobile Medical LA">
                </div>
                <div class="form-group">
                    <label for="address">Street Address</label>
                    <input type="text" id="address" name="address" placeholder="123 Main St">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city">
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" maxlength="2" placeholder="CA">
                </div>
                <div class="form-group">
                    <label for="zip">Zip Code</label>
                    <input type="text" id="zip" name="zip" maxlength="10">
                </div>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: flex-start; gap: 8px;">
                    <input type="checkbox" name="terms" value="1" required style="margin-top: 4px;">
                    <span>I agree to the <a href="/terms/" target="_blank">Terms of Service</a> and <a href="/privacy/" target="_blank">Privacy Policy</a></span>
                </label>
            </div>
            <div class="form-actions" style="text-align: center;">
                <button type="submit" class="button" id="register-btn">Create Account</button>
            </div>
        </form>
        <div class="portal-register-links" style="text-align: center; margin-top: 30px;">
            <p><a href="/portal-login/">Already have an account? Login here</a></p>
            <p><a href="/">← Back to Main Site</a></p>
        </div>
    </div>
</div>
</div><!-- #portal-page-main -->
<script>
(function(){
  function run(){
    if (typeof window.jQuery === 'undefined') { setTimeout(run, 30); return; }
    window.jQuery(document).ready(function($) {
    // Password strength validation
    function updatePasswordStrength() {
        var password = $('#password').val();
        var strengthDisplay = $('#password-strength');
        var hasLetter = /[a-zA-Z]/.test(password);
        var hasNumber = /\d/.test(password);
        var isLongEnough = password.length >= 8;
        var strength = 'Weak';
        var color = '#dc3545';
        if (hasLetter && hasNumber && isLongEnough) {
            strength = 'Strong';
            color = '#28a745';
        } else if ((hasLetter || hasNumber) && isLongEnough) {
            strength = 'Medium';
            color = '#ffc107';
        }
        strengthDisplay.text('Password strength: ' + strength).css('color', color);
    }

    // Toggle password visibility
    $('#toggle-password').click(function() {
        var passwordField = $('#password');
        var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).text(type === 'password' ? '👁️' : '🙈');
    });

    $('#toggle-confirm-password').click(function() {
        var passwordField = $('#confirm_password');
        var type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        $(this).text(type === 'password' ? '👁️' : '🙈');
    });

    // Phone number formatting
    $('#phone').on('input', function(e) {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) value = value.slice(0, 10);
        if (value.length >= 6) {
            value = '(' + value.slice(0, 3) + ') ' + value.slice(3, 6) + '-' + value.slice(6);
        } else if (value.length >= 3) {
            value = '(' + value.slice(0, 3) + ') ' + value.slice(3);
        }
        $(this).val(value);
    });

    // Password strength on input
    $('#password').on('input', updatePasswordStrength);

    // Form submission: delegated so BOTH upper and lower forms use AJAX; fetch fresh nonce first
    $(document).on('submit', '#portal-register-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var password = form.find('#password').val();
        var confirmPassword = form.find('#confirm_password').val();
        var messageDiv = form.closest('.portal-content-area').find('#portal-register-messages');
        if (!messageDiv.length) messageDiv = $('#portal-register-messages').first();
        if (password !== confirmPassword) {
            messageDiv.html('<div class="portal-error">Passwords do not match.</div>');
            return;
        }
        var registerBtn = form.find('#register-btn');
        var originalText = registerBtn.text();
        registerBtn.text('Creating Account...').prop('disabled', true);
        var ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: { action: 'portal_register_fresh_nonce', _t: Date.now() },
            dataType: 'json',
            cache: false
        }).then(function(nonceResp) {
            var nonce = (nonceResp && nonceResp.success && nonceResp.data && nonceResp.data.nonce) ? nonceResp.data.nonce : null;
            if (!nonce) {
                messageDiv.html('<div class="portal-error">Security token could not be loaded. Please refresh the page.</div>');
                registerBtn.text(originalText).prop('disabled', false);
                return $.Deferred().reject();
            }
            form.find('input[name="nonce"]').val(nonce);
            return $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: form.serialize(),
                dataType: 'json'
            });
        }).then(function(response) {
            if (!response) return;
            if (response.success) {
                messageDiv.html('<div class="portal-success">Registration successful! Please check your email to verify your account.</div>');
                setTimeout(function() { window.location.href = '/portal-login/?registered=1'; }, 2000);
            } else {
                messageDiv.html('<div class="portal-error">' + (response.data || 'Registration failed. Please try again.') + '</div>');
                registerBtn.text(originalText).prop('disabled', false);
            }
        }).fail(function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.data) ? xhr.responseJSON.data : 'An error occurred. Please try again.';
            messageDiv.html('<div class="portal-error">' + msg + '</div>');
            registerBtn.text(originalText).prop('disabled', false);
        });
    });
  });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
  else run();
})();
</script>
<?php get_footer(); ?>
