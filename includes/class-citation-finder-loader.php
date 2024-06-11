<?php

class Citation_Finder_Loader
{
    protected $admin;
    protected $public;
    protected $rest_api;

    public function __construct()
    {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_rest_api_hooks();
        // this->set_locale(); // Not implemented (soon to be implemented)
    }

    private function load_dependencies()
    {
        require_once CF_PLUGIN_DIR . 'includes/class-citation-finder-admin.php';
        require_once CF_PLUGIN_DIR . 'includes/class-citation-finder-public.php';
        require_once CF_PLUGIN_DIR . 'includes/class-citation-finder-rest-api.php';
    }

    // private function set_locale(){
    // }

    private function define_admin_hooks()
    {
        $this->admin = new Citation_Finder_Admin();
        add_action('admin_menu', array($this->admin, 'citation_finder_menu'));
        add_action('admin_init', array($this->admin, 'citation_finder_settings'));
        // Add other admin hooks here
    }

    private function define_public_hooks()
    {
        $this->public = new Citation_Finder_Public();
        add_action('init', array($this->public, 'register_block_assets'));
        add_action('wp_head', array($this->public, 'remove_header_footer_css'));
    }

    private function define_rest_api_hooks()
    {
        $this->rest_api = new Citation_Finder_REST_API();
        add_action('rest_api_init', array($this->rest_api, 'register_routes'));
    }

    public function run()
    {
        // Put any code here that needs to be executed to fully initialize the plugin.
        // For now, leave it empty if there's nothing additional needed.
    }
}