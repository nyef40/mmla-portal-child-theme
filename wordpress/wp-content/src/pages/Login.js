import React, { useState } from 'react';

const Login = () => {
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [message, setMessage] = useState(window.location.search.includes('verified=1') ? 'Email verified! Please log in.' : '');
   
        const handleSubmit = async (e) => {
        e.preventDefault();
        const response = await fetch('https://mobilemedicalla.com/wp-json/mmla/v1/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password }),
        });
        const data = await response.json();
        if (response.ok) {
            setMessage('Login successful! Redirecting...');
            setTimeout(() => window.location.href = '/portal/dashboard', 2000);
        } else {
            setMessage(data.message);
        }
    };

    return (
        <div className="portal-login">
            <h2>Login</h2>
            <form onSubmit={handleSubmit}>
                <label>Username</label>
                <input
                    type="text"
                    value={username}
                    onChange={(e) => setUsername(e.target.value)}
                    placeholder="Username"
                    required
                />
                <label>Password</label>
                <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Password"
                    required
                />
                <button type="submit">Login</button>
            </form>
            {message && <p>{message}</p>}
        </div>
    );
};
export default Login;
