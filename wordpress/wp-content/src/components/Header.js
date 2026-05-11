// src/components/Header.js
import React from 'react';
import { Link, useLocation } from 'react-router-dom';

function Header({ user, isLoggedIn }) {
    const location = useLocation();
    const currentPath = location.pathname.replace('/portal/', '').replace('/', '') || 'dashboard';
    
    return (
        <div className="portal-header" style={{
            background: 'linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%)',
            padding: '15px 0',
            boxShadow: '0 4px 20px rgba(10, 61, 98, 0.3)',
            position: 'sticky',
            top: 0,
            zIndex: 9999
        }}>
            <div className="portal-header-content" style={{
                maxWidth: '1200px',
                margin: '0 auto',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                padding: '0 20px'
            }}>
                <Link to="/" className="portal-logo" style={{
                    color: 'white',
                    fontSize: '20px',
                    fontWeight: '700',
                    textDecoration: 'none',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px'
                }}>
                    🏥 Mobile Medical LA Portal
                </Link>
                
                <div className="portal-nav-menu" style={{
                    display: 'flex',
                    gap: '15px',
                    alignItems: 'center'
                }}>
                    <Link 
                        to="/" 
                        className={`portal-nav-item ${currentPath === '' ? 'active' : ''}`}
                        style={{
                            color: 'rgba(255, 255, 255, 0.8)',
                            textDecoration: 'none',
                            padding: '8px 16px',
                            borderRadius: '20px',
                            transition: 'all 0.3s ease',
                            fontWeight: '500',
                            fontSize: '14px',
                            ...(currentPath === '' ? {
                                background: 'rgba(255, 255, 255, 0.2)',
                                color: 'white'
                            } : {})
                        }}
                    >
                        Home
                    </Link>
                    
                    {isLoggedIn ? (
                        <>
                            <Link 
                                to="/dashboard" 
                                className={`portal-nav-item ${currentPath === 'dashboard' ? 'active' : ''}`}
                                style={{
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px',
                                    ...(currentPath === 'dashboard' ? {
                                        background: 'rgba(255, 255, 255, 0.2)',
                                        color: 'white'
                                    } : {})
                                }}
                            >
                                Dashboard
                            </Link>
                            <Link 
                                to="/profile" 
                                className={`portal-nav-item ${currentPath === 'profile' ? 'active' : ''}`}
                                style={{
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px',
                                    ...(currentPath === 'profile' ? {
                                        background: 'rgba(255, 255, 255, 0.2)',
                                        color: 'white'
                                    } : {})
                                }}
                            >
                                Profile
                            </Link>
                            <Link 
                                to="/resources" 
                                className={`portal-nav-item ${currentPath === 'resources' ? 'active' : ''}`}
                                style={{
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px',
                                    ...(currentPath === 'resources' ? {
                                        background: 'rgba(255, 255, 255, 0.2)',
                                        color: 'white'
                                    } : {})
                                }}
                            >
                                Resources
                            </Link>
                            <Link 
                                to="/referrals" 
                                className={`portal-nav-item ${currentPath === 'referrals' ? 'active' : ''}`}
                                style={{
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px',
                                    ...(currentPath === 'referrals' ? {
                                        background: 'rgba(255, 255, 255, 0.2)',
                                        color: 'white'
                                    } : {})
                                }}
                            >
                                Referrals
                            </Link>
                            <Link 
                                to="/contact" 
                                className={`portal-nav-item ${currentPath === 'contact' ? 'active' : ''}`}
                                style={{
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px',
                                    ...(currentPath === 'contact' ? {
                                        background: 'rgba(255, 255, 255, 0.2)',
                                        color: 'white'
                                    } : {})
                                }}
                            >
                                Contact
                            </Link>
                            <a 
                                href="/wp-logout.php?redirect_to=/portal/" 
                                className="portal-nav-item logout-btn"
                                style={{
                                    background: 'rgba(220, 53, 69, 0.8)',
                                    color: 'white',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px'
                                }}
                            >
                                Logout
                            </a>
                        </>
                    ) : (
                        <>
                            <Link 
                                to="/login" 
                                className={`portal-nav-item ${currentPath === 'login' ? 'active' : ''}`}
                                style={{
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px',
                                    ...(currentPath === 'login' ? {
                                        background: 'rgba(255, 255, 255, 0.2)',
                                        color: 'white'
                                    } : {})
                                }}
                            >
                                Login
                            </Link>
                            <Link 
                                to="/register" 
                                className={`portal-nav-item ${currentPath === 'register' ? 'active' : ''}`}
                                style={{
                                    color: 'rgba(255, 255, 255, 0.8)',
                                    textDecoration: 'none',
                                    padding: '8px 16px',
                                    borderRadius: '20px',
                                    transition: 'all 0.3s ease',
                                    fontWeight: '500',
                                    fontSize: '14px',
                                    ...(currentPath === 'register' ? {
                                        background: 'rgba(255, 255, 255, 0.2)',
                                        color: 'white'
                                    } : {})
                                }}
                            >
                                Register
                            </Link>
                        </>
                    )}
                </div>
                
                <a href="/" className="portal-back-btn" style={{
                    background: 'rgba(255, 255, 255, 0.2)',
                    color: 'white',
                    padding: '10px 20px',
                    borderRadius: '20px',
                    textDecoration: 'none',
                    fontWeight: '600',
                    transition: 'all 0.3s ease',
                    backdropFilter: 'blur(10px)',
                    border: '1px solid rgba(255, 255, 255, 0.3)',
                    fontSize: '14px'
                }}>
                    ← Main Site
                </a>
            </div>
        </div>
    );
}

export default Header;