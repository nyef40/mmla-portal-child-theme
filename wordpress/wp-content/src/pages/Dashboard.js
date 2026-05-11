"use client";
import { useState, useEffect } from 'react';
import api from '../utils/api';
import PortalNavigation from '../components/PortalNavigation';
import LoadingSpinner from '../components/LoadingSpinner';
import ErrorMessage from '../components/ErrorMessage';

const Dashboard = () => {
    const [data, setData] = useState({ recentActivity: [], resources: [], referrals: [] });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                const response = await api.fetchDashboardData();
                if (response.success) {
                    setData({
                        recentActivity: response.data.recentActivity || [],
                        resources: response.data.resources || [],
                        referrals: response.data.referrals || []
                    });
                    setError(null);
                } else {
                    setError(response.message || 'Failed to load dashboard data');
                }
            } catch (err) {
                setError('Failed to load dashboard data: ' + err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    if (loading) return <LoadingSpinner />;
    if (error) return <ErrorMessage message={error} />;

    return (
        <div className="portal-dashboard">
            <div className="portal-layout">
                <PortalNavigation activePage="dashboard" />
                <div className="portal-content">
                    <h1>Welcome to Your Dashboard!</h1>
                    <p>This is your personalized portal dashboard. Here you can find quick links and an overview of your activities.</p>
                    <p>Logged in on: July 22, 2025, 5:24 pm</p>
                    <p>Last resource accessed: Understanding HIPAA Compliance</p>
                    <p>Pending referrals: {data.referrals.filter(r => !r.is_validated).length}</p>
                    <div className="dashboard-cards">
                        <div className="card">
                            <h3>Profile</h3>
                            <p>Manage your personal and professional details</p>
                            <a href="/portal-profile/" className="button button-primary">View Profile</a>
                        </div>
                        <div className="card">
                            <h3>Resources</h3>
                            <p>Access training materials and documents</p>
                            <a href="/portal-resources/" className="button button-primary">View Resources</a>
                        </div>
                        <div className="card">
                            <h3>Referrals</h3>
                            <p>Submit and track patient referrals</p>
                            <a href="/portal-referrals/" className="button button-primary">Manage Referrals</a>
                        </div>
                        <div className="card">
                            <h3>Support</h3>
                            <p>Contact our support team for assistance</p>
                            <a href="/contact/" className="button button-primary">Contact Support</a>
                        </div>
                        <div className="card">
                            <h3>Recent Activity</h3>
                            <ul>
                                {data.recentActivity.length === 0 ? (
                                    <li>No recent activity</li>
                                ) : (
                                    data.recentActivity.map(activity => (
                                        <li key={activity.id}>{activity.details} - {new Date(activity.accessed_at).toLocaleString()}</li>
                                    ))
                                )}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;

