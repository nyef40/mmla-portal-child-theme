import React, { useState, useEffect } from 'react';
import { post } from '../utils/api';

function Login() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);
  const [showResend, setShowResend] = useState(false);
  const [resendEmail, setResendEmail] = useState('');
  const [resending, setResending] = useState(false);

  useEffect(() => {
    // Check URL params for verification status
    const params = new URLSearchParams(window.location.search);

    if (params.get('verified') === '1') {
      setSuccess('Email verified successfully! You can now log in.');
    }
    if (params.get('registered') === '1') {
      setSuccess('Registration successful! Please check your email to verify your account.');
    }
    if (params.get('message') === 'already_verified') {
      setSuccess('Your email is already verified. Please log in.');
    }
    if (params.get('error') === 'invalid_token') {
      setError('Invalid verification link. Please request a new one.');
      setShowResend(true);
    }
    if (params.get('error') === 'token_expired') {
      setError('Verification link has expired. Please request a new one.');
      setShowResend(true);
    }
    if (params.get('error') === 'invalid_link') {
      setError('Invalid verification link.');
    }
    if (params.get('resend')) {
      setResendEmail(params.get('resend'));
      setShowResend(true);
    }
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const response = await post('portal_login', {
        username,
        password,
        remember: remember ? '1' : '0',
      });

      if (response.success && response.data) {
        window.location.href = response.data.redirect || '/dashboard/';
        return;
      }
      setError(response.data || 'Login failed');
      if (response.data && typeof response.data === 'string' && response.data.includes('verify')) {
        setShowResend(true);
        setResendEmail(username);
      }
    } catch (err) {
      const message = err.message || (err.data && (typeof err.data === 'string' ? err.data : err.data.message)) || 'An error occurred. Please try again.';
      setError(message);
      if (typeof message === 'string' && message.includes('verify')) {
        setShowResend(true);
        setResendEmail(username);
      }
    } finally {
      setLoading(false);
    }
  };

  const handleResendVerification = async (e) => {
    e.preventDefault();
    setResending(true);
    setError('');
    setSuccess('');

    try {
      const response = await post('resend_verification_email', {
        email: resendEmail || username,
      });

      if (response.success) {
        setSuccess(response.data.message);
        setShowResend(false);
      } else {
        setError(response.data || 'Failed to resend verification email');
      }
    } catch (err) {
      setError('An error occurred. Please try again.');
    } finally {
      setResending(false);
    }
  };

  return (
    <div className="portal-page portal-login-page">
      <div className="portal-container">
        <div className="login-card">
          <div className="login-header">
            <h1>Welcome Back</h1>
            <p>Sign in to access your provider portal</p>
          </div>

          {error && <div className="alert alert-error">{error}</div>}
          {success && <div className="alert alert-success">{success}</div>}

          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label>Username or Email</label>
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Enter your username"
                required
                autoComplete="username"
              />
            </div>

            <div className="form-group">
              <label>Password</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Enter your password"
                required
                autoComplete="current-password"
              />
            </div>

            <div className="form-group checkbox-group">
              <label className="checkbox-label">
                <input
                  type="checkbox"
                  checked={remember}
                  onChange={(e) => setRemember(e.target.checked)}
                />
                <span>Remember me</span>
              </label>
            </div>

            <button type="submit" className="btn btn-primary btn-full" disabled={loading}>
              {loading ? 'Signing in...' : 'Sign In'}
            </button>
          </form>

          {showResend && (
            <div className="resend-verification">
              <p>Need to verify your email?</p>
              <form onSubmit={handleResendVerification}>
                <div className="form-group">
                  <input
                    type="email"
                    value={resendEmail}
                    onChange={(e) => setResendEmail(e.target.value)}
                    placeholder="Enter your email"
                    required
                  />
                </div>
                <button type="submit" className="btn btn-secondary btn-full" disabled={resending}>
                  {resending ? 'Sending...' : 'Resend Verification Email'}
                </button>
              </form>
            </div>
          )}

          <div className="login-footer">
            <p>
              Don't have an account?
              <a href="/register/">Create one</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Login;
