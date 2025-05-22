<?php

namespace ElementPack\Builder;

defined('ABSPATH') || exit;

use ElementPack\Includes\Builder\Meta;

class Activator {
    public static $instance = null;

    protected $templates;
    public $header_template;
    public $footer_template;

    protected $current_theme;
    protected $current_template;

    protected $post_type = 'bdt-template-builder';

    public function __construct() {
        add_action('wp', array($this, 'hooks'));
    }

    public function hooks() {
        $this->current_template = basename(get_page_template_slug());
        if ($this->current_template == 'elementor_canvas') {
            return;
        }

        $this->current_theme = get_template();
        switch ($this->current_theme) {
            case 'astra':
                new Themes_Hooks\Astra(self::template_ids());
                break;

                // case 'neve':
                //     // new Themes_Hooks\Neve(self::template_ids());
                //     break;

                // case 'generatepress':
                // case 'generatepress-child':
                //     // new Themes_Hooks\Generatepress(self::template_ids());
                //     break;

                // case 'oceanwp':
                // case 'oceanwp-child':
                //     // new Themes_Hooks\Oceanwp(self::template_ids());
                //     break;

                // case 'bb-theme':
                // case 'bb-theme-child':
                //     // new Themes_Hooks\Bbtheme(self::template_ids());
                //     break;

                // case 'genesis':
                // case 'genesis-child':
                //     // new Themes_Hooks\Genesis(self::template_ids());
                //     break;

                // case 'twentynineteen':
                //     // new Themes_Hooks\TwentyNineteen(self::template_ids());
                //     break;

                // case 'my-listing':
                // case 'my-listing-child':
                //     // new Themes_Hooks\MyListing(self::template_ids());
                //     break;

            default:
                new Themes_Hooks\Themes_Support(self::template_ids());
                break;
        }
    }

    public static function template_ids() {
        $cached = wp_cache_get('bdthemes_template_builder_template_ids');
        if (false !== $cached) {
            return $cached;
        }

        $instance = self::instance();
        $instance->the_filter();

        $ids = array(
            $instance->header_template,
            $instance->footer_template,
        );

        // echo '========ids========<br>';
        // print_r($ids);
        
        // if ($instance->header_template != null) {
        //     bdt_templates_render_elementor_content_css($instance->header_template);
        // }

        // if ($instance->footer_template != null) {
        //     bdt_templates_render_elementor_content_css($instance->footer_template);
        // }

        wp_cache_set('bdthemes_template_builder_template_ids', $ids);
        return $ids;
    }

    protected function the_filter() {
        $arg    = array(
            'posts_per_page' => -1,
            'orderby'        => 'id',
            'order'          => 'DESC',
            'post_status'    => 'publish',
            'post_type'      => $this->post_type,
            'meta_query'     => array(
                array(
                    'key'     => '_bdthemes_builder_template_type',
                    'value'   => array('themes|header', 'themes|footer'),
                    'compare' => 'IN', // Use 'IN' to match multiple values
                ),
            ),
        );

        $this->templates = get_posts($arg);

        // more conditions can be triggered at once
        // don't use switch case
        // may impliment and callable by dynamic class in future

        // entire site
        if (!is_admin()) {
            $filters = array(
                array(
                    'key'   => 'condition_a',
                    'value' => 'entire_site',
                ),
            );
            $this->get_header_footer($filters);
        }
    }

    protected function get_header_footer($filters) {
        $template_id = array();

        if ($this->templates != null) {
            foreach ($this->templates as $template) {
                $template    = $this->get_full_data($template);
                $match_found = true;

                $meta = get_post_meta($template['ID']);

                $templateType = isset($meta[Meta::TEMPLATE_TYPE][0]) ? $meta[Meta::TEMPLATE_TYPE][0] : '';
                $enabledTemplate = strtolower(Meta::TEMPLATE_ID . $templateType);
                $enabledTemplate = get_option($enabledTemplate);

                
                /**
                 * WPML Language Check
                 */
                if (defined('ICL_LANGUAGE_CODE')) :
                    $current_lang = apply_filters('wpml_post_language_details', null, $template['ID']);

                    if (!empty($current_lang) && !$current_lang['different_language'] && ($current_lang['language_code'] == ICL_LANGUAGE_CODE)) :
                        $template_id[$template['type']] = $template['ID'];
                    endif;
                endif;

                foreach ($filters as $filter) {
                    if ($filter['key'] == 'condition_singular_id') {
                        $ids = explode(',', $template[$filter['key']]);
                        if (!in_array($filter['value'], $ids)) {
                            $match_found = false;
                        }
                    } elseif ($template[$filter['key']] != $filter['value']) {
                        $match_found = false;
                    }
                    if ($filter['key'] == 'condition_a' && $template[$filter['key']] == 'singular' && count($filters) < 2) {
                        $match_found = false;
                    }
                }

                if (!$enabledTemplate) {
                    $match_found = false;
                }

                if ($match_found == true) {
                    if ($template['type'] == 'themes|header') {
                        $this->header_template = isset($template_id['themes|header']) ? $template_id['themes|header'] : $template['ID'];
                    }
                
                    if ($template['type'] == 'themes|footer') {
                        $this->footer_template = isset($template_id['themes|footer']) ? $template_id['themes|footer'] : $template['ID'];
                    }
                }
                
            }
        }
    }

    protected function get_full_data($post) {
        if ($post != null) {
            return array_merge(
                (array) $post,
                array(
                    'type'                  => get_post_meta($post->ID, '_bdthemes_builder_template_type', true),
                    'condition_a'           => get_post_meta($post->ID, '_bdthemes_builder_template_condition_a', true),
                    /**
                     * todo: Condition for future
                     */
                    'condition_singular'    => get_post_meta($post->ID, '_bdthemes_builder_condition_singular', true),
                    'condition_singular_id' => get_post_meta($post->ID, '_bdthemes_builder_condition_singular_id', true),
                )
            );
        }
    }

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

Activator::instance();
