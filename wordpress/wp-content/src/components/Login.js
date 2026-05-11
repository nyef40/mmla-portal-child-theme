import React, { useState } from 'react';

const Login = () => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    const formData = new FormData();
    formData.append('action', 'portal_login');
    formData.append('nonce', window.wpPortalData?.nonce || '');
    formData.append('username', username);
    formData.append('password', password);
    formData.append('remember', remember ? '1' : '0');

    try {
      const response = await fetch(window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const data = await response.json();

      if (data.success) {
        window.location.href = data.data.redirect || '/dashboard/';
      } else {
        setError(data.data || 'Login failed. Please try again.');
      }
    } catch (err) {
      setError('An error occurred. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="portal-page login-page">
      <div className="portal-container">
        <div className="portal-card">
          <h1>Login to Portal</h1>
          <p>Welcome back! Please sign in to access your account.</p>
          
          {error && <div className="portal-error">{error}</div>}
          
          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label htmlFor="username">Username or Email</label>
              <input
                type="text"
                id="username"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
              />
            </div>
            
            <div className="form-group">
              <label htmlFor="password">Password</label>
              <input
                type="password"
                id="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>
            
            <div className="form-group checkbox">
              <label>
                <input
                  type="checkbox"
                  checked={remember}
                  onChange={(e) => setRemember(e.target.checked)}
                />
                Remember me
              </label>
            </div>
            
            <button type="submit" className="portal-btn" disabled={loading}>
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>
          
          <p className="portal-link">
            Don't have an account? <a href="/portal/register">Register here</a>
          </p>
        </div>
      </div>
    </div>
  );
};

export default Login;