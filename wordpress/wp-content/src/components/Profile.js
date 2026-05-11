import React, { useState, useEffect } from 'react';

const Profile = () => {
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetchProfile();
  }, []);

  const fetchProfile = async () => {
    try {
      const formData = new FormData();
      formData.append('action', 'get_user_profile');

      const response = await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();
      if (data.success) {
        setProfile(data.data);
      }
    } catch (err) {
      console.error('Failed to fetch profile:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    const formData = new FormData(e.target);
    formData.append('action', 'update_user_profile');

    try {
      const response = await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();
      if (data.success) {
        setMessage('Profile updated successfully!');
      } else {
        setMessage('Failed to update profile.');
      }
    } catch (err) {
      setMessage('An error occurred.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div className="portal-loading">Loading profile...</div>;

  return (
    <div className="portal-page profile-page">
      <div className="portal-container">
        <h1>My Profile</h1>
        
        {message && <div className="portal-message">{message}</div>}
        
        <form onSubmit={handleSave}>
          <div className="portal-card">
            <h2>Personal Information</h2>
            <div className="form-row">
              <div className="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" defaultValue={profile?.personal?.first_name || ''} />
              </div>
              <div className="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" defaultValue={profile?.personal?.last_name || ''} />
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Email</label>
                <input type="email" value={profile?.personal?.email || ''} disabled />
              </div>
              <div className="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" defaultValue={profile?.personal?.phone || ''} />
              </div>
            </div>
          </div>
          
          <div className="portal-card">
            <h2>Professional Information</h2>
            <div className="form-row">
              <div className="form-group">
                <label>Practice Name</label>
                <input type="text" name="practice_name" defaultValue={profile?.professional?.practice_name || ''} />
              </div>
              <div className="form-group">
                <label>Specialty</label>
                <input type="text" name="specialty" defaultValue={profile?.professional?.specialty || ''} />
              </div>
            </div>
            <div className="form-group">
              <label>Address</label>
              <input type="text" name="address" defaultValue={profile?.professional?.address || ''} />
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>City</label>
                <input type="text" name="city" defaultValue={profile?.professional?.city || ''} />
              </div>
              <div className="form-group">
                <label>State</label>
                <input type="text" name="state" defaultValue={profile?.professional?.state || ''} />
              </div>
              <div className="form-group">
                <label>ZIP</label>
                <input type="text" name="zip" defaultValue={profile?.professional?.zip || ''} />
              </div>
            </div>
          </div>
          
          <button type="submit" className="portal-btn" disabled={saving}>
            {saving ? 'Saving...' : 'Save Changes'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default Profile;