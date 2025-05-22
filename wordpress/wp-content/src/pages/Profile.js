import React from 'react';

const Profile = () => {
    const user = window.currentUser || {};
    return (
        <div className="portal-profile">
            <h2>Your Profile</h2>
            <p>Manage your account details:</p>
            <div className="profile-details">
                <p><strong>Username:</strong> {user.username || 'N/A'}</p>
                <p><strong>Email:</strong> {user.email || 'N/A'}</p>
                <p><strong>Specialty:</strong> {window.currentUserSpecialty || 'LVN'}</p> // Placeholder until DB fetch
                <p><strong>License Number:</strong> {window.currentUserLicense || '12345'}</p> // Placeholder
            </div>
        </div>
    );
};
export default Profile;
