import React, { useState, useEffect } from 'react';

const Referrals = () => {
  const [referrals, setReferrals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetchReferrals();
  }, []);

  const fetchReferrals = async () => {
    try {
      const formData = new FormData();
      formData.append('action', 'get_referrals');

      const response = await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();
      if (data.success) {
        setReferrals(data.data || []);
      }
    } catch (err) {
      console.error('Failed to fetch referrals:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setMessage('');

    const formData = new FormData(e.target);
    formData.append('action', 'submit_referral');

    try {
      const response = await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();
      if (data.success) {
        setMessage('Referral submitted successfully!');
        setShowForm(false);
        e.target.reset();
        fetchReferrals();
      } else {
        setMessage(data.data || 'Failed to submit referral.');
      }
    } catch (err) {
      setMessage('An error occurred.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) return <div className="portal-loading">Loading referrals...</div>;

  return (
    <div className="portal-page referrals-page">
      <div className="portal-container">
        <div className="page-header">
          <h1>Referrals</h1>
          <button className="portal-btn" onClick={() => setShowForm(!showForm)}>
            {showForm ? 'Cancel' : 'New Referral'}
          </button>
        </div>
        
        {message && <div className="portal-message">{message}</div>}
        
        {showForm && (
          <div className="portal-card">
            <h2>Submit New Referral</h2>
            <form onSubmit={handleSubmit}>
              <div className="form-row">
                <div className="form-group">
                  <label>Provider Name *</label>
                  <input type="text" name="provider_name" required />
                </div>
                <div className="form-group">
                  <label>Practice Name</label>
                  <input type="text" name="provider_practice" />
                </div>
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label>Provider Email *</label>
                  <input type="email" name="provider_email" required />
                </div>
                <div className="form-group">
                  <label>Provider Phone</label>
                  <input type="tel" name="provider_phone" />
                </div>
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label>Patient Name *</label>
                  <input type="text" name="patient_name" required />
                </div>
                <div className="form-group">
                  <label>Patient Email</label>
                  <input type="email" name="patient_email" />
                </div>
              </div>
              <div className="form-group">
                <label>Reason for Referral *</label>
                <input type="text" name="reason" required />
              </div>
              <div className="form-group">
                <label>Additional Notes</label>
                <textarea name="notes" rows="3"></textarea>
              </div>
              <button type="submit" className="portal-btn" disabled={submitting}>
                {submitting ? 'Submitting...' : 'Submit Referral'}
              </button>
            </form>
          </div>
        )}
        
        <div className="portal-card">
          <h2>Recent Referrals</h2>
          {referrals.length === 0 ? (
            <p>No referrals yet.</p>
          ) : (
            <table className="portal-table">
              <thead>
                <tr>
                  <th>Patient</th>
                  <th>Provider</th>
                  <th>Reason</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {referrals.map(ref => (
                  <tr key={ref.submission_id}>
                    <td>{ref.patient_name}</td>
                    <td>{ref.provider_name}</td>
                    <td>{ref.reason}</td>
                    <td>{new Date(ref.created_at).toLocaleDateString()}</td>
                    <td>
                      <span className={`status ${ref.is_validated ? 'validated' : 'pending'}`}>
                        {ref.is_validated ? 'Validated' : 'Pending'}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>
    </div>
  );
};

export default Referrals;