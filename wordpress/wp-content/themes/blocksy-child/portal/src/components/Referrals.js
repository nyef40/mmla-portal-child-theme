import React, { useState, useEffect } from 'react';
import { post } from '../utils/api';
import LoadingSpinner from './LoadingSpinner';

function Referrals() {
  const [referrals, setReferrals] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });

  useEffect(() => {
    loadReferrals();
  }, []);

  const loadReferrals = async () => {
    try {
      const response = await post('get_referrals');
      if (response.success) {
        setReferrals(response.data || []);
      }
    } catch (err) {
      console.error('Failed to load referrals:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setMessage({ type: '', text: '' });

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
      const response = await post('submit_referral', data);
      console.log('[Portal]', data);
      if (response.success) {
        setMessage({ type: 'success', text: 'Referral submitted successfully!' });
        setShowForm(false);
        e.target.reset();
        loadReferrals();
      } else {
        setMessage({ type: 'error', text: response.data || 'Failed to submit' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'An error occurred' });
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) return <LoadingSpinner message="Loading referrals..." />;

  return (
    <div className="portal-page referrals-page">
      <div className="portal-container">
        <div className="page-header-row">
          <div>
            <h1 className="page-title">Referrals</h1>
            <p className="page-subtitle">Submit and track patient referrals</p>
          </div>
          <button className="btn btn-primary" onClick={() => setShowForm(!showForm)}>
            {showForm ? 'Cancel' : '+ New Referral'}
          </button>
        </div>

        {message.text && (
          <div className={`alert alert-${message.type}`}>{message.text}</div>
        )}

        {showForm && (
          <div className="card referral-form-card">
            <h2>Submit New Referral</h2>
            <form onSubmit={handleSubmit}>
              <div className="form-section">
                <h3>Provider Information</h3>
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
              </div>

              <div className="form-section">
                <h3>Patient Information</h3>
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
              </div>

              <div className="form-section">
                <h3>Referral Details</h3>
                <div className="form-group">
                  <label>Reason for Referral *</label>
                  <input type="text" name="reason" required />
                </div>
                <div className="form-group">
                  <label>Additional Notes</label>
                  <textarea name="notes" rows="3" />
                </div>
              </div>

              <button type="submit" className="btn btn-primary" disabled={submitting}>
                {submitting ? 'Submitting...' : 'Submit Referral'}
              </button>
            </form>
          </div>
        )}

        <div className="card">
          <h2>Recent Referrals</h2>
          {referrals.length === 0 ? (
            <p className="empty-text">No referrals submitted yet.</p>
          ) : (
            <div className="table-container">
              <table className="data-table">
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
                  {referrals.map((ref) => (
                    <tr key={ref.submission_id}>
                      <td>{ref.patient_name}</td>
                      <td>{ref.provider_name}</td>
                      <td>{ref.reason}</td>
                      <td>{new Date(ref.created_at).toLocaleDateString()}</td>
                      <td>
                        <span className={`status-badge ${ref.is_validated ? 'validated' : 'pending'}`}>
                          {ref.is_validated ? 'Validated' : 'Pending'}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default Referrals;
