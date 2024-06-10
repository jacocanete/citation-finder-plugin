<?php

/*
Plugin Name: LocalWiz Enhancements
Description: Enhancements for LocalWiz
Version: 1.2
Author: Jaco Gagarin Canete
Author URI: jaco-portfolio.me
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class LocalWizEnhancements
{
    function __construct()
    {
        $this->page_id_to_modify = null;

        add_action('admin_menu', array($this, 'localwiz_enhancements_menu'));
        add_action('admin_init', array($this, 'localwiz_enhancements_settings'));
        add_action('init', array($this, 'localwiz_enhancements_assets'));
        add_action('wp_head', array($this, 'remove_header_footer_css'));

        // Add REST API endpoint http://gosystem7.local/wp-json/localwiz-enhancements/v1/citation-finder
        add_action('rest_api_init', function () {
            register_rest_route(
                'localwiz-enhancements/v1',
                'citation-finder',
                array(
                    'methods' => WP_REST_SERVER::READABLE,
                    'callback' => array($this, 'localwiz_enhancements_citation_finder')
                )
            );
        });
    }

    function remove_header_footer_css()
    {
        if (is_page($this->page_id_to_modify)) {
            echo '<style>
                    header { display: none; }
                    footer { display: none; }
                  </style>';
        }
    }

    // REST API endpoint callback
    function localwiz_enhancements_citation_finder($keyword)
    {
        $curl = curl_init();

        $postFields = json_encode(
            array(
                array(
                    "keyword" => sanitize_text_field($keyword['kw']),
                    "location_code" => 2840,
                    "language_code" => "en",
                    "device" => "desktop",
                    "os" => "windows",
                    "depth" => 100
                )
            )

        );

        $useCredits = false;

        if (get_option('localwiz-enhancements-use-credits') == '1') {
            $useCredits = true;
        } else {
            $useCredits = false;
        }

        $apiUrl = $useCredits ? 'https://api.dataforseo.com/v3/serp/google/organic/live/advanced' : 'https://sandbox.dataforseo.com/v3/serp/google/organic/live/advanced';

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic " . base64_encode(get_option('localwiz-enhancements-username') . ":" . get_option('localwiz-enhancements-password')),
                    "Content-Type: application/json"
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);

        $responseArray = json_decode($response, true);

        wp_send_json($responseArray);
    }

    // Enqueue assets
    function localwiz_enhancements_assets()
    {
        wp_register_style('localwiz-enhancements-css', plugin_dir_url(__FILE__) . 'build/index.css');
        wp_register_script('localwiz-enhancements-blocks', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks', 'wp-element', 'wp-editor'));
        register_block_type(
            'localwiz-enhancements/citation-finder',
            array(
                'editor_script' => 'localwiz-enhancements-blocks',
                'editor_style' => 'localwiz-enhancements-css',
                'render_callback' => array($this, 'localwiz_enhancements_render')
            )
        );
    }

    // Render block
    function localwiz_enhancements_render($attributes)
    {
        if (!is_admin()) {
            wp_enqueue_script('localwiz-enhancements-frontEnd', plugin_dir_url(__FILE__) . 'build/frontend.js', array('wp-element', 'wp-components', 'wp-i18n'));
            wp_enqueue_style('localwiz-enhancements-frontEnd-css', plugin_dir_url(__FILE__) . 'build/frontend.css');

            $site_url = array(
                'root_url' => get_site_url(),
                'nonce' => wp_create_nonce('wp_rest')
            );

            $this->page_id_to_modify = get_the_ID();

            wp_localize_script('localwiz-enhancements-frontEnd', 'site_url', $site_url);

            // Enqueue Bootstrap CSS
            wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');

            // Enqueue Bootstrap JavaScript
            wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js', array('jquery'), null, true);
        }

        add_filter('script_loader_tag', function ($tag, $handle) {
            if ('localwiz-enhancements-frontEnd' !== $handle) {
                return $tag;
            }

            return str_replace(' src', ' defer="defer" src', $tag);
        }, 10, 2);

        ob_start(); ?>
        <div class="citation-finder-update">hey</div>
        <?php return ob_get_clean();
    }

    // Settings
    function localwiz_enhancements_settings()
    {
        add_settings_section('localwiz-enhancements-credentials-section', null, null, 'localwiz-enhancements');
        // Username
        add_settings_field('localwiz-enhancements-username', 'API login', array($this, 'usernameHTML'), 'localwiz-enhancements', 'localwiz-enhancements-credentials-section');
        register_setting(
            'localwiz-enhancements-credentials-group',
            'localwiz-enhancements-username',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Password
        add_settings_field('localwiz-enhancements-password', 'API password', array($this, 'passwordHTML'), 'localwiz-enhancements', 'localwiz-enhancements-credentials-section');
        register_setting(
            'localwiz-enhancements-credentials-group',
            'localwiz-enhancements-password',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1234567890'
            )
        );

        // Use credits
        add_settings_field('localwiz-enhancements-use-credits', 'Use credits', array($this, 'useCreditsHTML'), 'localwiz-enhancements', 'localwiz-enhancements-credentials-section');
        register_setting(
            'localwiz-enhancements-credentials-group',
            'localwiz-enhancements-use-credits',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '0'
            )
        );
    }

    // Use credits HTML
    function useCreditsHTML()
    { ?>
        <select name="localwiz-enhancements-use-credits" value>
            <option value="0" <?php selected(get_option('localwiz-enhancements-use-credits'), '0') ?>>False</option>
            <option value="1" <?php selected(get_option('localwiz-enhancements-use-credits'), '1') ?>>True</option>
        </select>
    <?php }

    // Username HTML
    function usernameHTML()
    { ?>
        <input type="text" name="localwiz-enhancements-username"
            value="<?php echo get_option('localwiz-enhancements-username'); ?>" />
    <?php }

    // Password HTML
    function passwordHTML()
    { ?>
        <input type="password" name="localwiz-enhancements-password"
            value="<?php echo get_option('localwiz-enhancements-password'); ?>" />
    <?php }

    function localwiz_enhancements_menu()
    {
        add_options_page('LocalWiz Enhancements', 'LocalWiz Enhancements', 'manage_options', 'localwiz-enhancements', array($this, 'localwiz_enhancements_page'));
    }

    function localwiz_enhancements_page()
    { ?>
        <div class="wrap">
            <h2>LocalWiz Enhancements Settings</h2>
            <h3>Credentials</h3>
            <form action="options.php" method="POST">
                <?php
                settings_fields('localwiz-enhancements-credentials-group');
                do_settings_sections('localwiz-enhancements');
                submit_button();
                ?>
            </form>
        </div>
    <?php }
}

$localWizEnhancements = new LocalWizEnhancements();


