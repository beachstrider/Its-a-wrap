<?php

class ItsaWrap {
	protected $loader;
	protected $plugin_name;
	protected $version;

  public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'It\'s a wrap';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-its-a-wrap-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-its-a-wrap-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-its-a-wrap-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-its-a-wrap-public.php';

		$this->loader = new ItsaWrap_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new ItsaWrap_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {
		$plugin_admin = new ItsaWrap_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_itsaWrap_menus' );
	}

	private function define_public_hooks() {
		$plugin_public = new ItsaWrap_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'add_custom_redirect_rules' );
		
		$plugin_public->add_shortcodes();
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}