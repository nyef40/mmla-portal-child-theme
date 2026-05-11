const api = {
    fetchDashboardData: async () => {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'fetch_dashboard_data',
                nonce: window.portalSettings.nonce
            })
        });
        return response.json();
    },
    submitReferral: async (referralData) => {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'submit_referral',
                nonce: window.portalSettings.nonce,
                ...referralData
            })
        });
        return response.json();
    },
    logResourceAccess: async (resourceId) => {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'log_resource_access',
                nonce: window.portalSettings.nonce,
                resource_id: resourceId
            })
        });
        return response.json();
    },
    getUserProfile: async () => {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'get_user_profile',
                nonce: window.portalSettings.nonce
            })
        });
        return response.json();
    },
    updateUserProfile: async (profileData) => {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'update_user_profile',
                nonce: window.portalSettings.nonce,
                ...profileData
            })
        });
        return response.json();
    },
    getReferrals: async () => {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'get_referrals',
                nonce: window.portalSettings.nonce
            })
        });
        return response.json();
    },
    getResources: async () => {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'get_resources',
                nonce: window.portalSettings.nonce
            })
        });
        return response.json();
    }
};

export default api;

