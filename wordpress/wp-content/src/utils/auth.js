export function isAuthenticated() {
    return window.portalSettings?.isLoggedIn || false;
}

export function getCurrentUser() {
    if (isAuthenticated()) {
        return {
            id: window.portalSettings?.user?.id || 0,
            username: window.portalSettings?.user?.username || 'unknown',
            email: window.portalSettings?.user?.email || '',
            role: window.portalSettings?.user?.role || 'portal_user'
        };
    }
    return null;
}

export async function login(username, password) {
    try {
        const response = await fetch(window.portalSettings.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'portal_login',
                nonce: window.portalSettings.nonce,
                username,
                password
            })
        });
        const data = await response.json();
        if (data.success) {
            window.location.href = '/dashboard/';
            return { success: true, user: data.data.user };
        } else {
            return { success: false, message: data.data.message };
        }
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Network error or server issue.' };
    }
}

export function logout() {
    fetch(window.portalSettings.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'portal_logout',
            nonce: window.portalSettings.nonce
        })
    }).then(() => {
        window.location.href = '/portal-login/';
    });
}

