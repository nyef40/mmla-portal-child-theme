// src/components/Dashboard.js
import React, { useEffect, useState } from 'react';

function Dashboard({ portalData }) {
    const [loading, setLoading] = useState(false);
    
    useEffect(() => {
        // Simulate API call
        setLoading(true);
        const timer = setTimeout(() => {
            setLoading(false);
        }, 500);
        
        return () => clearTimeout(timer);
    }, []);
    
    if (loading) {
        return (
            <div style={{ textAlign: 'center', padding: '40px' }}>
                <h2>Loading Dashboard...</h2>
                <div style={{
                    border: '4px solid #f3f3f3',
                    borderTop: '4px solid #0A3D62',
                    borderRadius: '50%',
                    width: '40px',
                    height: '40px',
                    animation: 'spin 1s linear infinite',
                    margin: '20px auto'
                }}></div>
            </div>
        );
    }
    
    return (
        <div style={{ padding: '20px' }}>
            <h1 style={{ color: '#0A3D62', marginBottom: '20px' }}>
                Welcome, {portalData?.user?.firstName || portalData?.user?.displayName || 'User'}!
            </h1>
            
            <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
                gap: '20px',
                marginTop: '30px'
            }}>
                <div style={{
                    background: 'white',
                    borderRadius: '10px',
                    padding: '20px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
                }}>
                    <h3 style={{ color: '#0A3D62', marginBottom: '15px' }}>Quick Actions</h3>
                    <ul style={{ listStyle: 'none', padding: 0 }}>
                        <li style={{ marginBottom: '10px' }}>
                            <a href="#profile" style={{ color: '#1e5f8b', textDecoration: 'none' }}>
                                Update Profile
                            </a>
                        </li>
                        <li style={{ marginBottom: '10px' }}>
                            <a href="#referrals" style={{ color: '#1e5f8b', textDecoration: 'none' }}>
                                Submit Referral
                            </a>
                        </li>
                        <li style={{ marginBottom: '10px' }}>
                            <a href="#resources" style={{ color: '#1e5f8b', textDecoration: 'none' }}>
                                View Resources
                            </a>
                        </li>
                        <li style={{ marginBottom: '10px' }}>
                            <a href="#contact" style={{ color: '#1e5f8b', textDecoration: 'none' }}>
                                Contact Support
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div style={{
                    background: 'white',
                    borderRadius: '10px',
                    padding: '20px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.1)'
                }}>
                    <h3 style={{ color: '#0A3D62', marginBottom: '15px' }}>Account Info</h3>
                    <div>
                        <p><strong>Name:</strong> {portalData?.user?.firstName} {portalData?.user?.lastName}</p>
                        <p><strong>Email:</strong> {portalData?.user?.email}</p>
                        <p><strong>Account Type:</strong> Healthcare Provider</p>
                    </div>
                </div>
            </div>
            
            <style>{`
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `}</style>
        </div>
    );
}

export default Dashboard;