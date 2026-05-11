<?php
/**
 * Template Name: Portal Main Page
 * Description: Main portal landing page with React app for logged-in users
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Debug info
error_log("=== Loading Portal Page ===");
error_log("User logged in: " . (is_user_logged_in() ? 'Yes' : 'No'));
error_log("User ID: " . get_current_user_id());
error_log("Post ID: " . get_the_ID());
error_log("Post slug: " . get_post_field('post_name', get_the_ID()));

get_header();

function enqueue_portal_template_scripts() {
    $manifest_path = get_stylesheet_directory() . '/portal/dist/manifest.json';
    
    if (file_exists($manifest_path)) {
        $manifest = json_decode(file_get_contents($manifest_path), true);
        
        // Always load main portal script
        if (isset($manifest['portal.js'])) {
            wp_enqueue_script(
                'portal-app',
                get_stylesheet_directory_uri() . '/portal/dist/' . $manifest['portal.js'],
                array('wp-element'), // WordPress React dependency
                null, // Version from hash
                true  // Load in footer
            );
            
            // Pass data to React
            wp_localize_script('portal-app', 'wpPortalData', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('portal_nonce'),
                'restUrl' => rest_url(),
                'restNonce' => wp_create_nonce('wp_rest'),
                'isLoggedIn' => is_user_logged_in(),
                'currentUser' => is_user_logged_in() ? array(
                    'id' => get_current_user_id(),
                    'email' => wp_get_current_user()->user_email,
                    'displayName' => wp_get_current_user()->display_name,
                    'firstName' => get_user_meta(get_current_user_id(), 'first_name', true),
                    'lastName' => get_user_meta(get_current_user_id(), 'last_name', true)
                ) : false,
                'portalPages' => array(
                    'dashboard' => '/dashboard/',
                    'profile' => '/portal-profile/',
                    'resources' => '/portal-resources/',
                    'referrals' => '/portal-referrals/',
                    'login' => '/portal-login/',
                    'register' => '/register/',
                    'home' => '/'
                )
            ));
        }
        
        // Always load main portal styles
        if (isset($manifest['portal.css'])) {
            wp_enqueue_style(
                'portal-styles',
                get_stylesheet_directory_uri() . '/portal/dist/' . $manifest['portal.css'],
                array(),
                null
            );
        }
    } else {
        // Fallback for development
        wp_enqueue_script(
            'portal-app',
            get_stylesheet_directory_uri() . '/portal/dist/portal.js',
            array('wp-element'),
            '1.0.0',
            true
        );
        wp_enqueue_style(
            'portal-styles',
            get_stylesheet_directory_uri() . '/portal/dist/portal.css',
            array(),
            '1.0.0'
        );
    }
}

// Hook scripts only for this template
add_action('wp_enqueue_scripts', 'enqueue_portal_template_scripts');

get_header();
?>

<div class="portal-container">
    <?php if (is_user_logged_in()): ?>
        <!-- React App for logged-in users -->
        <div id="portal-root" class="portal-react-app"></div>
        
        <!-- Loading state (will be replaced by React) -->
        <div id="portal-loading" class="portal-loading" style="
            text-align: center; 
            padding: 60px 20px; 
            background: #f8f9fa; 
            border-radius: 15px; 
            margin: 40px 20px;
        ">
            <div style="
                width: 60px; 
                height: 60px; 
                border: 5px solid #e3e6e8; 
                border-top-color: #0A3D62; 
                border-radius: 50%; 
                animation: spin 1s linear infinite; 
                margin: 0 auto 20px;
            "></div>
            <h3 style="color: #0A3D62; margin-bottom: 10px;">Loading Portal...</h3>
            <p style="color: #666; max-width: 400px; margin: 0 auto;">
                Preparing your personalized portal experience
            </p>
        </div>
        
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .portal-react-app ~ #portal-loading {
                display: block;
            }
            
            .portal-react-app:not(:empty) ~ #portal-loading {
                display: none;
            }
        </style>
        
    <?php else: ?>
        <!-- Static HTML for non-logged in users -->
        <div class="portal-content-area" style="padding: 40px 20px;">
            <div class="portal-welcome" style="text-align: center; margin-bottom: 50px;">
                <h1 style="color: #0A3D62; font-size: 2.5em; margin-bottom: 20px;">
                    Welcome to Mobile Medical LA Portal
                </h1>
                <p style="font-size: 1.2em; color: #666; max-width: 600px; margin: 0 auto;">
                    Your comprehensive healthcare management platform. Access your dashboard, 
                    manage referrals, view resources, and stay connected with our team.
                </p>
            </div>

            <!-- Login/Register Cards -->
            <div class="portal-auth-cards" style="
                display: grid; 
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
                gap: 30px; 
                max-width: 800px; 
                margin: 0 auto 50px;
            ">
                <div class="auth-card" style="
                    background: linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%); 
                    padding: 40px; 
                    border-radius: 15px; 
                    text-align: center; 
                    color: white; 
                    box-shadow: 0 10px 30px rgba(10, 61, 98, 0.3);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                ">
                    <div style="font-size: 3em; margin-bottom: 20px;">🔐</div>
                    <h3 style="margin-bottom: 15px; font-size: 1.5em;">Sign In</h3>
                    <p style="margin-bottom: 25px; opacity: 0.9;">
                        Access your existing account and dashboard
                    </p>
                    <a href="<?php echo esc_url(home_url('/portal-login/')); ?>" 
                       style="
                           background: rgba(255, 255, 255, 0.2); 
                           color: white; 
                           padding: 12px 30px; 
                           border-radius: 25px; 
                           text-decoration: none; 
                           font-weight: 600; 
                           transition: all 0.3s ease; 
                           display: inline-block; 
                           backdrop-filter: blur(10px); 
                           border: 1px solid rgba(255, 255, 255, 0.3);
                       ">
                        Login Now
                    </a>
                </div>
                
                <div class="auth-card" style="
                    background: linear-gradient(135deg, #28a745 0%, #20c997 100%); 
                    padding: 40px; 
                    border-radius: 15px; 
                    text-align: center; 
                    color: white; 
                    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                ">
                    <div style="font-size: 3em; margin-bottom: 20px;">👤</div>
                    <h3 style="margin-bottom: 15px; font-size: 1.5em;">New User</h3>
                    <p style="margin-bottom: 25px; opacity: 0.9;">
                        Create your account to get started
                    </p>
                    <a href="<?php echo esc_url(home_url('/register/')); ?>" 
                       style="
                           background: rgba(255, 255, 255, 0.2); 
                           color: white; 
                           padding: 12px 30px; 
                           border-radius: 25px; 
                           text-decoration: none; 
                           font-weight: 600; 
                           transition: all 0.3s ease; 
                           display: inline-block; 
                           backdrop-filter: blur(10px); 
                           border: 1px solid rgba(255, 255, 255, 0.3);
                       ">
                        Register
                    </a>
                </div>
            </div>

            <!-- Portal Features Cards -->
            <div class="portal-features" style="
                display: grid; 
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
                gap: 25px; 
                max-width: 1200px; 
                margin: 0 auto;
            ">
                <?php 
                $features = array(
                    array(
                        'icon' => '📊',
                        'color' => '#0A3D62',
                        'title' => 'Dashboard',
                        'desc' => 'View your personalized dashboard with quick access to all your portal features and recent activity.',
                        'link' => '/dashboard/',
                        'login_required' => true
                    ),
                    array(
                        'icon' => '🏥',
                        'color' => '#28a745',
                        'title' => 'Referrals',
                        'desc' => 'Submit and track patient referrals with our streamlined referral management system.',
                        'link' => '/portal-referrals/',
                        'login_required' => true
                    ),
                    array(
                        'icon' => '📚',
                        'color' => '#17a2b8',
                        'title' => 'Resources',
                        'desc' => 'Access educational materials, guidelines, and important documents for healthcare providers.',
                        'link' => '/portal-resources/',
                        'login_required' => true
                    ),
                    array(
                        'icon' => '📞',
                        'color' => '#6f42c1',
                        'title' => 'Contact',
                        'desc' => 'Get in touch with our team for support, questions, or additional information.',
                        'link' => '/contact/',
                        'login_required' => false
                    )
                );
                
                foreach ($features as $feature): 
                ?>
                    <div class="feature-card" style="
                        background: white; 
                        padding: 30px; 
                        border-radius: 15px; 
                        text-align: center; 
                        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); 
                        transition: transform 0.3s ease, box-shadow 0.3s ease; 
                        border: 1px solid #f0f0f0;
                    ">
                        <div style="font-size: 2.5em; margin-bottom: 20px; color: <?php echo esc_attr($feature['color']); ?>;">
                            <?php echo $feature['icon']; ?>
                        </div>
                        <h3 style="color: #0A3D62; margin-bottom: 15px; font-size: 1.3em;">
                            <?php echo esc_html($feature['title']); ?>
                        </h3>
                        <p style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                            <?php echo esc_html($feature['desc']); ?>
                        </p>
                        <?php if (!$feature['login_required'] || is_user_logged_in()): ?>
                            <a href="<?php echo esc_url(home_url($feature['link'])); ?>" 
                               style="
                                   background: linear-gradient(135deg, <?php echo esc_attr($feature['color']); ?> 0%, <?php echo esc_attr(darken_color($feature['color'], 20)); ?> 100%); 
                                   color: white; 
                                   padding: 10px 25px; 
                                   border-radius: 20px; 
                                   text-decoration: none; 
                                   font-weight: 600; 
                                   transition: all 0.3s ease; 
                                   display: inline-block;
                               ">
                                <?php echo $feature['login_required'] ? 'Access ' . esc_html($feature['title']) : 'Contact Us'; ?>
                            </a>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">Login required</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Debug script
console.log('=== Portal Template Loaded ===');
console.log('User logged in:', <?php echo is_user_logged_in() ? 'true' : 'false'; ?>);
console.log('Portal root element:', document.getElementById('portal-root'));
console.log('wpPortalReact data:', window.wpPortalReact);

// Check if React loaded
setTimeout(function() {
    var portalRoot = document.getElementById('portal-root');
    if (portalRoot && portalRoot.children.length === 0) {
        console.warn('React not mounted after 2 seconds');
        document.getElementById('portal-debug-info').innerHTML += '<br>⚠️ React not mounting - check console for errors';
    }
}, 2000);

// Hide loading when React mounts
var checkReactMount = setInterval(function() {
    var portalRoot = document.getElementById('portal-root');
    var loadingFallback = document.getElementById('portal-loading-fallback');
    
    if (portalRoot && portalRoot.children.length > 0 && loadingFallback) {
        loadingFallback.style.display = 'none';
        clearInterval(checkReactMount);
        console.log('React mounted successfully');
    }
}, 100);

// Timeout after 10 seconds
setTimeout(function() {
    clearInterval(checkReactMount);
    var loadingFallback = document.getElementById('portal-loading-fallback');
    if (loadingFallback && loadingFallback.style.display !== 'none') {
        loadingFallback.innerHTML = '<p style="color: #dc3545;">Failed to load portal. Please check browser console and refresh.</p>';
    }
}, 10000);
</script>

<?php
// Add portal styles (moved from inline to here)
add_action('wp_footer', function() {
    if (!is_user_logged_in()): // Only show these styles for non-logged in static version
    ?>
    <style>
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
        }

        .auth-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(10, 61, 98, 0.4) !important;
        }

        @media (max-width: 768px) {
            .portal-welcome h1 {
                font-size: 2em !important;
            }
            
            .portal-welcome p {
                font-size: 1em !important;
            }
            
            .portal-auth-cards,
            .portal-features {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    <?php
    endif;
}, 100);

get_footer();

// Helper function to darken color for gradients
function darken_color($color, $percent) {
    $color = str_replace('#', '', $color);
    if (strlen($color) == 3) {
        $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
    }
    $rgb = array(
        hexdec(substr($color, 0, 2)),
        hexdec(substr($color, 2, 2)),
        hexdec(substr($color, 4, 2))
    );
    
    for ($i = 0; $i < 3; $i++) {
        $rgb[$i] = round($rgb[$i] * (100 - $percent) / 100);
        if ($rgb[$i] < 0) $rgb[$i] = 0;
        if ($rgb[$i] > 255) $rgb[$i] = 255;
    }
    
    return sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
}
?>
