<?php

class Citation_Finder_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'citation_finder_menu'));
        add_action('admin_init', array($this, 'citation_finder_settings'));
    }

    public function citation_finder_menu()
    {
        add_options_page('Citation Finder', 'Citation Finder', 'manage_options', 'citation-finder', array($this, 'citation_finder_page'));
    }

    public function citation_finder_page()
    { ?>
        <div class="wrap">
            <h2>Citation Finder Settings</h2>
            <h3>Credentials</h3>
            <form action="options.php" method="POST">
                <?php
                settings_fields('citation-finder-credentials-group');
                do_settings_sections('citation-finder');
                submit_button();
                ?>
            </form>
            <h3>Version</h3>
            <p>Version: <?php $this->echo_plugin_version(); ?></p>
        </div>
    <?php }

    public function citation_finder_settings()
    {
        // Register settings
        add_settings_section('citation-finder-credentials-section', null, null, 'citation-finder');

        // Username field
        add_settings_field('citation-finder-username', 'API login', array($this, 'usernameHTML'), 'citation-finder', 'citation-finder-credentials-section');
        register_setting(
            'citation-finder-credentials-group',
            'citation-finder-username',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        // Password field
        add_settings_field('-password', 'API password', array($this, 'passwordHTML'), 'citation-finder', 'citation-finder-credentials-section');
        register_setting(
            'citation-finder-credentials-group',
            'citation-finder-password',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1234567890'
            )
        );

        // Use credits field
        add_settings_field('citation-finder-use-credits', 'Use credits', array($this, 'useCreditsHTML'), 'citation-finder', 'citation-finder-credentials-section');
        register_setting(
            'citation-finder-credentials-group',
            'citation-finder-use-credits',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '0'
            )
        );
    }

    // Use credits HTML
    function useCreditsHTML()
    { ?>
        <select name="citation-finder-use-credits" value>
            <option value="0" <?php selected(get_option('citation-finder-use-credits'), '0') ?>>False</option>
            <option value="1" <?php selected(get_option('citation-finder-use-credits'), '1') ?>>True</option>
        </select>
    <?php }

    // Username HTML
    function usernameHTML()
    { ?>
        <input type="text" name="citation-finder-username" value="<?php echo get_option('citation-finder-username'); ?>" />
    <?php }

    // Password HTML
    function passwordHTML()
    { ?>
        <input type="password" name="citation-finder-password" value="<?php echo get_option('citation-finder-password'); ?>" />
    <?php }

    function echo_plugin_version()
    {
        $plugin_data = get_plugin_data(CF_PLUGIN_DIR . 'citation-finder.php');
        echo $plugin_data['Version'];
    }
}