"use client"

import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import { getCurrentUser } from '../utils/auth';
import PortalNavigation from '../components/PortalNavigation';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';

const Profile = () => {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [profile, setProfile] = useState({});
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState('');
  const user = getCurrentUser();

  useEffect(() => {
    const fetchProfile = async () => {
      try {
        setLoading(true);
        const data = await api.getUserProfile();
        setProfile({
          firstName: user.firstName || '',
          lastName: user.lastName || '',
          phone: data.phone || '',
          practice: data.practice || '',
          specialty: data.specialty || '',
          address: data.address || '',
          city: data.city || '',
          state: data.state || '',
          zip: data.zip || '',
        });
        setError(null);
      } catch (err) {
        setError('Failed to load profile: ' + err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchProfile();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setProfile(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      await api.updateUserProfile(profile);
      setSuccessMessage('Profile updated successfully!');
      setError(null);
      
      // Clear success message after 3 seconds
      setTimeout(() => {
        setSuccessMessage('');
      }, 3000);
    } catch (err) {
      setError('Failed to update profile: ' + err.message);
      setSuccessMessage('');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <LoadingSpinner />;
  if (error && !profile) return <ErrorMessage message={error} />;

  return (
    <div className="portal-profile">
      <div className="portal-layout">
        <PortalNavigation activePage="profile" />
        
        <div className="portal-content">
          <h1>My Profile</h1>
          
          {error && <div className="error-message">{error}</div>}
          {successMessage && <div className="success-message">{successMessage}</div>}
          
          <form onSubmit={handleSubmit} className="profile-form">
            <div className="form-section">
              <h2>Personal Information</h2>
              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="firstName">First Name</label>
                  <input
                    type="text"
                    id="firstName"
                    name="firstName"
                    value={profile.firstName || ''}
                    onChange={handleChange}
                  />
                </div>
                <div className="form-group">
                  <label htmlFor="lastName">Last Name</label>
                  <input
                    type="text"
                    id="lastName"
                    name="lastName"
                    value={profile.lastName || ''}
                    onChange={handleChange}
                  />
                </div>
              </div>
              <div className="form-group">
                <label htmlFor="phone">Phone</label>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  value={profile.phone || ''}
                  onChange={handleChange}
                />
              </div>
            </div>
            
            <div className="form-section">
              <h2>Professional Information</h2>
              <div className="form-group">
                <label htmlFor="practice">Practice Name</label>
                <input
                  type="text"
                  id="practice"
                  name="practice"
                  value={profile.practice || ''}
                  onChange={handleChange}
                />
              </div>
              <div className="form-group">
                <label htmlFor="specialty">Specialty</label>
                <input
                  type="text"
                  id="specialty"
                  name="specialty"
                  value={profile.specialty || ''}
                  onChange={handleChange}
                />
              </div>
            </div>
            
            <div className="form-section">
              <h2>Address</h2>
              <div className="form-group">
                <label htmlFor="address">Street Address</label>
                <input
                  type="text"
                  id="address"
                  name="address"
                  value={profile.address || ''}
                  onChange={handleChange}
                />
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label htmlFor="city">City</label>
                  <input
                    type="text"
                    id="city"
                    name="city"
                    value={profile.city || ''}
                    onChange={handleChange}
                  />
                </div>
                <div className="form-group">
                  <label htmlFor="state">State</label>
                  <input
                    type="text"
                    id="state"
                    name="state"
                    value={profile.state || ''}
                    onChange={handleChange}
                  />
                </div>
                <div className="form-group">
                  <label htmlFor="zip">ZIP Code</label>
                  <input
                    type="text"
                    id="zip"
                    name="zip"
                    value={profile.zip || ''}
                    onChange={handleChange}
                  />
                </div>
              </div>
            </div>
            
            <div className="form-actions">
              <button type="submit" className="button button-primary" disabled={saving}>
                {saving ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default Profile;
