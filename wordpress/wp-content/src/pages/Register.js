"use client";
import { useState, useEffect } from 'react';
import { api } from '../utils/api';

function Register() {
    const [formData, setFormData] = useState({
        first_name: '', last_name: '', email: '', phone: '', username: '', password: '', role: '',
        specialty: '', license_number: '', practice: '', address: '', city: '', state: '', zip: ''
    });
    const [passwordStrength, setPasswordStrength] = useState('Weak');
    const [message, setMessage] = useState(null);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData({ ...formData, [name]: value });

        if (name === 'password') {
            const hasLetter = /[a-zA-Z]/.test(value);
            const hasNumber = /\d/.test(value);
            const isLongEnough = value.length >= 8;
            setPasswordStrength(
                hasLetter && hasNumber && isLongEnough ? 'Strong' :
                (hasLetter || hasNumber) && isLongEnough ? 'Medium' : 'Weak'
            );
        }
    };

    const handlePhoneInput = (e) => {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        setFormData({ ...formData, phone: value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const response = await fetch(window.portalSettings.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'portal_register',
                    nonce: window.portalSettings.nonce,
                    ...formData
                })
            });
            const result = await response.json();
            if (result.success) {
                window.location.href = '/register/?registration=success';
            } else {
                setMessage({ type: 'error', text: result.data.message });
            }
        } catch (error) {
            setMessage({ type: 'error', text: 'Registration failed: ' + error.message });
        }
    };

    return (
        <div className="portal-container">
            <div className="portal-auth-form">
                <h1>Create Your Portal Account</h1>
                <p className="form-subtitle">Join the Mobile Medical LA Portal for healthcare professionals</p>
                {message && (
                    <div className={message.type === 'error' ? 'error-message' : 'success-message'}>
                        <p>{message.text}</p>
                    </div>
                )}
                <form onSubmit={handleSubmit} className="portal-registration-form">
                    <input type="hidden" name="nonce" value={window.portalSettings.nonce} />
                    <div className="form-section">
                        <h3>Personal Information</h3>
                        <div className="form-row">
                            <div className="form-group half">
                                <label htmlFor="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" value={formData.first_name} onChange={handleChange} required />
                            </div>
                            <div className="form-group half">
                                <label htmlFor="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" value={formData.last_name} onChange={handleChange} required />
                            </div>
                        </div>
                        <div className="form-group">
                            <label htmlFor="email">Email Address *</label>
                            <input type="email" id="email" name="email" value={formData.email} onChange={handleChange} required />
                            <small>We'll send a verification email to this address</small>
                        </div>
                        <div className="form-group">
                            <label htmlFor="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value={formData.phone} onChange={handlePhoneInput} placeholder="(555) 123-4567" />
                        </div>
                    </div>
                    <div className="form-section">
                        <h3>Account Credentials</h3>
                        <div className="form-group">
                            <label htmlFor="username">Username *</label>
                            <input type="text" id="username" name="username" value={formData.username} onChange={handleChange} required />
                            <small>Choose a unique username for your portal account</small>
                        </div>
                        <div className="form-group">
                            <label htmlFor="password">Password *</label>
                            <input type="password" id="password" name="password" value={formData.password} onChange={handleChange} required minLength="8" />
                            <small>Minimum 8 characters, include letters and numbers</small>
                            <small id="password-strength" style={{ color: passwordStrength === 'Strong' ? '#28a745' : passwordStrength === 'Medium' ? '#ffc107' : '#dc3545' }}>
                                Password strength: {passwordStrength}
                            </small>
                        </div>
                    </div>
                    <div className="form-section">
                        <h3>Professional & Location Information</h3>
                        <div className="form-group">
                            <label htmlFor="role">Your Role *</label>
                            <select id="role" name="role" value={formData.role} onChange={handleChange} required>
                                <option value="">Select Your Role</option>
                                <option value="RN">Registered Nurse (RN)</option>
                                <option value="LVN">Licensed Vocational Nurse (LVN)</option>
                                <option value="PT">Physical Therapist (PT)</option>
                                <option value="OT">Occupational Therapist (OT)</option>
                                <option value="ST">Speech Therapist (ST)</option>
                                <option value="MSW">Medical Social Worker (MSW)</option>
                                <option value="CNA">Certified Nursing Assistant (CNA)</option>
                                <option value="HHA">Home Health Aide (HHA)</option>
                                <option value="Admin">Administrative Staff</option>
                                <option value="Other">Other Healthcare Professional</option>
                            </select>
                        </div>
                        <div className="form-row">
                            <div className="form-group half">
                                <label htmlFor="specialty">Specialty/Department</label>
                                <input type="text" id="specialty" name="specialty" value={formData.specialty} onChange={handleChange} placeholder="e.g., Home Health, Wound Care" />
                            </div>
                            <div className="form-group half">
                                <label htmlFor="license_number">License Number</label>
                                <input type="text" id="license_number" name="license_number" value={formData.license_number} onChange={handleChange} placeholder="Professional license number" />
                            </div>
                        </div>
                        <div className="form-group">
                            <label htmlFor="practice">Organization/Practice Name</label>
                            <input type="text" id="practice" name="practice" value={formData.practice} onChange={handleChange} placeholder="Mobile Medical LA or partner organization" />
                        </div>
                        <div className="form-group">
                            <label htmlFor="address">Street Address</label>
                            <input type="text" id="address" name="address" value={formData.address} onChange={handleChange} placeholder="123 Main St" />
                        </div>
                        <div className="form-row">
                            <div className="form-group third">
                                <label htmlFor="city">City</label>
                                <input type="text" id="city" name="city" value={formData.city} onChange={handleChange} />
                            </div>
                            <div className="form-group third">
                                <label htmlFor="state">State</label>
                                <input type="text" id="state" name="state" value={formData.state} onChange={handleChange} maxLength="2" placeholder="CA" />
                            </div>
                            <div className="form-group third">
                                <label htmlFor="zip">Zip Code</label>
                                <input type="text" id="zip" name="zip" value={formData.zip} onChange={handleChange} maxLength="10" />
                            </div>
                        </div>
                    </div>
                    <div className="form-actions">
                        <button type="submit" className="button button-primary">Create Portal Account</button>
                    </div>
                </form>
                <p className="auth-links">
                    Already have an account? <a href="/portal-login/">Sign In</a>
                </p>
            </div>
        </div>
    );
}

export default Register;
