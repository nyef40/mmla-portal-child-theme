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

get_header(); ?>

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
                <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px;">5 Documents</span>
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
                <!-- Referral Guidelines -->
                <div class="resource-item" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-left: 5px solid #17a2b8;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.3em;">Understanding HIPAA Compliance</h3>
                            <p style="color: #666; margin-bottom: 10px;">Comprehensive guide to HIPAA compliance for healthcare providers</p>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="background: #17a2b8; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px;">Compliance</span>
                                <span style="color: #999; font-size: 14px;">PDF • 2.3 MB</span>
                            </div>
                        </div>
                        <a href="#" onclick="downloadResource(1)" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">Download</a>
                    </div>
                </div>

                <div class="resource-item" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-left: 5px solid #28a745;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.3em;">Patient Care Guidelines</h3>
                            <p style="color: #666; margin-bottom: 10px;">Best practices for patient care in home health settings</p>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px;">Clinical</span>
                                <span style="color: #999; font-size: 14px;">PDF • 1.8 MB</span>
                            </div>
                        </div>
                        <a href="#" onclick="downloadResource(2)" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">Download</a>
                    </div>
                </div>

                <div class="resource-item" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-left: 5px solid #dc3545;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.3em;">Emergency Procedures</h3>
                            <p style="color: #666; margin-bottom: 10px;">Step-by-step emergency response procedures</p>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px;">Safety</span>
                                <span style="color: #999; font-size: 14px;">PDF • 1.2 MB</span>
                            </div>
                        </div>
                        <a href="#" onclick="downloadResource(3)" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">Download</a>
                    </div>
                </div>

                <div class="resource-item" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-left: 5px solid #6f42c1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.3em;">Referral Form Template</h3>
                            <p style="color: #666; margin-bottom: 10px;">Standard referral form template for patient transfers</p>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="background: #6f42c1; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px;">Forms</span>
                                <span style="color: #999; font-size: 14px;">DOC • 0.5 MB</span>
                            </div>
                        </div>
                        <a href="#" onclick="downloadResource(4)" style="background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%); color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">Download</a>
                    </div>
                </div>

                <div class="resource-item" style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1); border-left: 5px solid #fd7e14;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="color: #0A3D62; margin-bottom: 8px; font-size: 1.3em;">Insurance Verification Checklist</h3>
                            <p style="color: #666; margin-bottom: 10px;">Complete checklist for verifying patient insurance coverage</p>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="background: #fd7e14; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px;">Administrative</span>
                                <span style="color: #999; font-size: 14px;">PDF • 0.8 MB</span>
                            </div>
                        </div>
                        <a href="#" onclick="downloadResource(5)" style="background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%); color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">Download</a>
                    </div>
                </div>
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
function downloadResource(resourceId) {
    // Log resource access
    if (typeof portalData !== 'undefined') {
        fetch(portalData.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'log_resource_access',
                resource_id: resourceId,
                nonce: portalData.nonce
            })
        });
    }
    
    // Simulate download (replace with actual file URLs)
    const resources = {
        1: '/wp-content/uploads/hipaa-guide.pdf',
        2: '/wp-content/uploads/patient-care-guide.pdf',
        3: '/wp-content/uploads/emergency-procedures.pdf',
        4: '/wp-content/uploads/referral-form-template.doc',
        5: '/wp-content/uploads/insurance-verification-checklist.pdf'
    };
    
    if (resources[resourceId]) {
        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = resources[resourceId];
        link.download = '';
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


