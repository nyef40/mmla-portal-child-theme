<?php
/**
 * Template Name: Portal Login Enhanced
 * Description: Enhanced login page for the portal system.
 */

// At the top of page-profile.php, page-resources.php, etc.
portal_debug("TEMPLATE LOADED", [
    'template' => basename(__FILE__),
    'user_logged_in' => is_user_logged_in(),
    'request_uri' => $_SERVER['REQUEST_URI']
]);

// Redirect if already logged in
if (is_user_logged_in()) {
    
    portal_debug("REDIRECT TRIGGERED", [
        'reason' => !is_user_logged_in() ? 'not_logged_in' : 'no_portal_access',
        'redirect_to' => '/portal-login/'
    ]);
    
    wp_redirect(home_url('/dashboard/'));
    exit;
}

get_header(); ?>

<div id="portal-page-main">
<div class="portal-container">
    <div class="portal-content-area" style="padding: 40px 20px;">
        <div class="portal-login-form" style="max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
            <h1 style="text-align: center; margin-bottom: 30px; color: #0A3D62; font-size: 2em;">Login to Your Portal</h1>
            
            <div id="portal-login-messages"></div>
            
            <form id="portal-login-form" class="portal-form">
                <input type="hidden" name="nonce" id="portal-login-nonce" value="">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="username" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Username or Email</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username or email" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="password" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" required placeholder="Enter your password" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease; padding-right: 50px;">
                        <button type="button" id="toggle-password" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; font-size: 18px;">👁️</button>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="remember" value="1" style="margin: 0;"> Remember Me
                    </label>
                </div>
                
                <div class="form-actions" style="margin-bottom: 30px;">
                    <button type="submit" class="button" id="login-btn" style="width: 100%; background: linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%); color: white; padding: 15px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Login</button>
                </div>
            </form>
            
            <div class="portal-login-links" style="text-align: center;">
                <p style="margin-bottom: 10px;"><a href="/register/" style="color: #0A3D62; text-decoration: none; font-weight: 600;">Don't have an account? Register here</a></p>
                <p style="margin-bottom: 10px;"><a href="<?php echo wp_lostpassword_url(); ?>" style="color: #666; text-decoration: none;">Forgot your password?</a></p>
                <p><a href="/" style="color: #666; text-decoration: none;">← Back to Main Site</a></p>
            </div>
        </div>
    </div>
</div>

<style>
#username:focus, #password:focus {
    border-color: #0A3D62;
    outline: none;
    box-shadow: 0 0 0 3px rgba(10, 61, 98, 0.1);
}

#login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(10, 61, 98, 0.3);
}

.portal-success {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
}

.portal-error {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}

.portal-loading {
    background: #d1ecf1;
    color: #0c5460;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #bee5eb;
}
</style>

<script>
(function() {
var ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';

// Fetch fresh nonce and optionally set on a specific form (fixes "Security check failed" when page is cached or duplicate form used)
function refreshLoginNonce(optionalForm) {
    var fd = new FormData();
    fd.append('action', 'portal_login_fresh_nonce');
    fd.append('_t', Date.now());
    return fetch(ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin', cache: 'no-store' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var nonce = data.success && data.data && data.data.nonce ? data.data.nonce : null;
            if (nonce) {
                if (optionalForm) {
                    var input = optionalForm.querySelector('input[name="nonce"]');
                    if (input) input.value = nonce;
                } else {
                    var el = document.getElementById('portal-login-nonce');
                    if (el) el.value = nonce;
                }
            }
            return nonce;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    refreshLoginNonce();
    // Toggle password visibility (all login forms on page)
    document.querySelectorAll('#toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = btn.closest('form');
            var passwordField = form ? form.querySelector('#password') : document.getElementById('password');
            if (passwordField) {
                var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                btn.textContent = type === 'password' ? '👁️' : '🙈';
            }
        });
    });
});

// Handle form submission – fetch fresh nonce first so cached/duplicate form always works
document.addEventListener('submit', function(e) {
    var form = e.target;
    if (!form || form.id !== 'portal-login-form') return;
    e.preventDefault();

    var loginBtn = form.querySelector('#login-btn');
    var messageDiv = form.closest('.portal-login-form') ? form.closest('.portal-login-form').querySelector('#portal-login-messages') : document.getElementById('portal-login-messages');
    if (!messageDiv) messageDiv = form.querySelector('#portal-login-messages') || document.getElementById('portal-login-messages');
    var originalText = loginBtn ? loginBtn.textContent : 'Login';

    if (loginBtn) { loginBtn.textContent = 'Logging in...'; loginBtn.disabled = true; }
    if (messageDiv) messageDiv.innerHTML = '<div class="portal-loading">Logging in...</div>';

    refreshLoginNonce(form).then(function(nonce) {
        if (!nonce) {
            if (messageDiv) messageDiv.innerHTML = '<div class="portal-error">Security token could not be loaded. Please refresh the page.</div>';
            if (loginBtn) { loginBtn.textContent = originalText; loginBtn.disabled = false; }
            return;
        }
        var formData = new FormData(form);
        formData.set('action', 'portal_login');
        formData.set('nonce', nonce);

        fetch(ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin', cache: 'no-store' })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    if (messageDiv) messageDiv.innerHTML = '<div class="portal-success">Login successful! Redirecting...</div>';
                    setTimeout(function() { window.location.href = data.data.redirect || '/dashboard/'; }, 1000);
                } else {
                    if (messageDiv) messageDiv.innerHTML = '<div class="portal-error">' + (data.data || 'Login failed. Please try again.') + '</div>';
                    if (loginBtn) { loginBtn.textContent = originalText; loginBtn.disabled = false; }
                }
            })
            .catch(function() {
                if (messageDiv) messageDiv.innerHTML = '<div class="portal-error">An error occurred. Please try again.</div>';
                if (loginBtn) { loginBtn.textContent = originalText; loginBtn.disabled = false; }
            });
    });
});
})();
</script>
</div><!-- #portal-page-main -->

<?php get_footer(); ?>





