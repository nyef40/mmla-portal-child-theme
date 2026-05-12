<?php
// portal-loader.php - Minimal portal loader
add_action('init', function() {
    $portal_pages = ['portal', 'dashboard', 'portal-profile', 'portal-resources', 'portal-referrals', 'portal-login', 'register', 'contact'];
    if (!is_admin() && is_page($portal_pages)) {
        // Load React only on dashboard and referrals (single view). /portal/ redirects to /dashboard/.
        $react_pages = ['dashboard', 'portal-referrals'];
        $load_react = is_page($react_pages);

        add_action('wp_enqueue_scripts', function() use ($load_react) {
            wp_dequeue_style('blocksy-parent-style');
            wp_dequeue_style('blocksy-child-style');
            wp_dequeue_style('elementor-frontend');

            wp_enqueue_style(
                'portal-only-css',
                get_stylesheet_directory_uri() . '/portal/dist/portal.css',
                [],
                filemtime(get_stylesheet_directory() . '/portal/dist/portal.css')
            );

            // On PHP-only portal pages, hide duplicate so only #portal-page-main shows (5 pages)
            // Use both portal-single-view and page-template-* body classes (WordPress always adds page-template-page-{filename})
            if (is_page(['portal-login', 'register', 'portal-profile', 'portal-resources', 'contact'])) {
                $sel = 'body.portal-single-view,body.page-template-page-portal-login-enhanced,body.page-template-page-register-enhanced,body.page-template-page-profile,body.page-template-page-portal-resources,body.page-template-page-contact';
                $hide_css = $sel.' #content>*:not(:has(#portal-page-main)),'.$sel.' main>*:not(:has(#portal-page-main)),'.$sel.' [data-block="content"]>*:not(:has(#portal-page-main)),'.$sel.' .content-area>*:not(:has(#portal-page-main)),'.$sel.' .site-main>*:not(:has(#portal-page-main)),'.$sel.' .ct-container>*:not(:has(#portal-page-main)),'.$sel.' .elementor-location-single{display:none!important}'.$sel.' #portal-page-main{display:block!important;visibility:visible!important}';
                wp_add_inline_style('portal-only-css', $hide_css);
                add_action('wp_footer', function() use ($hide_css) {
                    echo '<style id="portal-hide-duplicate-css">' . $hide_css . '</style>';
                }, 4);
                add_action('wp_footer', function() {
                    $run = "var all=document.querySelectorAll('#portal-page-main');if(!all||!all.length)return;"
                        . "var m=all[all.length-1];"
                        . "for(var k=0;k<all.length-1;k++){all[k].style.setProperty('display','none','important');}"
                        . "var hide=function(el){if(el&&el!==m&&!m.contains(el))el.style.setProperty('display','none','important');};"
                        . "var sel='#content,main,[data-block=content],.content-area,.site-main,.ct-container';"
                        . "document.querySelectorAll(sel).forEach(function(c){var ch=c.children;for(var i=0;i<ch.length;i++)if(!ch[i].contains(m))hide(ch[i]);});"
                        . "var n=m;while(n&&n!==document.body){var p=n.previousElementSibling;while(p){hide(p);p=p.previousElementSibling;}n=n.parentElement;}";
                    echo '<script id="portal-hide-duplicate-js">(function(){function d(){' . $run . '}document.addEventListener("DOMContentLoaded",d);if(document.readyState!=="loading")d();setTimeout(d,150);setTimeout(d,500);})();</script>';
                }, 5);
            }

            if ($load_react) {
                wp_register_script(
                    'portal-only-js',
                    get_stylesheet_directory_uri() . '/portal/dist/portal.js',
                    [],
                    filemtime(get_stylesheet_directory() . '/portal/dist/portal.js'),
                    true
                );
                wp_localize_script('portal-only-js', 'wpPortalData', [
                    'ajaxUrl'    => admin_url('admin-ajax.php'),
                    'nonce'     => wp_create_nonce('portal_nonce'),
                    'restNonce' => wp_create_nonce('wp_rest'),
                    'isLoggedIn' => is_user_logged_in(),
                    'currentPage' => get_post_field('post_name', get_queried_object_id()),
                    'siteUrl'   => home_url('/'),
                    'logoutUrl' => wp_logout_url(home_url('/portal-login/')),
                ]);
                wp_enqueue_script('portal-only-js');
            }
        }, 9999);

        // #portal-root is output from functions.php (portal template) with loading shell — avoid duplicate id + extra white space.
    }
});