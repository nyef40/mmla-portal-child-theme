<?php
/**
 * Template Name: Resources (public)
 * Used for the site page slug "resources" (page-resources.php).
 * Public marketing / training videos — not the logged-in portal (/portal-resources/).
 *
 * YouTube IDs default to the same nine Elementor widgets as production
 * https://mobilemedicalla.com/resources2/ — override with option
 * mmla_public_resources_youtube_ids (array of 11-char IDs) or filter
 * mmla_public_resources_youtube_ids.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('portal_debug')) {
    portal_debug('TEMPLATE LOADED', [
        'template' => basename(__FILE__),
        'user_logged_in' => is_user_logged_in(),
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
    ]);
}

get_header();

$video_ids = function_exists('mmla_get_public_resources_youtube_ids')
    ? mmla_get_public_resources_youtube_ids()
    : [];
?>

<main id="primary" class="mmla-public-resources">
    <div class="mmla-public-resources-inner">
        <?php
        while (have_posts()) :
            the_post();
            ?>
            <header class="mmla-public-resources-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>
            <div class="entry-content mmla-public-resources-content">
                <?php the_content(); ?>
            </div>
            <?php
        endwhile;
        ?>

        <?php if (!empty($video_ids)) : ?>
            <section class="mmla-youtube-section" aria-label="Video resources">
                <h2 class="mmla-youtube-heading">Video resources</h2>
                <div class="mmla-youtube-grid">
                    <?php
                    foreach ($video_ids as $vid) {
                        $vid = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $vid);
                        if ($vid === '') {
                            continue;
                        }
                        $embed = esc_url('https://www.youtube-nocookie.com/embed/' . $vid);
                        ?>
                        <div class="mmla-youtube-cell">
                            <div class="mmla-youtube-aspect">
                                <iframe
                                    src="<?php echo $embed; ?>"
                                    title="<?php echo esc_attr('YouTube video ' . $vid); ?>"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>

<style>
.mmla-public-resources {
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px 20px 60px;
}
.mmla-public-resources-inner .entry-title {
    color: #0A3D62;
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    margin: 0 0 1rem;
}
.mmla-public-resources-content {
    margin-bottom: 2.5rem;
    color: #334155;
    line-height: 1.6;
}
.mmla-youtube-heading {
    color: #0A3D62;
    font-size: 1.35rem;
    margin: 0 0 1.25rem;
}
.mmla-youtube-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.mmla-youtube-cell {
    min-width: 0;
}
.mmla-youtube-aspect {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    background: #0f172a;
}
.mmla-youtube-aspect iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}
@media (max-width: 900px) {
    .mmla-youtube-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 560px) {
    .mmla-youtube-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
get_footer();
