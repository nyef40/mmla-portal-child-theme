import React from 'react';

const Home = () => {
    const isLoggedIn = !!document.cookie.match(/wordpress_logged_in_/); // Basic check for WP login cookie

    return (
        <div className="portal-home">
            <h2>Welcome to Mobile Medical LA Portal</h2>
            <p>Your gateway to medical resources and account management.</p>
            {isLoggedIn ? (
                <div>
                    <p>You’re logged in! Access your portal below:</p>
                    <a href="/portal/dashboard">Go to Dashboard</a>
                </div>
            ) : (
                <div>
                    <p>Please log in or register to access the portal:</p>
                    <a href="/portal/login">Login</a>
                    <span> | </span>
                    <a href="/portal/register">Register</a>
                </div>
            )}
        </div>
    );
};
export default Home;
