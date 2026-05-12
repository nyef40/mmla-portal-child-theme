<?php
/**
 * Template Name: Portal Resources
 * Description: Resources page for portal users
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    get_header(); ?>
    <div id="portal-page-main">
    <div class="portal-container">
        <div class="portal-content-area" style="padding: 40px 20px; text-align: center;">
            <h1 style="color: #0A3D62; margin-bottom: 20px;">Access Restricted</h1>
            <p style="font-size: 1.2em; color: #666; margin-bottom: 30px;">You need to be logged in to access portal resources.</p>
            <a href="/portal-login/" style="background: linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%); color: white; padding: 15px 30px; border-radius: 25px; text-decoration: none; font-weight: 600; display: inline-block;">Login to Continue</a>
        </div>
    </div>
    </div>
    <?php get_footer();
    return;
}

get_header();

$portal_resources = function_exists('mmla_portal_get_resources_normalized')
    ? mmla_portal_get_resources_normalized()
    : [];
$res_count = count($portal_resources);
$url_map = [];
foreach ($portal_resources as $r) {
    $url_map[(int) $r['id']] = $r['url'];
}
?>

<div id="portal-page-main">
<div class="portal-container">
    <div class="portal-content-area" style="padding: 40px 20px;">
        <div class="resources-header" style="text-align: center; margin-bottom: 50px;">
            <h1 style="color: #0A3D62; font-size: 2.5em; margin-bottom: 20px;">Portal Resources</h1>
            <p style="font-size: 1.2em; color: #666; max-width: 600px; margin: 0 auto;">Access educational materials, guidelines, and important documents for healthcare providers.</p>
        </div>

        <!-- Resource Categories -->
        <div class="resource-categories" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 50px;">
            <div class="category-card" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); padding: 30px; border-radius: 15px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(23, 162, 184, 0.3);">
                <div style="font-size: 3em; margin-bottom: 20px;">📋</div>
                <h3 style="margin-bottom: 15px; font-size: 1.5em;">Referral Guidelines</h3>
                <p style="margin-bottom: 25px; opacity: 0.9;">Step-by-step guides for patient referrals and documentation requirements.</p>
                <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px;"><?php echo esc_html((string) $res_count); ?> Documents</span>
            </div>
            
            <div class="category-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 30px; border-radius: 15px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);">
                <div style="font-size: 3em; margin-bottom: 20px;">🏥</div>
                <h3 style="margin-bottom: 15px; font-size: 1.5em;">Forms & Templates</h3>
                <p style="margin-bottom: 25px; opacity: 0.9;">Downloadable forms and templates for various medical procedures.</p>
                <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px;">8 Documents</span>
            </div>
            
            <div class="category-card" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); padding: 30px; border-radius: 15px; text-align: center; color: white; box-shadow: 0 10px 30px rgba(111, 66, 193, 0.3);">
                <div style="font-size: 3em; margin-bottom: 20px;">⚖️</div>
                <h3 style="margin-bottom: 15px; font-size: 1.5em;">Compliance</h3>
                <p style="margin-bottom: 25px; opacity: 0.9;">HIPAA guidelines, compliance documents, and regulatory information.</p>
                <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px;">3 Documents</span>
            </div>
        </div>

        <!-- Resource List -->
        <div class="resources-list">
            <h2 style="color: #0A3D62; margin-bottom: 30px; font-size: 1.8em;">Available Resources</h2>
            
            <div class="resource-grid" style="display: grid; gap: 20px;">
                <?php foreach ($portal_resources as $resource) :
                    $cat = $resource['category'] ?? 'General';
                    $th = function_exists('mmla_portal_resource_card_theme')
                        ? mmla_portal_resource_card_theme($cat)
                        : ['border' => '#0A3D62', 'btn' => 'linear-gradient(135deg, #0A3D62 0%, #2980b9 100%)', 'badge' => '#0A3D62'];
                    $rid = (int) ($resource['id'] ?? 0);
                    $dl_url = esc_url(home_url($resource['url'] ?? '/'));
                    $meta = $resource['meta'] ?? ($resource['type'] ?? 'PDF');
                    ?>
                <div class="resource-item" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-left: 5px solid <?php echo esc_attr($th['border']); ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.3em;"><?php echo esc_html($resource['title'] ?? ''); ?></h3>
                            <p style="color: #666; margin-bottom: 10px;"><?php echo esc_html($resource['description'] ?? ''); ?></p>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="background: <?php echo esc_attr($th['badge']); ?>; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px;"><?php echo esc_html($cat); ?></span>
                                <span style="color: #999; font-size: 14px;"><?php echo esc_html($meta); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo $dl_url; ?>" onclick="downloadResource(<?php echo (int) $rid; ?>); return false;" style="background: <?php echo esc_attr($th['btn']); ?>; color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">Download</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Access Section -->
        <div class="quick-access" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 40px; border-radius: 15px; margin-top: 50px; text-align: center;">
            <h3 style="color: #0A3D62; margin-bottom: 30px; font-size: 1.5em;">Quick Access</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <a href="/portal-referrals/" style="background: white; padding: 20px; border-radius: 10px; text-decoration: none; color: #0A3D62; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <div style="font-size: 2em; margin-bottom: 10px;">🏥</div>
                    <div style="font-weight: 600;">Submit Referral</div>
                </a>
                <a href="/contact/" style="background: white; padding: 20px; border-radius: 10px; text-decoration: none; color: #0A3D62; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <div style="font-size: 2em; margin-bottom: 10px;">📞</div>
                    <div style="font-weight: 600;">Contact Support</div>
                </a>
                <a href="/dashboard/" style="background: white; padding: 20px; border-radius: 10px; text-decoration: none; color: #0A3D62; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                    <div style="font-size: 2em; margin-bottom: 10px;">📊</div>
                    <div style="font-weight: 600;">Dashboard</div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(23, 162, 184, 0.4) !important;
}

.resource-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}

.quick-access a:hover {
    transform: translateY(-3px);
}

@media (max-width: 768px) {
    .resource-item > div {
        flex-direction: column !important;
        gap: 15px !important;
    }
    
    .resource-item a {
        align-self: flex-start !important;
    }
}
</style>

<script>
window.mmlaPortalResourceUrls = <?php echo wp_json_encode($url_map); ?>;
function downloadResource(resourceId) {
    var ajaxUrl = (typeof portalData !== 'undefined' && portalData.ajaxUrl) ? portalData.ajaxUrl : <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
    var nonce = (typeof portalData !== 'undefined' && portalData.nonce) ? portalData.nonce : <?php echo wp_json_encode(wp_create_nonce('portal_nonce')); ?>;
    fetch(ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'log_resource_access',
            resource_id: resourceId,
            nonce: nonce
        })
    }).catch(function() {});

    var resources = window.mmlaPortalResourceUrls || {};
    var url = resources[resourceId] || resources[String(resourceId)];
    if (url) {
        var link = document.createElement('a');
        link.href = url.indexOf('http') === 0 ? url : new URL(url.charAt(0) === '/' ? url : '/' + url, window.location.origin).href;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        alert('Resource not found. Please contact support.');
    }
}
</script>
</div><!-- #portal-page-main -->

<?php get_footer(); ?>


