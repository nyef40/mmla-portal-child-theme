import React, { useState, useEffect } from 'react';
import { post } from '../utils/api';
import LoadingSpinner from './LoadingSpinner';

const Profile = () => {
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });

  console.log('[Portal]', profile);

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    try {
      const response = await post('get_user_profile');
      if (response.success) {
        setProfile(response.data);
      }
    } catch (err) {
      console.error('Failed to load profile:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setMessage({ type: '', text: '' });

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());

    try {
      const response = await post('update_user_profile', data);
      if (response.success) {
        setMessage({ type: 'success', text: 'Profile updated successfully!' });
      } else {
        setMessage({ type: 'error', text: response.data || 'Failed to update profile' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'An error occurred' });
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <LoadingSpinner message="Loading profile..." />;

  const personal = profile?.personal || {};
  const professional = profile?.professional || {};

  return (
    <div className="portal-page profile-page">
      <div className="portal-container">
        <h1 className="page-title">My Profile</h1>

        {message.text && (
          <div className={`alert alert-${message.type}`}>{message.text}</div>
        )}

        <form onSubmit={handleSubmit}>
          <div className="card">
            <h2>Personal Information</h2>
            <div className="form-row">
              <div className="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" defaultValue={personal.first_name} />
              </div>
              <div className="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" defaultValue={personal.last_name} />
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Email</label>
                <input type="email" value={personal.email} disabled />
              </div>
              <div className="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" defaultValue={personal.phone} />
              </div>
            </div>
          </div>

          <div className="card">
            <h2>Professional Information</h2>
            <div className="form-row">
              <div className="form-group">
                <label>Practice Name</label>
                <input type="text" name="practice" defaultValue={professional.practice} />
              </div>
              <div className="form-group">
                <label>Specialty</label>
                <input type="text" name="specialty" defaultValue={professional.specialty} />
              </div>
            </div>
            <div className="form-group">
              <label>License Number</label>
              <input type="text" name="license_number" defaultValue={professional.license_number} />
            </div>
            <div className="form-group">
              <label>Address</label>
              <input type="text" name="address" defaultValue={professional.address} />
            </div>
            <div className="form-row form-row-3">
              <div className="form-group">
                <label>City</label>
                <input type="text" name="city" defaultValue={professional.city} />
              </div>
              <div className="form-group">
                <label>State</label>
                <input type="text" name="state" defaultValue={professional.state} />
              </div>
              <div className="form-group">
                <label>ZIP</label>
                <input type="text" name="zip" defaultValue={professional.zip} />
              </div>
            </div>
          </div>

          <button type="submit" className="btn btn-primary" disabled={saving}>
            {saving ? 'Saving...' : 'Save Changes'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default Profile;