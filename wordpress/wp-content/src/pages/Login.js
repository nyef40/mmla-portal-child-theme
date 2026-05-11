"use client";
import { useState } from 'react';
import { login } from '../utils/auth';

function Login() {
    const [formData, setFormData] = useState({ username: '', password: '' });
    const [message, setMessage] = useState(null);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const result = await login(formData.username, formData.password);
        if (!result.success) {
            setMessage({ type: 'error', text: result.message });
        }
    };

    return (
        <div className="portal-container">
            <div className="portal-auth-form">
                <h1>Sign In to Portal</h1>
                <p className="form-subtitle">Access the Mobile Medical LA Portal</p>
                {message && (
                    <div className={message.type === 'error' ? 'error-message' : 'success-message'}>
                        <p>{message.text}</p>
                    </div>
                )}
                <form onSubmit={handleSubmit} className="portal-login-form">
                    <input type="hidden" name="nonce" value={window.portalSettings.nonce} />
                    <div className="form-group">
                        <label htmlFor="username">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value={formData.username}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            value={formData.password}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div className="form-actions">
                        <button type="submit" className="button button-primary">Sign In</button>
                    </div>
                </form>
                <p className="auth-links">
                    Need an account? <a href="/register/">Register</a>
                </p>
            </div>
        </div>
    );
}

export default Login;
