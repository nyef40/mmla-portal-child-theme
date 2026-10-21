<?php
/**
 * Plugin Name: MMLA Industry News
 * Description: Shows the latest home health, home infusion & IVIG, and clinical trials news. Place [mmla_news] on any page. Stories are updated by ny-bots (website/news.py), which writes wp-content/uploads/mmla-news/news.json.
 * Version: 1.0.0
 * Author: Mobile Medical LA
 */

if (!defined('ABSPATH')) {
    exit;
}

/** A file written by ny-bots (news.json or videos.json), or null when missing or unreadable. */
function mmla_news_data($name = 'news.json') {
    $uploads = wp_upload_dir();
    $file = trailingslashit($uploads['basedir']) . 'mmla-news/' . $name;
    if (!is_readable($file)) {
        return null;
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : null;
}

function mmla_news_date($iso, $format = 'M j, Y') {
    $time = strtotime((string) $iso);
    return $time ? wp_date($format, $time) : '';
}

function mmla_news_card($story, $icon) {
    $url = esc_url($story['url'] ?? '');
    $source = esc_html($story['source'] ?? '');
    $out = '<article class="mmla-news-card">';
    $out .= '<div class="mmla-news-band"><span aria-hidden="true">' . esc_html($icon) . '</span>' . $source . '</div>';
    $out .= '<div class="mmla-news-body">';
    $out .= '<time>' . esc_html(mmla_news_date($story['date'] ?? '')) . '</time>';
    $out .= '<h3><a href="' . $url . '" target="_blank" rel="noopener">' . esc_html($story['title'] ?? '') . '</a></h3>';
    if (!empty($story['summary'])) {
        $out .= '<p>' . esc_html($story['summary']) . '</p>';
    }
    $out .= '<a class="mmla-news-more" href="' . $url . '" target="_blank" rel="noopener">Read at ' . $source . ' &#8599;</a>';
    return $out . '</div></article>';
}

function mmla_news_shortcode($atts) {
    $data = mmla_news_data();
    if (!$data || empty($data['areas'])) {
        return '<p class="mmla-news-empty">News will appear here shortly.</p>';
    }
    $count = 0;
    foreach ($data['areas'] as $area) {
        $count += count($area['stories'] ?? []);
    }
    $atts = shortcode_atts(
        [
            'title' => 'Industry News',
            'intro' => 'Daily updates on home health, home infusion & IVIG, and clinical research, gathered from CMS, FDA, NHIA, the Infusion Nurses Society, the Immune Deficiency Foundation and ClinicalTrials.gov.',
        ],
        $atts,
        'mmla_news'
    );
    $out = '<div class="mmla-news">';
    if ($atts['title'] !== '') {
        // A div, not <header>: the site's theme hides <header> elements on its main pages.
        $out .= '<div class="mmla-news-hero"><h1>' . esc_html($atts['title']) . '</h1>';
        $out .= '<p>' . esc_html($atts['intro']) . '</p></div>';
    }
    $out .= '<div class="mmla-news-stamp">Updated ' . esc_html(mmla_news_date($data['updated'] ?? '', 'F j, Y')) . ' &middot; ' . intval($count) . ' stories</div>';

    // Filter buttons: All, then one per area.
    $out .= '<nav class="mmla-news-tabs" aria-label="News areas"><button type="button" class="on" data-area="all">All</button>';
    foreach ($data['areas'] as $area) {
        $out .= '<button type="button" data-area="' . esc_attr($area['key']) . '">' . esc_html(trim(($area['icon'] ?? '') . ' ' . $area['name'])) . '</button>';
    }
    $out .= '</nav>';

    // Today's briefing: the newest stories across all areas.
    if (!empty($data['briefing'])) {
        $out .= '<section class="mmla-news-briefing" data-area="all"><h2>Today&#8217;s briefing</h2><div class="mmla-news-sub">The newest stories across our three service areas</div><ol>';
        foreach ($data['briefing'] as $story) {
            $out .= '<li><div><a href="' . esc_url($story['url']) . '" target="_blank" rel="noopener">' . esc_html($story['title']) . '</a>';
            $out .= '<small>' . esc_html($story['source']) . ' &middot; ' . esc_html($story['area_name'] ?? '') . '</small></div></li>';
        }
        $out .= '</ol></section>';
    }

    foreach ($data['areas'] as $area) {
        if (empty($area['stories'])) {
            continue;
        }
        $icon = $area['icon'] ?? '';
        $out .= '<section class="mmla-news-area" data-area="' . esc_attr($area['key']) . '">';
        $out .= '<h2><span aria-hidden="true">' . esc_html($icon) . '</span> ' . esc_html($area['name']) . '</h2><div class="mmla-news-grid">';
        foreach ($area['stories'] as $story) {
            $out .= mmla_news_card($story, $icon);
        }
        $out .= '</div></section>';
    }
    $out .= '<p class="mmla-news-note">Headlines and short summaries link to the original publishers. This page is for general information and is not medical advice. Updated automatically twice a day; the newest story in each area replaces the oldest.</p>';
    return $out . '</div>';
}
add_shortcode('mmla_news', 'mmla_news_shortcode');

/** The YouTube video id in a watch address, or ''. */
function mmla_news_video_id($url) {
    return preg_match('~(?:[?&]v=|youtu\.be/|/live/)([\w-]{11})~', (string) $url, $m) ? $m[1] : '';
}

/** [mmla_videos]: the newest videos from the industry's own YouTube channels. */
function mmla_videos_shortcode($atts) {
    $atts = shortcode_atts(
        [
            'title' => 'Resources',
            'intro' => 'Videos for patients, families and professionals from NHIA, the Infusion Nurses Society, the Immune Deficiency Foundation, the National Alliance for Care at Home, CISCRP and the NIH Clinical Center.',
        ],
        $atts,
        'mmla_videos'
    );
    $data = mmla_news_data('videos.json');
    if (!$data || empty($data['areas'])) {
        return '<p class="mmla-news-empty">Videos will appear here shortly.</p>';
    }
    $out = '<div class="mmla-news mmla-videos">';
    if ($atts['title'] !== '') {
        $out .= '<div class="mmla-news-hero"><h1>' . esc_html($atts['title']) . '</h1>';
        $out .= '<p>' . esc_html($atts['intro']) . '</p></div>';
    }
    $out .= '<nav class="mmla-news-tabs" aria-label="Video areas"><button type="button" class="on" data-area="all">All</button>';
    foreach ($data['areas'] as $area) {
        $out .= '<button type="button" data-area="' . esc_attr($area['key']) . '">' . esc_html(trim(($area['icon'] ?? '') . ' ' . $area['name'])) . '</button>';
    }
    $out .= '</nav>';
    foreach ($data['areas'] as $area) {
        if (empty($area['stories'])) {
            continue;
        }
        $out .= '<section class="mmla-news-area" data-area="' . esc_attr($area['key']) . '">';
        $out .= '<h2><span aria-hidden="true">' . esc_html($area['icon'] ?? '') . '</span> ' . esc_html($area['name']) . '</h2><div class="mmla-news-grid">';
        foreach ($area['stories'] as $video) {
            $id = mmla_news_video_id($video['url'] ?? '');
            if ($id === '') {
                continue;
            }
            $title = esc_html($video['title'] ?? '');
            $out .= '<article class="mmla-news-card mmla-video">';
            // The picture only; the player loads when someone presses play (keeps the page fast).
            $out .= '<button type="button" class="mmla-video-play" data-video="' . esc_attr($id) . '" aria-label="Play: ' . esc_attr($video['title'] ?? '') . '">';
            $out .= '<img src="https://i.ytimg.com/vi/' . esc_attr($id) . '/hqdefault.jpg" alt="" loading="lazy"><span class="mmla-video-icon" aria-hidden="true"></span></button>';
            $out .= '<div class="mmla-news-body"><time>' . esc_html(mmla_news_date($video['date'] ?? '')) . '</time>';
            $out .= '<h3>' . $title . '</h3>';
            $out .= '<a class="mmla-news-more" href="' . esc_url($video['url']) . '" target="_blank" rel="noopener">' . esc_html($video['source'] ?? '') . ' on YouTube &#8599;</a></div></article>';
        }
        $out .= '</div></section>';
    }
    $out .= '<p class="mmla-news-note">Videos are published by the organizations named and play from YouTube. They are for general information and are not medical advice. The newest video in each area replaces the oldest.</p>';
    return $out . '</div>';
}
add_shortcode('mmla_videos', 'mmla_videos_shortcode');

/** Styles and the small filter script, only on pages that use the shortcode. */
function mmla_news_assets() {
    global $post;
    if (!is_a($post, 'WP_Post') || !(has_shortcode($post->post_content, 'mmla_news') || has_shortcode($post->post_content, 'mmla_videos'))) {
        return;
    }
    $dir = plugin_dir_url(__FILE__);
    $version = filemtime(plugin_dir_path(__FILE__) . 'mmla-news.css');
    wp_enqueue_style('mmla-news', $dir . 'mmla-news.css', [], $version);
    wp_register_script('mmla-news', '', [], $version, true);
    wp_enqueue_script('mmla-news');
    wp_add_inline_script('mmla-news', <<<'JS'
document.querySelectorAll('.mmla-news').forEach(function (root) {
  root.querySelectorAll('.mmla-news-tabs button').forEach(function (button) {
    button.addEventListener('click', function () {
      var area = button.dataset.area;
      root.querySelectorAll('.mmla-news-tabs button').forEach(function (b) { b.classList.toggle('on', b === button); });
      root.querySelectorAll('section[data-area]').forEach(function (s) {
        s.hidden = area !== 'all' && s.dataset.area !== area;
      });
    });
  });
  root.querySelectorAll('.mmla-video-play').forEach(function (button) {
    button.addEventListener('click', function () {
      var frame = document.createElement('iframe');
      frame.src = 'https://www.youtube-nocookie.com/embed/' + button.dataset.video + '?autoplay=1&rel=0';
      frame.title = button.getAttribute('aria-label');
      frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
      frame.allowFullscreen = true;
      frame.className = 'mmla-video-frame';
      button.replaceWith(frame);
    });
  });
});
JS
    );
}
add_action('wp_enqueue_scripts', 'mmla_news_assets');
