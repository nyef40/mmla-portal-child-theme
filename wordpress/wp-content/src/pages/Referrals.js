"use client";
import { useState, useEffect } from 'react';
import api from '../utils/api';
import PortalNavigation from '../components/PortalNavigation';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';

const Referrals = () => {
    const [loading, setLoading] = useState(true);
    const [referrals, setReferrals] = useState([]);
    const [error, setError] = useState(null);
    const [filter, setFilter] = useState('all');
    const [referralStatus, setReferralStatus] = useState(null);
    const [formData, setFormData] = useState({
        patientName: '',
        patientEmail: '',
        reason: ''
    });

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setReferralStatus('submitting');
        try {
            const response = await api.submitReferral(formData);
            if (response.success) {
                setReferralStatus('success');
                setFormData({ patientName: '', patientEmail: '', reason: '' });
                const data = await api.getReferrals();
                if (data.success) setReferrals(data.data);
            } else {
                setReferralStatus('error');
                setError(response.message || 'Failed to submit referral');
            }
        } catch (err) {
            setReferralStatus('error');
            setError('Failed to submit referral: ' + err.message);
        }
    };

    useEffect(() => {
        const fetchReferrals = async () => {
            try {
                setLoading(true);
                const response = await api.getReferrals();
                if (response.success) {
                    setReferrals(response.data);
                    setError(null);
                } else {
                    setError(response.message || 'Failed to load referrals');
                }
            } catch (err) {
                setError('Failed to load referrals: ' + err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchReferrals();
    }, []);

    const filteredReferrals = referrals.filter((referral) => {
        if (filter === 'all') return true;
        if (filter === 'pending') return !referral.is_validated;
        if (filter === 'validated') return referral.is_validated;
        return true;
    });

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <div className="portal-referrals">
            <div className="portal-layout">
                <PortalNavigation activePage="referrals" />
                <div className="portal-content">
                    <div className="referrals-header">
                        <h1>My Referrals</h1>
                        <p>View and manage your submitted patient referrals.</p>
                        <p>Pending referrals: {referrals.filter(r => !r.is_validated).length}</p>
                    </div>
                    <div className="referrals-filters">
                        <button className={`filter-btn ${filter === 'all' ? 'active' : ''}`} onClick={() => setFilter('all')}>
                            All ({referrals.length})
                        </button>
                        <button className={`filter-btn ${filter === 'pending' ? 'active' : ''}`} onClick={() => setFilter('pending')}>
                            Pending ({referrals.filter(r => !r.is_validated).length})
                        </button>
                        <button className={`filter-btn ${filter === 'validated' ? 'active' : ''}`} onClick={() => setFilter('validated')}>
                            Validated ({referrals.filter(r => r.is_validated).length})
                        </button>
                    </div>
                    {filteredReferrals.length === 0 ? (
                        <div className="no-referrals">
                            <h3>No referrals found</h3>
                            <p>{filter === 'all' ? 'You haven\'t submitted any referrals yet.' : `No ${filter} referrals found.`}</p>
                        </div>
                    ) : (
                        <div className="referrals-list">
                            {filteredReferrals.map((referral) => (
                                <div key={referral.submission_id} className="referral-card">
                                    <div className="referral-header">
                                        <h3>Patient: {referral.patient_name}</h3>
                                        <span className={`status-badge ${referral.is_validated ? 'validated' : 'pending'}`}>
                                            {referral.is_validated ? 'Validated' : 'Pending'}
                                        </span>
                                    </div>
                                    <div className="referral-details">
                                        <div className="detail-row">
                                            <strong>Patient Email:</strong> {referral.patient_email}
                                        </div>
                                        <div className="detail-row">
                                            <strong>Reason:</strong> {referral.reason}
                                        </div>
                                        <div className="detail-row">
                                            <strong>Submitted:</strong> {formatDate(referral.created_at)}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                    <div className="referrals-content">
                        <h2>Submit a New Referral</h2>
                        {referralStatus === 'success' && <p className="success-message">Referral submitted successfully!</p>}
                        {referralStatus === 'error' && <p className="error-message">Failed to submit referral. Please try again.</p>}
                        <form onSubmit={handleSubmit}>
                            <div className="form-group">
                                <label htmlFor="patientName">Patient Name:</label>
                                <input
                                    type="text"
                                    id="patientName"
                                    name="patientName"
                                    value={formData.patientName}
                                    onChange={handleChange}
                                    required
                                />
                            </div>
                            <div className="form-group">
                                <label htmlFor="patientEmail">Patient Email:</label>
                                <input
                                    type="email"
                                    id="patientEmail"
                                    name="patientEmail"
                                    value={formData.patientEmail}
                                    onChange={handleChange}
                                    required
                                />
                            </div>
                            <div className="form-group">
                                <label htmlFor="reason">Reason:</label>
                                <input
                                    type="text"
                                    id="reason"
                                    name="reason"
                                    value={formData.reason}
                                    onChange={handleChange}
                                    required
                                />
                            </div>
                            <button type="submit" className="button button-primary">Submit Referral</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Referrals;

