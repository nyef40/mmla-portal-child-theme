<?php
/**
 * Template Name: Resources
 */

// At the top of page-profile.php, page-resources.php, etc.
portal_debug("TEMPLATE LOADED", [
    'template' => basename(__FILE__),
    'user_logged_in' => is_user_logged_in(),
    'request_uri' => $_SERVER['REQUEST_URI']
]);

// Before redirect logic
if (!is_user_logged_in() || (function_exists('user_has_portal_access_enhanced') && !user_has_portal_access_enhanced())) {
    portal_debug("REDIRECT TRIGGERED", [
        'reason' => !is_user_logged_in() ? 'not_logged_in' : 'no_portal_access',
        'redirect_to' => '/portal-login/'
    ]);
    wp_redirect(home_url('/portal-login/'));
    exit;
}

get_header(); ?>

<div class="portal-container">
    <div class="portal-resources">
        <h1>Portal Resources</h1>
        <p class="page-subtitle">Access important documents, guides, and training materials.</p>

        <div class="resource-grid">
            <?php
            global $wpdb;
            $resources_table = $wpdb->prefix . 'portal_resources';
            $resources = $wpdb->get_results("SELECT * FROM {$resources_table} ORDER BY created_at DESC");

            if ($resources) {
                foreach ($resources as $resource) {
                    ?>
                    <div class="resource-card">
                        <h3><?php echo esc_html($resource->title); ?></h3>
                        <p><?php echo esc_html($resource->description); ?></p>
                        <?php if ($resource->file_path): ?>
                            <a href="<?php echo esc_url(content_url($resource->file_path)); ?>" target="_blank" class="button button-primary">Download Resource</a>
                        <?php endif; ?>
                        <?php if ($resource->access_level): ?>
                            <span class="access-level">Access: <?php echo esc_html(ucfirst($resource->access_level)); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-resources">No resources available at the moment. Please check back later!</p>';
            }
            ?>
        </div>

        <div class="resource-upload-section">
            <h2>Upload New Resource</h2>
            <p>Only administrators can upload new resources.</p>
            <?php if (current_user_can('manage_options')): // Check if current user is an administrator ?>
                <form method="post" action="" enctype="multipart/form-data" class="resource-upload-form">
                    <?php wp_nonce_field('upload_portal_resource', 'upload_resource_nonce'); ?>

                    <div class="form-group">
                        <label for="resource_title">Title *</label>
                        <input type="text" id="resource_title" name="resource_title" required>
                    </div>

                    <div class="form-group">
                        <label for="resource_description">Description</label>
                        <textarea id="resource_description" name="resource_description" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="resource_file">Upload File</label>
                        <input type="file" id="resource_file" name="resource_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.jpg,.jpeg,.png">
                        <small>Max file size: 2MB. Allowed types: PDF, Word, Excel, PPT, Zip, Images.</small>
                    </div>

                    <div class="form-group">
                        <label for="access_level">Access Level</label>
                        <select id="access_level" name="access_level">
                            <option value="public">Public</option>
                            <option value="private">Private (Portal Users Only)</option>
                            <option value="admin">Admin Only</option>
                        </select>
                    </div>

                    <button type="submit" class="button button-primary">Upload Resource</button>
                </form>
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_resource_nonce']) && wp_verify_nonce($_POST['upload_resource_nonce'], 'upload_portal_resource')) {
                    $title = sanitize_text_field($_POST['resource_title']);
                    $description = sanitize_textarea_field($_POST['resource_description']);
                    $access_level = sanitize_text_field($_POST['access_level']);
                    $file_path = null;

                    if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = wp_upload_dir();
                        $target_dir = $upload_dir['basedir'] . '/portal-resources/';
                        if (!file_exists($target_dir)) {
                            wp_mkdir_p($target_dir);
                        }

                        $file_name = sanitize_file_name($_FILES['resource_file']['name']);
                        $target_file = $target_dir . $file_name;
                        $file_type = wp_check_filetype($file_name);

                        $allowed_types = [
                            'pdf' => 'application/pdf',
                            'doc' => 'application/msword',
                            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'xls' => 'application/vnd.ms-excel',
                            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'ppt' => 'application/vnd.ms-powerpoint',
                            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'zip' => 'application/zip',
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                        ];

                        if (!in_array($file_type['type'], $allowed_types)) {
                            echo '<div class="error-message">Invalid file type.</div>';
                        } elseif ($_FILES['resource_file']['size'] > 2 * 1024 * 1024) { // 2MB limit
                            echo '<div class="error-message">File size exceeds 2MB limit.</div>';
                        } elseif (move_uploaded_file($_FILES['resource_file']['tmp_name'], $target_file)) {
                            $file_path = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $target_file);
                            $file_path = str_replace(home_url(), '', $file_path); // Store relative path
                            echo '<div class="success-message">File uploaded successfully!</div>';
                        } else {
                            echo '<div class="error-message">Error uploading file.</div>';
                        }
                    }

                    $insert_result = $wpdb->insert(
                        $resources_table,
                        [
                            'title' => $title,
                            'description' => $description,
                            'file_path' => $file_path,
                            'access_level' => $access_level,
                        ],
                        ['%s', '%s', '%s', '%s']
                    );

                    if ($insert_result) {
                        echo '<div class="success-message">Resource added to database!</div>';
                        // Refresh the page to show new resource
                        echo '<meta http-equiv="refresh" content="0">';
                    } else {
                        echo '<div class="error-message">Failed to add resource to database.</div>';
                    }
                }
                ?>
            <?php else: ?>
                <div class="info-message">You do not have permission to upload resources.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.portal-resources {
    max-width: 960px;
    margin: 50px auto;
    padding: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
}

.portal-resources h1 {
    color: #0A3D62;
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.page-subtitle {
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 40px;
}

.resource-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.resource-card {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 10px;
    border: 1px solid #e1e5e9;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.resource-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.resource-card h3 {
    color: #0A3D62;
    font-size: 1.6rem;
    margin-bottom: 15px;
}

.resource-card p {
    font-size: 1rem;
    color: #666;
    margin-bottom: 25px;
    line-height: 1.6;
    flex-grow: 1;
}

.resource-card .button {
    display: inline-block;
    padding: 10px 20px;
    background: linear-gradient(135deg, #0A3D62 0%, #1e5f8b 100%);
    color: white;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 15px;
}

.resource-card .button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(10, 61, 98, 0.2);
}

.resource-card .access-level {
    display: block;
    margin-top: 10px;
    font-size: 0.9rem;
    color: #888;
    font-style: italic;
}

.no-resources {
    font-size: 1.2rem;
    color: #888;
    margin-top: 50px;
}

.resource-upload-section {
    margin-top: 60px;
    padding: 40px;
    background: #f0f4f7;
    border-radius: 12px;
    border-left: 5px solid #0A3D62;
    text-align: left;
}

.resource-upload-section h2 {
    color: #0A3D62;
    font-size: 2rem;
    margin-bottom: 20px;
}

.resource-upload-section p {
    color: #555;
    margin-bottom: 30px;
}

.resource-upload-form .form-group {
    margin-bottom: 20px;
}

.resource-upload-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.resource-upload-form input[type="text"],
.resource-upload-form textarea,
.resource-upload-form select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 16px;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

.resource-upload-form input[type="file"] {
    padding: 10px 0;
}

.resource-upload-form input:focus,
.resource-upload-form textarea:focus,
.resource-upload-form select:focus {
    border-color: #0A3D62;
    outline: none;
    box-shadow: 0 0 0 3px rgba(10, 61, 98, 0.1);
}

.resource-upload-form small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 14px;
}

.resource-upload-form .button-primary {
    margin-top: 20px;
    width: auto;
}

.success-message {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #c3e6cb;
    text-align: center;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #f5c6cb;
}

.info-message {
    background: #d1ecf1;
    color: #0c5460;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px solid #bee5eb;
}

@media (max-width: 768px) {
    .portal-resources {
        margin: 20px;
        padding: 30px 20px;
    }

    .resource-grid {
        grid-template-columns: 1fr;
    }

    .resource-upload-section {
        padding: 30px 20px;
    }
}
</style>

<?php get_footer(); ?>
