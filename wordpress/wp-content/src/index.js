// Update your index.js to remove the initial loading screen
import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import './styles/portal.css';

console.log('=== Portal Script Loading ===');

// Prevent multiple initializations
let portalInitialized = false;

function initPortal() {
    if (portalInitialized) {
        console.warn('Portal already initialized, skipping');
        return;
    }
    
    console.log('Initializing portal...');
    portalInitialized = true;
    
    // Check if portal root already exists
    let portalRoot = document.getElementById('portal-root');
    
    if (!portalRoot) {
        console.warn('No portal-root found, creating one');
        portalRoot = document.createElement('div');
        portalRoot.id = 'portal-root';
        document.body.appendChild(portalRoot);
    } else {
        // Remove any loading content
        portalRoot.innerHTML = '';
    }
    
    console.log('Portal root ready:', portalRoot);
    
    // Check wpPortalData
    if (!window.wpPortalData) {
        console.error('ERROR: wpPortalData is not defined!');
        portalRoot.innerHTML = `
            <div style="padding: 40px; text-align: center; color: #dc3545;">
                <h2>Portal Configuration Error</h2>
                <p>Required data not loaded. Please refresh.</p>
                <button onclick="window.location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Refresh Page
                </button>
            </div>
        `;
        return;
    }
    
    // Fix wpPortalData types
    const portalData = {
        ...window.wpPortalData,
        isLoggedIn: window.wpPortalData.isLoggedIn === '1' || window.wpPortalData.isLoggedIn === true || window.wpPortalData.isLoggedIn === 1,
        nonce: window.wpPortalData.nonce || '',
        restNonce: window.wpPortalData.restNonce || ''
    };
    
    console.log('Portal data:', portalData);
    
    try {
        const root = createRoot(portalRoot);
        root.render(
            <React.StrictMode>
                <App portalData={portalData} />
            </React.StrictMode>
        );
        
        console.log('✅ Portal React app mounted successfully');
    } catch (error) {
        console.error('❌ Failed to mount React app:', error);
        portalRoot.innerHTML = `
            <div style="padding: 40px; text-align: center; color: #dc3545;">
                <h2>React Error</h2>
                <p>${error.message}</p>
                <button onclick="window.location.reload()" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Reload Portal
                </button>
            </div>
        `;
    }
}

// Wait for DOM and ensure single initialization
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(initPortal, 100);
    });
} else {
    setTimeout(initPortal, 100);
}

console.log('=== Portal script loaded ===');