import React, { useState } from 'react';
import { post } from '../utils/api';

function Contact() {
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setMessage({ type: '', text: '' });

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    console.log('[Portal]', data);

    try {
      const response = await post('submit_contact', data);
      if (response.success) {
        setMessage({ type: 'success', text: response.data.message });
        setSubmitted(true);
      } else {
        setMessage({ type: 'error', text: response.data || 'Failed to send' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'An error occurred' });
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="portal-page contact-page">
      <div className="portal-container">
        <h1 className="page-title">Contact Us</h1>
        <p className="page-subtitle">Have questions? We'd love to hear from you.</p>

        <div className="contact-grid">
          <div className="card contact-form-card">
            <h2>Send a Message</h2>

            {message.text && (
              <div className={`alert alert-${message.type}`}>{message.text}</div>
            )}

            {!submitted ? (
              <form onSubmit={handleSubmit}>
                <div className="form-group">
                  <label>Your Name *</label>
                  <input type="text" name="name" required />
                </div>
                <div className="form-group">
                  <label>Email *</label>
                  <input type="email" name="email" required />
                </div>
                <div className="form-group">
                  <label>Subject *</label>
                  <input type="text" name="subject" required />
                </div>
                <div className="form-group">
                  <label>Message *</label>
                  <textarea name="message" rows="5" required />
                </div>
                <button type="submit" className="btn btn-primary" disabled={submitting}>
                  {submitting ? 'Sending...' : 'Send Message'}
                </button>
              </form>
            ) : (
              <div className="success-state">
                <p>Thank you for your message!</p>
                <button className="btn" onClick={() => setSubmitted(false)}>
                  Send Another
                </button>
              </div>
            )}
          </div>

          <div className="contact-info">
            <div className="card info-card">
              <h3>Contact Information</h3>
              <div className="info-item">
                <strong>Phone</strong>
                <p>(310) 555-0100</p>
              </div>
              <div className="info-item">
                <strong>Email</strong>
                <p>info@mobilemedicalla.com</p>
              </div>
              <div className="info-item">
                <strong>Address</strong>
                <p>Los Angeles, CA</p>
              </div>
            </div>

            <div className="card info-card">
              <h3>Office Hours</h3>
              <p>
                <strong>Mon - Fri:</strong>
                {' '}
                9am - 5pm
              </p>
              <p>
                <strong>Sat:</strong>
                {' '}
                10am - 2pm
              </p>
              <p>
                <strong>Sun:</strong>
                {' '}
                Closed
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Contact;
