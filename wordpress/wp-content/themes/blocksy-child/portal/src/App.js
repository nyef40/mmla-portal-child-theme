import React from 'react';
import Login from './components/Login';
import Register from './components/Register';
import Dashboard from './components/Dashboard';
import Profile from './components/Profile';
import Resources from './components/Resources';
import Referrals from './components/Referrals';
import Contact from './components/Contact';

const App = () => {
  const currentPage = window.wpPortalData?.currentPage || '';
  const isLoggedIn = window.wpPortalData?.isLoggedIn || false;

  // Route based on WordPress page slug
const renderPage = () => {
  switch (currentPage) {
    case 'login':
    case 'portal-login':
      return <Login />;
    case 'register':
      return <Register />;
    case 'dashboard':
      return isLoggedIn ? <Dashboard /> : <Login />;
    case 'profile':
    case 'portal-profile':
      return isLoggedIn ? <Profile /> : <Login />;
    case 'resources':
    case 'portal-resources':
      return isLoggedIn ? <Resources /> : <Login />;
    case 'referrals':
    case 'portal-referrals':
      return isLoggedIn ? <Referrals /> : <Login />;
    case 'contact':
      return <Contact />;
    case 'portal':
    default:
      return isLoggedIn ? <Dashboard /> : <Login />;
  }
};

  return (
    <div className="portal-app">
      {renderPage()}
    </div>
  );
};

export default App;