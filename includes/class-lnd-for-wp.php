<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      0.1.0
 *
 * @package    LND_For_WP
 * @subpackage LND_For_WP/includes
 */

class LND_For_WP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The LND instance responsible for communicating with LND node
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      LND_For_WP_Lnd    $lnd    Communicates with remote LND node
	 */

	protected $lnd;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'lnd-for-wp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->configure_lnd();

		add_shortcode( 'lnd', array( $this, 'lnd_wp_shortcode' ));

	}

	/**
	 * Handle Wordpress Shortcode functionality
	 *
	 * @since    0.1.0
	 */
	public function lnd_wp_shortcode( $attributes ){

		if(is_array($attributes)){

			$shortcode_action = $attributes[0];

			switch($shortcode_action){
				case 'on_chain_address':
					return $this->lnd_wp_onchain_address( $attributes );
					break;

				case 'lightning_invoice':
					return $this->lnd_wp_request_invoice( $attributes );
					break;

				case 'current_version':
					return $this->lnd->get_node_version();
					break;
			}

		}

	}

	public function lnd_wp_request_invoice( $attributes ){

		if(is_array($attributes)){
			if($attributes['ajax'] == "true"){

				ob_start();
				include(plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/lnd-for-wp-request-invoice-ajax.php');
				$ajax_html = ob_get_clean();

				return $ajax_html;

			}else{

				$invoice_amount = $attributes['amount'];
				$invoice_memo 	= $attributes['memo'];

				$payment_request = $this->lnd->get_new_invoice($invoice_amount,$invoice_memo);
				$json_data = json_decode($payment_request);

				return $json_data->payment_request;
			}
		}

	}

	/**
	 * Shortcode function handler for new on chain address request
	 *
	 * If the shortcode parameter generate_new is false, function attempts
	 * to load the last on chain address stored in wordpress options.
	 * If this value is empty, or if shortcode parameter generate_new is true,
	 * function sends a request to lnd for a new on chain address.
	 * Finally the wordpress on-chain-address option is updated.
	 *
	 * @since    0.1.0
	 */
	public function lnd_wp_onchain_address( $attributes ){

		if(is_array($attributes)){

			// if generate_new parameter is not set, force new address generation
			// as default behaviour.

			if(empty($attributes['generate_new'])){
				$generate_new = "true";
			}else{
				$generate_new = $attributes['generate_new'];
			}


			if($generate_new === "true"){
				$on_chain_address = $this->lnd->get_node_chain_address();
			}else{

				$saved_chain_address = get_option( 'lnd-on-chain-address' );

				if(!empty($saved_chain_address)){
					$on_chain_address = $saved_chain_address;
				}else{
					$on_chain_address = $this->lnd->get_node_chain_address();
				}
			}

			update_option( 'lnd-on-chain-address', $on_chain_address );

			return $on_chain_address;

		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the admin area.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lnd-for-wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lnd-for-wp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-lnd-for-wp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-lnd-for-wp-public.php';

		/**
		 * Require the main LND interface Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/lnd.class.php';

		/**
		 * Require the QR Generator Class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/phpqrcode/qrlib.php';

		/**
		 * Require the QR Decoder Classes
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/qrdecoder/lib/QrReader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/qrdecoder/QRCodeReader.php';

		$this->loader = new LND_For_WP_Loader();
		$this->lnd = new lnd();
	}

	/**
	 * Pass the saved admin menu configuration options to
	 * the lnd instance
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function configure_lnd() {

		$lnd_hostname = get_option( 'lnd-hostname' );
		$lnd_macaroon = get_option( 'lnd-macaroon' );
		$lnd_conn_timeout = get_option( 'lnd-conn-timeout' );
		$lnd_force_ssl = get_option( 'lnd-force-ssl', false );

		if($lnd_force_ssl){
			$lnd_tls_cert_name = get_option( 'lnd-tls-cert-name' );
		}

		try {
			$this->lnd->set_connection_timeout($lnd_conn_timeout);
			$this->lnd->set_host($lnd_hostname);
			$this->lnd->load_macaroon_from_data($lnd_macaroon);
			$this->lnd->set_curl_log_file(plugin_dir_path( dirname( __FILE__ ) ) . 'logs/curl.log');

			if($lnd_force_ssl){
				$this->lnd->load_tls_cert(plugin_dir_path( dirname( __FILE__ ) ) . 'admin/cert/' . $lnd_tls_cert_name);
				$this->lnd->set_cacert_file(plugin_dir_path( dirname( __FILE__ ) ) . 'admin/cert/cacert.pem');
			}

		}catch (Exception $e){

		}
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new LND_for_WP_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new LND_For_WP_Admin( $this->get_plugin_name(), $this->get_version(), $this->lnd );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_wp_admin_menu' );
		$this->loader->add_action( 'wp_ajax_lnd_menu_update_default_ajax', $plugin_admin, 'lnd_menu_update_default_ajax' );
		$this->loader->add_action( 'wp_ajax_lnd_decode_qr_ajax', $plugin_admin, 'lnd_decode_qr_ajax' );

		if( $_REQUEST['page'] == $this->get_plugin_name() ){
			$this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'admin_footer' );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new LND_For_WP_Public( $this->get_plugin_name(), $this->get_version(), $this->lnd );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_request_lightning_invoice_ajax', $plugin_public, 'request_lightning_invoice_ajax' );
		$this->loader->add_action( 'wp_ajax_is_lightning_invoice_paid_ajax', $plugin_public, 'is_lightning_invoice_paid_ajax' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.1.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
