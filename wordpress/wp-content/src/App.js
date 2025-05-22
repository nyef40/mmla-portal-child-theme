import React from 'react';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import Home from './pages/Home';
import Resources from './pages/Resources';
import Profile from './pages/Profile';
import './styles.css';

const App = () => {
    const page = window.PortalPage || 'home';
    const isLoggedIn = window.MmlaPortal?.isLoggedIn || false;

    const renderPage = () => {
        switch (page) {
            case 'login': return <Login />;
            case 'register': return <Register />;
            case 'dashboard': return <Dashboard />;
            case 'resources': return <Resources />;
            case 'profile': return <Profile />;
            case 'home': return <Home />;
            default: return <div>404 - Page Not Found</div>;
        }
    };

    return (
        <div className="portal-app">
            <nav className="portal-nav">
                <a href="/portal/">Home</a>
                {isLoggedIn ? (
                    <>
                        <a href="/portal/dashboard">Dashboard</a>
                        <a href="/portal/resources">Resources</a>
                        <a href="/portal/profile">Profile</a>
                        <a href="/?mmla_logout=1">Logout</a>
                    </>
                ) : (
                    <>
                        <a href="/portal/login">Login</a>
                        <a href="/portal/register">Register</a>
                    </>
                )}
            </nav>
            {renderPage()}
            <footer className="portal-footer">
                <p>© 2025 Mobile Medical LA. All rights reserved.</p>
            </footer>
        </div>
    );
};
export default App;
