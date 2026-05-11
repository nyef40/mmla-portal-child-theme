<?php
/**
 * Template Name: Portal Profile
 */

// At the top of page-profile.php, page-resources.php, etc.
portal_debug("TEMPLATE LOADED", [
    'template' => basename(__FILE__),
    'user_logged_in' => is_user_logged_in(),
    'request_uri' => $_SERVER['REQUEST_URI']
]);

// Redirect if user is not logged in or doesn't have portal access
if (!is_user_logged_in() || (function_exists('user_has_portal_access_enhanced') && !user_has_portal_access_enhanced())) {
    
    // Temporary debug - add this before the redirect
    error_log('Portal Profile Access Check:');
    error_log('User logged in: ' . (is_user_logged_in() ? 'YES' : 'NO'));
    error_log('User ID: ' . get_current_user_id());

    if (function_exists('user_has_portal_access_enhanced')) {
        $has_access = user_has_portal_access_enhanced();
        error_log('Has portal access: ' . ($has_access ? 'YES' : 'NO'));
    } else {
        error_log('user_has_portal_access_enhanced function does not exist');
    }

    portal_debug("REDIRECT TRIGGERED", [
        'reason' => !is_user_logged_in() ? 'not_logged_in' : 'no_portal_access',
        'redirect_to' => '/portal-login/'
    ]);
    
    wp_redirect(home_url('/portal-login/'));
    exit;
}

get_header(); ?>

<div id="portal-page-main">
<div class="portal-container">
    <div class="portal-profile-form">
        <h1>My Profile</h1>
        <p class="form-subtitle">Update your personal and professional information.</p>

        <?php
        global $wpdb;
        $current_user = wp_get_current_user();
        $portal_user_table = $wpdb->prefix . 'portal_users';
        $portal_user_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$portal_user_table} WHERE wp_user_id = %d", $current_user->ID));

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile_nonce']) && wp_verify_nonce($_POST['update_profile_nonce'], 'update_portal_profile')) {
            $updated_data = [];
            $format = [];

            // Personal Information
            if (isset($_POST['first_name'])) {
                $updated_data['first_name'] = sanitize_text_field($_POST['first_name']);
                $format[] = '%s';
            }
            if (isset($_POST['last_name'])) {
                $updated_data['last_name'] = sanitize_text_field($_POST['last_name']);
                $format[] = '%s';
            }
            if (isset($_POST['phone'])) {
                $updated_data['phone'] = sanitize_text_field($_POST['phone']);
                $format[] = '%s';
            }

            // Professional & Location Information
            if (isset($_POST['role'])) {
                $updated_data['role'] = sanitize_text_field($_POST['role']);
                $format[] = '%s';
            }
            if (isset($_POST['specialty'])) {
                $updated_data['specialty'] = sanitize_text_field($_POST['specialty']);
                $format[] = '%s';
            }
            if (isset($_POST['license_number'])) {
                $updated_data['license_number'] = sanitize_text_field($_POST['license_number']);
                $format[] = '%s';
            }
            if (isset($_POST['practice'])) {
                $updated_data['practice'] = sanitize_text_field($_POST['practice']);
                $format[] = '%s';
            }
            if (isset($_POST['address'])) {
                $updated_data['address'] = sanitize_text_field($_POST['address']);
                $format[] = '%s';
            }
            if (isset($_POST['city'])) {
                $updated_data['city'] = sanitize_text_field($_POST['city']);
                $format[] = '%s';
            }
            if (isset($_POST['state'])) {
                $updated_data['state'] = sanitize_text_field($_POST['state']);
                $format[] = '%s';
            }
            if (isset($_POST['zip'])) {
                $updated_data['zip'] = sanitize_text_field($_POST['zip']);
                $format[] = '%s';
            }

            if (!empty($updated_data)) {
                $update_result = $wpdb->update(
                    $portal_user_table,
                    $updated_data,
                    ['wp_user_id' => $current_user->ID],
                    $format,
                    ['%d']
                );

                if ($update_result !== false) {
                    echo '<div class="success-message">Profile updated successfully!</div>';
                    // Re-fetch data to display updated values
                    $portal_user_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$portal_user_table} WHERE wp_user_id = %d", $current_user->ID));
                } else {
                    echo '<div class="error-message">Failed to update profile. Please try again.</div>';
                }
            } else {
                echo '<div class="info-message">No changes submitted.</div>';
            }
        }
        ?>

        <form method="post" action="" class="portal-profile-form-inner">
            <?php wp_nonce_field('update_portal_profile', 'update_profile_nonce'); ?>

            <div class="form-section">
                <h3>Personal Information</h3>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($portal_user_data->first_name); ?>">
                    </div>

                    <div class="form-group half">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($portal_user_data->last_name); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo esc_attr($portal_user_data->email); ?>" readonly disabled>
                    <small>Email cannot be changed here.</small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($portal_user_data->phone); ?>" placeholder="(555) 123-4567">
                </div>
            </div>

            <div class="form-section">
                <h3>Professional & Location Information</h3>

                <div class="form-group">
                    <label for="role">Your Role</label>
                    <select id="role" name="role">
                        <?php
                        $roles = [
                            '' => 'Select Your Role',
                            'RN' => 'Registered Nurse (RN)',
                            'LVN' => 'Licensed Vocational Nurse (LVN)',
                            'PT' => 'Physical Therapist (PT)',
                            'OT' => 'Occupational Therapist (OT)',
                            'ST' => 'Speech Therapist (ST)',
                            'MSW' => 'Medical Social Worker (MSW)',
                            'CNA' => 'Certified Nursing Assistant (CNA)',
                            'HHA' => 'Home Health Aide (HHA)',
                            'Admin' => 'Administrative Staff',
                            'Other' => 'Other Healthcare Professional'
                        ];
                        foreach ($roles as $value => $label) {
                            $selected = ($portal_user_data->role === $value) ? 'selected' : '';
                            echo "<option value=\"{$value}\" {$selected}>{$label}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label for="specialty">Specialty/Department</label>
                        <input type="text" id="specialty" name="specialty" value="<?php echo esc_attr($portal_user_data->specialty); ?>" placeholder="e.g., Home Health, Wound Care">
                    </div>

                    <div class="form-group half">
                        <label for="license_number">License Number</label>
                        <input type="text" id="license_number" name="license_number" value="<?php echo esc_attr($portal_user_data->license_number); ?>" placeholder="Professional license number">
                    </div>
                </div>

                <div class="form-group">
                    <label for="practice">Organization/Practice Name</label>
                    <input type="text" id="practice" name="practice" value="<?php echo esc_attr($portal_user_data->practice); ?>" placeholder="Mobile Medical LA or partner organization">
                </div>

                <div class="form-group">
                    <label for="address">Street Address</label>
                    <input type="text" id="address" name="address" value="<?php echo esc_attr($portal_user_data->address); ?>" placeholder="123 Main St">
                </div>

                <div class="form-row">
                    <div class="form-group third">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?php echo esc_attr($portal_user_data->city); ?>">
                    </div>
                    <div class="form-group third">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" maxlength="2" value="<?php echo esc_attr($portal_user_data->state); ?>" placeholder="CA">
                    </div>
                    <div class="form-group third">
                        <label for="zip">Zip Code</label>
                        <input type="text" id="zip" name="zip" maxlength="10" value="<?php echo esc_attr($portal_user_data->zip); ?>">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>
</div><!-- #portal-page-main -->

<style>
.portal-profile-form {
    max-width: 700px;
    margin: 50px auto;
    padding: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.portal-profile-form h1 {
    text-align: center;
    margin-bottom: 10px;
    color: #0A3D62;
    font-size: 2.2rem;
}

.form-subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 40px;
    font-size: 1.1rem;
}

.form-section {
    margin-bottom: 40px;
    padding: 30px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #0A3D62;
}

.form-section h3 {
    color: #0A3D62;
    margin-bottom: 20px;
    font-size: 1.3rem;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group.half {
    flex: 1;
    margin-bottom: 0;
}

.form-group.third {
    flex: 1;
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 16px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

.portal-profile-form select {
    min-height: 48px;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #0A3D62;
    outline: none;
    box-shadow: 0 0 0 3px rgba(10, 61, 98, 0.1);
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 14px;
}

.form-actions {
    margin-top: 40px;
    text-align: center;
}

.button {
    padding: 16px 40px;
    background: linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(10, 61, 98, 0.3);
}

.success-message {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #c3e6cb;
    text-align: center;
}

.success-message h3 {
    margin: 0 0 15px 0;
    color: #155724;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #f5c6cb;
}

.info-message {
    background: #d1ecf1;
    color: #0c5460;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #bee5eb;
}

@media (max-width: 768px) {
    .portal-profile-form {
        margin: 20px;
        padding: 30px 20px;
    }

    .form-row {
        flex-direction: column;
        gap: 0;
    }

    .form-group.half,
    .form-group.third {
        margin-bottom: 20px;
    }
    .form-section {
        padding: 20px;
    }
}

/* Add to your existing CSS */
.button:disabled {
    background: linear-gradient(135deg, #6c757d 0%, #868e96 100%) !important;
    cursor: not-allowed !important;
    opacity: 0.7 !important;
}

.button:disabled:hover {
    transform: none !important;
    box-shadow: none !important;
}

.form-status-message {
    padding: 10px 15px;
    margin: 10px 0;
    border-radius: 5px;
    text-align: center;
    font-size: 14px;
    animation: fadeIn 0.3s ease;
}

.info-message {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}

</style>

<script>
    
// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting (existing code)
    const phoneField = document.getElementById('phone');
    if (phoneField) {
        phoneField.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
            }
            e.target.value = value;
        });
    }
    
// Enhanced: Disable submit button when no changes exist
function setupFormChangeDetection() {
    const form = document.querySelector('.portal-profile-form-inner');
    const submitBtn = document.querySelector('.button-primary');
    const formFields = form.querySelectorAll('input:not([readonly]):not([disabled]), select, textarea');
    
    if (!form || !submitBtn) return;
    
    // Store initial form state
    const initialValues = {};
    formFields.forEach(field => {
        initialValues[field.name || field.id] = field.value;
    });
    
    // Function to check if any field has changed
    function checkForChanges() {
        let hasChanges = false;
        
        formFields.forEach(field => {
            const fieldId = field.name || field.id;
            const currentValue = field.value;
            const initialValue = initialValues[fieldId];
            
            // Special handling for phone field (normalize for comparison)
            if (fieldId === 'phone') {
                const normalizePhone = (phone) => phone.replace(/\D/g, '');
                if (normalizePhone(currentValue) !== normalizePhone(initialValue)) {
                    hasChanges = true;
                }
            } 
            // For all other fields
            else if (currentValue !== initialValue) {
                hasChanges = true;
            }
        });
        
        // Update button state
        submitBtn.disabled = !hasChanges;
        submitBtn.title = hasChanges ? 'Click to update profile' : 'Make changes to enable update';
        
        // Visual feedback for disabled state
        if (!hasChanges) {
            submitBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
        
        return hasChanges;
    }
    
    // Listen for changes on all form fields
    formFields.forEach(field => {
        field.addEventListener('input', checkForChanges);
        field.addEventListener('change', checkForChanges);
    });
    
    // Also check on page load
    checkForChanges();
    
    // Prevent submission when no changes (extra safety)
    form.addEventListener('submit', function(e) {
        if (!checkForChanges()) {
            e.preventDefault();
            // Optional: Show a subtle message instead of alert
            showTemporaryMessage('No changes to update', 'info');
            return false;
        }
        
        // If we get here, changes exist - disable button during submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';
        
        // Re-enable after 5 seconds (in case of error)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Profile';
        }, 5000);
    });
}

// Helper function for temporary messages (optional)
function showTemporaryMessage(message, type = 'info') {
    // Remove any existing message
    const existingMsg = document.querySelector('.form-status-message');
    if (existingMsg) existingMsg.remove();
    
    // Create new message
    const msgDiv = document.createElement('div');
    msgDiv.className = `form-status-message ${type}-message`;
    msgDiv.textContent = message;
    msgDiv.style.cssText = `
        padding: 10px 15px;
        margin: 10px 0;
        border-radius: 5px;
        text-align: center;
        font-size: 14px;
        animation: fadeIn 0.3s ease;
    `;
    
    if (type === 'info') {
        msgDiv.style.backgroundColor = '#d1ecf1';
        msgDiv.style.color = '#0c5460';
        msgDiv.style.border = '1px solid #bee5eb';
    }
    
    // Insert after the form subtitle
    const formSubtitle = document.querySelector('.form-subtitle');
    if (formSubtitle) {
        formSubtitle.parentNode.insertBefore(msgDiv, formSubtitle.nextSibling);
    }
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (msgDiv.parentNode) {
            msgDiv.style.opacity = '0';
            msgDiv.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                if (msgDiv.parentNode) msgDiv.remove();
            }, 500);
        }
    }, 3000);
}

// Add form change detection
setupFormChangeDetection();


});

</script>

<?php get_footer(); ?>
