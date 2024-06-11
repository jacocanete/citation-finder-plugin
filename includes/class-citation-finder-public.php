<?php

class Citation_Finder_Public
{
    public function __construct()
    {
        add_action('wp_head', array($this, 'remove_header_footer_css'));
        add_action('init', array($this, 'register_block_assets'));
    }

    public function register_block_assets()
    {
        wp_register_style('citation-finder-css', CF_PLUGIN_URL . 'build/index.css');
        wp_register_script('citation-finder-blocks', CF_PLUGIN_URL . 'build/index.js', array('wp-blocks', 'wp-element', 'wp-editor'));
        register_block_type(
            'citation-finder/main',
            array(
                'editor_script' => 'citation-finder-blocks',
                'editor_style' => 'citation-finder-css',
                'render_callback' => array($this, 'citation_finder_render')
            )
        );
    }

    public function citation_finder_render()
    {
        if (!is_admin()) {
            wp_enqueue_script('citation-finder-frontEnd', CF_PLUGIN_URL . 'build/frontend.js', array('wp-element', 'wp-components', 'wp-i18n'));
            wp_enqueue_style('citation-finder-frontEnd-css', CF_PLUGIN_URL . 'build/frontend.css');

            $site_url = array(
                'root_url' => get_site_url(),
                'nonce' => wp_create_nonce('wp_rest')
            );

            $this->page_id_to_modify = get_the_ID();

            // Localize the script with new data (site_url)
            wp_localize_script('citation-finder-frontEnd', 'site_url', $site_url);

            // Enqueue Bootstrap CSS
            wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');

            // Enqueue Bootstrap JavaScript
            wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'), null, true);
        }

        add_filter('script_loader_tag', function ($tag, $handle) {
            if ('citation-finder-frontEnd' !== $handle) {
                return $tag;
            }

            return str_replace(' src', ' defer="defer" src', $tag);
        }, 10, 2);

        ob_start(); ?>
        <div class="citation-finder-update">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </div>
        <?php return ob_get_clean();
    }

    public function remove_header_footer_css()
    {
        // Ensure the page_id_to_modify property is available
        if (isset($this->page_id_to_modify) && is_page($this->page_id_to_modify)) {
            echo '<style>
                    header { display: none; }
                    footer { display: none; }
                  </style>';
        }
    }
}