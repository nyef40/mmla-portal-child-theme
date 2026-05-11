import React, { useState, useEffect } from 'react';
import { post } from '../utils/api';
import LoadingSpinner from './LoadingSpinner';

const Dashboard = () => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    try {
      const response = await post('get_dashboard_stats');
      if (response.success) {
        setData(response.data);
      }
    } catch (err) {
      console.error('Failed to load dashboard:', err);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <LoadingSpinner message="Loading dashboard..." />;

  const user = data?.user || {};
  const stats = data?.stats || {};

  console.log('[Portal]', data);

  return (
    <div className="portal-page dashboard-page">
      <div className="portal-container">
        <div className="dashboard-header">
          <h1>Welcome back, {user.firstName || 'Provider'}!</h1>
          <p className="subtitle">{user.practice || 'Mobile Medical LA Portal'}</p>
        </div>

        <div className="stats-grid">
          <div className="stat-card stat-referrals">
            <div className="stat-icon">📋</div>
            <div className="stat-content">
              <h3>{stats.referrals || 0}</h3>
              <p>Total Referrals</p>
            </div>
          </div>

          <div className="stat-card stat-resources">
            <div className="stat-icon">📚</div>
            <div className="stat-content">
              <h3>{stats.resources || 0}</h3>
              <p>Resources Available</p>
            </div>
          </div>

          <div className="stat-card stat-member">
            <div className="stat-icon">🏥</div>
            <div className="stat-content">
              <h3>Active</h3>
              <p>Member Status</p>
            </div>
          </div>
        </div>

        <div className="quick-actions">
          <h2>Quick Actions</h2>
          <div className="actions-grid">
            <a href="/portal-referrals/" className="action-card">
              <span className="action-icon">➕</span>
              <span>Submit Referral</span>
            </a>
            <a href="/portal-resources/" className="action-card">
              <span className="action-icon">📖</span>
              <span>View Resources</span>
            </a>
            <a href="/portal-profile/" className="action-card">
              <span className="action-icon">👤</span>
              <span>Update Profile</span>
            </a>
            <a href="/contact/" className="action-card">
              <span className="action-icon">✉️</span>
              <span>Contact Support</span>
            </a>
          </div>
        </div>

        {user.lastLogin && (
          <p className="last-login">
            Last login: {new Date(user.lastLogin).toLocaleString()}
          </p>
        )}
      </div>
    </div>
  );
};

export default Dashboard;