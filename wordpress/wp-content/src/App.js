// src/App.js
import React, { useEffect, useState } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate, useNavigate } from 'react-router-dom';
import Login from './components/Login';
import Dashboard from './components/Dashboard';
import Profile from './components/Profile';
import Resources from './components/Resources';
import Referrals from './components/Referrals';
import Contact from './components/Contact';
import Header from './components/Header';

// Navigation handler component
function NavigationHandler() {
    const navigate = useNavigate();
    
    useEffect(() => {
        const handlePortalNavigate = (event) => {
            const route = event.detail?.route || '';
            if (route) {
                navigate(`/${route}`);
            }
        };
        
        window.addEventListener('portal-navigate', handlePortalNavigate);
        
        return () => {
            window.removeEventListener('portal-navigate', handlePortalNavigate);
        };
    }, [navigate]);
    
    return null;
}

function App({ portalData }) {
    console.log('[Portal] App component rendering with data:', portalData);
    
    // Use the passed portalData or window.wpPortalData as fallback
    const [data, setData] = useState(() => {
        if (portalData) return portalData;
        if (window.wpPortalData) {
            return {
                ...window.wpPortalData,
                isLoggedIn: window.wpPortalData.isLoggedIn === '1' || window.wpPortalData.isLoggedIn === true || window.wpPortalData.isLoggedIn === 1
            };
        }
        return {
            isLoggedIn: false,
            nonce: '',
            restNonce: '',
            user: null,
            currentPage: 'portal'
        };
    });
    
    const ProtectedRoute = ({ children }) => {
        if (!data.isLoggedIn) {
            return <Navigate to="/login" replace />;
        }
        return children;
    };
    
    const PublicRoute = ({ children }) => {
        if (data.isLoggedIn) {
            return <Navigate to="/dashboard" replace />;
        }
        return children;
    };
    
    return (
        <Router basename="/portal">
          <NavigationHandler />
            <Header user={data.user} isLoggedIn={data.isLoggedIn} />
            <div className="portal-content" style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
                <Routes>
                    <Route path="/" element={
                        data.isLoggedIn ? 
                            <Navigate to="/dashboard" replace /> : 
                            <Navigate to="/login" replace />
                    } />
                    <Route path="/login" element={
                        <PublicRoute>
                            <Login portalData={data} />
                        </PublicRoute>
                    } />
                    <Route path="/dashboard" element={
                        <ProtectedRoute>
                            <Dashboard portalData={data} />
                        </ProtectedRoute>
                    } />
                    <Route path="/profile" element={
                        <ProtectedRoute>
                            <Profile portalData={data} />
                        </ProtectedRoute>
                    } />
                    <Route path="/resources" element={
                        <ProtectedRoute>
                            <Resources portalData={data} />
                        </ProtectedRoute>
                    } />
                    <Route path="/referrals" element={
                        <ProtectedRoute>
                            <Referrals portalData={data} />
                        </ProtectedRoute>
                    } />
                    <Route path="/contact" element={
                        <ProtectedRoute>
                            <Contact portalData={data} />
                        </ProtectedRoute>
                    } />
                    <Route path="/register" element={
                        <PublicRoute>
                            <div style={{ padding: '40px', textAlign: 'center' }}>
                                <h2>Registration</h2>
                                <p>Registration form would go here.</p>
                                <p>For now, please use the main site registration.</p>
                                <a href="/register" className="btn btn-primary">
                                    Go to Registration
                                </a>
                            </div>
                        </PublicRoute>
                    } />
                </Routes>
            </div>
        </Router>
    );
}

export default App;
