import React, { useState } from 'react';
import { post } from '../utils/api';

function Register() {
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    confirmPassword: '',
    first_name: '',
    last_name: '',
    practice: '',
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    if (formData.password !== formData.confirmPassword) {
      setError('Passwords do not match');
      return;
    }

    if (formData.password.length < 8) {
      setError('Password must be at least 8 characters');
      return;
    }

    setLoading(true);

    try {
      const response = await post('portal_register', formData);
      console.log('[Portal]', response);

      if (response.success) {
        window.location.href = '/portal-login/?registered=1';
      } else {
        setError(response.data || 'Registration failed');
      }
    } catch (err) {
      setError('An error occurred. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="portal-page portal-register-page">
      <div className="portal-container">
        <div className="register-card">
          <div className="register-header">
            <h1>Create Account</h1>
            <p>Join the Mobile Medical LA Provider Network</p>
          </div>

          {error && <div className="alert alert-error">{error}</div>}

          <form onSubmit={handleSubmit}>
            <div className="form-row">
              <div className="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" value={formData.first_name} onChange={handleChange} required />
              </div>
              <div className="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" value={formData.last_name} onChange={handleChange} required />
              </div>
            </div>

            <div className="form-group">
              <label>Username *</label>
              <input type="text" name="username" value={formData.username} onChange={handleChange} required />
            </div>

            <div className="form-group">
              <label>Email *</label>
              <input type="email" name="email" value={formData.email} onChange={handleChange} required />
            </div>

            <div className="form-group">
              <label>Practice Name</label>
              <input type="text" name="practice" value={formData.practice} onChange={handleChange} />
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Password *</label>
                <input type="password" name="password" value={formData.password} onChange={handleChange} required />
              </div>
              <div className="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirmPassword" value={formData.confirmPassword} onChange={handleChange} required />
              </div>
            </div>

            <button type="submit" className="btn btn-primary btn-full" disabled={loading}>
              {loading ? 'Creating Account...' : 'Create Account'}
            </button>
          </form>

          <div className="register-footer">
            <p>
              Already have an account?
              <a href="/portal-login/">Sign in</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Register;
