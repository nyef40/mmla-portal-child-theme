import React, { useState } from 'react';

const Contact = () => {
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState('');
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setMessage('');

    const formData = new FormData(e.target);
    formData.append('action', 'submit_contact_form');
    formData.append('nonce', window.wpPortalData?.nonce || '');

    try {
      const response = await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();
      if (data.success) {
        setMessage(data.data.message);
        setSubmitted(true);
        e.target.reset();
      } else {
        setMessage(data.data || 'Failed to send message.');
      }
    } catch (err) {
      setMessage('An error occurred. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="portal-page contact-page">
      <div className="portal-container">
        <h1>Contact Us</h1>
        <p>Have questions? We'd love to hear from you.</p>
        
        <div className="contact-grid">
          <div className="portal-card">
            <h2>Send a Message</h2>
            
            {message && (
              <div className={`portal-message ${submitted ? 'success' : 'error'}`}>
                {message}
              </div>
            )}
            
            {!submitted ? (
              <form onSubmit={handleSubmit}>
                <div className="form-row">
                  <div className="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" required />
                  </div>
                  <div className="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" required />
                  </div>
                </div>
                <div className="form-row">
                  <div className="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required />
                  </div>
                  <div className="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" />
                  </div>
                </div>
                <div className="form-group">
                  <label>Subject *</label>
                  <input type="text" name="subject" required />
                </div>
                <div className="form-group">
                  <label>Message *</label>
                  <textarea name="message" rows="5" required></textarea>
                </div>
                <button type="submit" className="portal-btn" disabled={submitting}>
                  {submitting ? 'Sending...' : 'Send Message'}
                </button>
              </form>
            ) : (
              <button className="portal-btn" onClick={() => setSubmitted(false)}>
                Send Another Message
              </button>
            )}
          </div>
          
          <div className="contact-info">
            <div className="portal-card">
              <h3>Contact Information</h3>
              <p><strong>Phone:</strong> (123) 456-7890</p>
              <p><strong>Email:</strong> info@mobilemedicalla.com</p>
              <p><strong>Address:</strong><br />
                123 Healthcare Drive<br />
                Los Angeles, CA 90001
              </p>
            </div>
            
            <div className="portal-card">
              <h3>Office Hours</h3>
              <p>Monday - Friday: 9am - 5pm</p>
              <p>Saturday: 10am - 2pm</p>
              <p>Sunday: Closed</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Contact;