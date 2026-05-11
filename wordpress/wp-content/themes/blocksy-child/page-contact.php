<?php
/**
 * Template Name: Contact Page
 * Description: Contact page with forms and information
 */

// At the top of page-profile.php, page-resources.php, etc.
portal_debug("TEMPLATE LOADED", [
    'template' => basename(__FILE__),
    'user_logged_in' => is_user_logged_in(),
    'request_uri' => $_SERVER['REQUEST_URI']
]);

get_header(); ?>

<div id="portal-page-main">
<div class="portal-container">
    <div class="portal-content-area" style="padding: 40px 20px;">
        <div class="contact-header" style="text-align: center; margin-bottom: 50px;">
            <h1 style="color: #0A3D62; font-size: 2.5em; margin-bottom: 20px;">Contact Us</h1>
            <p style="font-size: 1.2em; color: #666; max-width: 600px; margin: 0 auto;">Get in touch with our team for support, questions, or additional information. We're here to help you with all your healthcare needs.</p>
        </div>

        <div class="contact-content" style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; max-width: 1200px; margin: 0 auto;">
            <!-- Contact Information -->
            <div class="contact-info">
                <h2 style="color: #0A3D62; margin-bottom: 30px; font-size: 1.8em;">Get In Touch</h2>
                
                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 25px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                    <div style="font-size: 1.5em; color: #0A3D62; margin-right: 15px; margin-top: 5px;">📍</div>
                    <div>
                        <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.2em;">Address</h3>
                        <p style="color: #666; line-height: 1.6; margin: 0;">
                            Mobile Medical LA<br>
                            123 Healthcare Drive<br>
                            Los Angeles, CA 90210
                        </p>
                    </div>
                </div>

                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 25px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                    <div style="font-size: 1.5em; color: #0A3D62; margin-right: 15px; margin-top: 5px;">📞</div>
                    <div>
                        <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.2em;">Phone</h3>
                        <p style="color: #666; line-height: 1.6; margin: 0;">
                            Main: <a href="tel:+1234567890" style="color: #0A3D62; text-decoration: none;">(123) 456-7890</a><br>
                            Emergency: <a href="tel:+1234567891" style="color: #dc3545; text-decoration: none;">(123) 456-7891</a>
                        </p>
                    </div>
                </div>

                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 25px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                    <div style="font-size: 1.5em; color: #0A3D62; margin-right: 15px; margin-top: 5px;">✉️</div>
                    <div>
                        <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.2em;">Email</h3>
                        <p style="color: #666; line-height: 1.6; margin: 0;">
                            General: <a href="mailto:info@mobilemedical.la" style="color: #0A3D62; text-decoration: none;">info@mobilemedical.la</a><br>
                            Support: <a href="mailto:support@mobilemedical.la" style="color: #0A3D62; text-decoration: none;">support@mobilemedical.la</a>
                        </p>
                    </div>
                </div>

                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 25px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                    <div style="font-size: 1.5em; color: #0A3D62; margin-right: 15px; margin-top: 5px;">🕒</div>
                    <div>
                        <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.2em;">Hours</h3>
                        <p style="color: #666; line-height: 1.6; margin: 0;">
                            Monday - Friday: 8:00 AM - 6:00 PM<br>
                            Saturday: 9:00 AM - 4:00 PM<br>
                            Sunday: Emergency Only
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h2 style="color: #0A3D62; margin-bottom: 30px; font-size: 1.8em;">Send Us a Message</h2>
                
                <form id="contact-form" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);">
                    <div id="contact-messages"></div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label for="first_name" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">First Name *</label>
                            <input type="text" id="first_name" name="first_name" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease;">
                        </div>
                        <div class="form-group">
                            <label for="last_name" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Email Address *</label>
                        <input type="email" id="email" name="email" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease;">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="phone" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Phone Number</label>
                        <input type="tel" id="phone" name="phone" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" placeholder="123-456-7890" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease;">
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="subject" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Subject *</label>
                        <select id="subject" name="subject" required style="width: 100%; padding: 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease; height: 60px; background: white; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 5\"><path fill=\"%23666\" d=\"M2 0L0 2h4zm0 5L0 3h4z\"/></svg>'); background-repeat: no-repeat; background-position: right 12px center; background-size: 12px;">
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="support">Technical Support</option>
                            <option value="referral">Referral Question</option>
                            <option value="billing">Billing</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label for="message" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Message *</label>
                        <textarea id="message" name="message" required rows="5" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; transition: border-color 0.3s ease; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" id="contact-submit" style="width: 100%; background: linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%); color: white; padding: 15px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">Send Message</button>
                </form>
            </div>
        </div>

        <!-- Emergency Notice -->
        <div class="emergency-notice" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; border-radius: 15px; text-align: center; margin-top: 50px; max-width: 800px; margin-left: auto; margin-right: auto;">
            <h3 style="margin-bottom: 15px; font-size: 1.5em;">🚨 Emergency Notice</h3>
            <p style="margin: 0; font-size: 1.1em; line-height: 1.6;">For medical emergencies, please call 911 immediately. For urgent medical questions outside business hours, call our emergency line at <a href="tel:+1234567891" style="color: white; font-weight: bold;">(123) 456-7891</a>.</p>
        </div>
    </div>
</div>

<style>
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #0A3D62;
    outline: none;
    box-shadow: 0 0 0 3px rgba(10, 61, 98, 0.1);
}

#contact-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(10, 61, 98, 0.3);
}

.contact-success {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #c3e6cb;
}

.contact-error {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}

/* Phone number validation styling */
#phone:invalid {
    border-color: #dc3545;
}

#phone:valid {
    border-color: #28a745;
}

@media (max-width: 768px) {
    .contact-content {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
    
    .form-row {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 6) {
        value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6, 10);
    } else if (value.length >= 3) {
        value = value.substring(0, 3) + '-' + value.substring(3);
    }
    e.target.value = value;
});

// Form validation and submission
document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('contact-submit');
    const originalText = submitBtn.textContent;
    const messageDiv = document.getElementById('contact-messages');
    
    // Client-side validation
    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value.trim();
    
    // Validate required fields
    if (!firstName || !lastName || !email || !subject || !message) {
        messageDiv.innerHTML = '<div class="contact-error">Please fill in all required fields.</div>';
        return;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        messageDiv.innerHTML = '<div class="contact-error">Please enter a valid email address.</div>';
        return;
    }
    
    // Validate phone format if provided
    if (phone && !/^\d{3}-\d{3}-\d{4}$/.test(phone)) {
        messageDiv.innerHTML = '<div class="contact-error">Please enter phone number in format: 123-456-7890</div>';
        return;
    }
    
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'submit_contact_form');
    formData.append('nonce', '<?php echo wp_create_nonce('contact_form_nonce'); ?>');
    formData.append('first_name', firstName);
    formData.append('last_name', lastName);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('subject', subject);
    formData.append('message', message);
    
    // Submit form via AJAX
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // SUCCESS: Show message, reset form, KEEP button disabled
            messageDiv.innerHTML = '<div class="contact-success">' + data.data.message + '</div>';
            document.getElementById('contact-form').reset();
            
            // Option 1: Keep button disabled with success text
            submitBtn.textContent = 'Message Sent!';
            submitBtn.disabled = true;
            submitBtn.style.backgroundColor = '#28a745'; // Green color
            
            // Option 2: Re-enable button after a delay (e.g., 5 seconds)
            setTimeout(() => {
                submitBtn.textContent = 'Send Message';
                submitBtn.disabled = false;
                submitBtn.style.backgroundColor = ''; // Reset to original
            }, 5000);
            
        } else {
            // ERROR: Show error, re-enable button for retry
            messageDiv.innerHTML = '<div class="contact-error">' + (data.data || 'Failed to send message. Please try again.') + '</div>';
            submitBtn.textContent = 'Send Message';
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Contact form error:', error);
        messageDiv.innerHTML = '<div class="contact-error">Network error. Please try again.</div>';
        submitBtn.textContent = 'Send Message';
        submitBtn.disabled = false;
    });
});
</script>
</div><!-- #portal-page-main -->

<?php get_footer(); ?>





