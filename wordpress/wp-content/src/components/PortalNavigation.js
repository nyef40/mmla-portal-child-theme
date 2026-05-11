"use client";
import { useState, useEffect } from 'react';
import { isAuthenticated, logout } from '../utils/auth';

function PortalNavigation({ activePage }) {
    const [loggedIn, setLoggedIn] = useState(false);

    useEffect(() => {
        setLoggedIn(isAuthenticated());
    }, []);

    const menuItems = [
        { label: 'Home', href: '/portal/', page: 'portal', show: !loggedIn },
        { label: 'Login', href: '/portal-login/', page: 'login', show: !loggedIn },
        { label: 'Register', href: '/register/', page: 'register', show: !loggedIn },
        { label: 'Contact', href: '/contact/', page: 'contact', show: true },
        { label: 'Dashboard', href: '/dashboard/', page: 'dashboard', show: loggedIn },
        { label: 'Profile', href: '/portal-profile/', page: 'profile', show: loggedIn },
        { label: 'Resources', href: '/portal-resources/', page: 'resources', show: loggedIn },
        { label: 'Referrals', href: '/portal-referrals/', page: 'referrals', show: loggedIn },
        { label: 'Logout', href: '#', page: 'logout', show: loggedIn, onClick: logout }
    ];

    return (
        <nav className="portal-nav">
            <div className="portal-nav-container">
                <a href="/" className="main-site-button">Main Site</a>
                <ul>
                    {menuItems
                        .filter(item => item.show)
                        .map(item => (
                            <li key={item.page}>
                                {item.onClick ? (
                                    <button 
                                        onClick={item.onClick} 
                                        className={`logout-button ${activePage === item.page ? 'active' : ''}`}
                                    >
                                        {item.label}
                                    </button>
                                ) : (
                                    <a 
                                        href={item.href} 
                                        className={activePage === item.page ? 'active' : ''}
                                    >
                                        {item.label}
                                    </a>
                                )}
                            </li>
                        ))}
                </ul>
            </div>
        </nav>
    );
}

export default PortalNavigation;


