<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      0.1.0
 *
 * @package    LND_For_WP
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    LND_For_WP
 * @author     RSTMSN
 */
class LND_for_WP_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Instance of LND for making requests
	 */
	protected $lnd;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $lnd ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->lnd = $lnd;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		 wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lnd-for-wp-public.css', array(), $this->version, 'all' );


	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lnd-for-wp-public.js', array( 'jquery' ), $this->version, false );

	    wp_localize_script( $this->plugin_name, 'ajax_object',
	    array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

	}

	/**
	 * Generates a new Lightning Invoice Payment Request
	 * and outputs the payment request string
	 * This is an ajax function, called through JS on the front end
	 */
	public function request_lightning_invoice_ajax() {

		if(
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'] , 'lnd_request_invoice' )
		){
			$invoice_amount = sanitize_text_field( $_REQUEST['amount'] );
			$invoice_memo = sanitize_text_field( $_REQUEST['memo'] );
			echo $this->lnd->get_new_invoice( $invoice_amount, $invoice_memo, true, true );
			wp_die();
		}
	}

	public function is_lightning_invoice_paid_ajax() {

		if(
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'] , 'lnd_invoice_paid' )
		){
			$payment_hash = sanitize_text_field($_REQUEST['payment_hash']);

			if( $this->lnd->invoice_is_paid( $payment_hash ) ){
				echo "true";
			}else{
				echo "false";
			}

			wp_die();
		}
	}

}
