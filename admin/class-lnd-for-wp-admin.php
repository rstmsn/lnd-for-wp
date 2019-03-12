<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      0.1.0
 *
 * @package    LND_For_WP
 */

class LND_For_WP_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Instance of LND for making requests
	 */
	private $lnd;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.1.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $lnd ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->lnd = $lnd;

	}

	/**
	 * Add lnd-for-wp to the Wordpress admin menu
	 *
	 * @since    0.1.0
	 */
	public function setup_wp_admin_menu() {

		$icon = plugins_url( 'img/lightning.png', __FILE__ );

		add_menu_page( "LND For WP", 'LND For WP', 'manage_options', 'lnd-for-wp', array( $this , 'admin_index') ,$icon );

	}

	/**
	 * This function is an AJAX handler that returns the state of the
	 * Node Configuration Menu on the Admin Front End. The menu is either hidden or visible.
	 *
	 * The Configuration Menu can either be expanded or collapsed.
	 *
	 * @since    0.1.0
	 */
	public function lnd_menu_update_default_ajax(){

		$menu_default = sanitize_text_field( $_REQUEST['hide_menu'] );

		if( $menu_default == "true" ){
			update_option( 'lnd-hide-config', true );
		}else{
			update_option( 'lnd-hide-config', false );
		}

		wp_die();
	}

	/**
	 * Decodes a QR code from base64 image
	 *
	 * @since    0.1.0
	 */
	public function lnd_decode_qr_ajax(){

		$image_data = sanitize_text_field( $_REQUEST['qr_payload'] );
		$QRCodeReader = new Libern\QRCodeReader\QRCodeReader();
		$qrcode_text = $QRCodeReader->decode( $image_data );

		if( substr( $qrcode_text, 0, 10 ) == "lightning:"){
			$qrcode_text = substr( $qrcode_text , 10 );
		}

		echo $qrcode_text;

		wp_die();
	}

	/**
	 * This function serves the plugins initial admin area content
	 *
	 * @since    0.1.0
	 */
	public function admin_index() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-display.php';
	}

	public function handle_funding_address() {

		// load the most recently generated on chain wallet address
		$on_chain_funding_address = get_option( 'lnd-on-chain-address' );
		// if no on chain funding address has been previously stored, or if
		// user has requested the generation of a new address, generate a new
		// on chain address and store it as a wordpress option
		if((
			isset( $_REQUEST['new'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			$_REQUEST['new'] == "Y" &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'], 'lnd_gen_funding_address' )
			) ||
			!$on_chain_funding_address
		){
			$on_chain_funding_address =	$this->lnd->get_node_chain_address();
			update_option( 'lnd-on-chain-address', $on_chain_funding_address );
		}

		return $on_chain_funding_address;

	}

	/**
	 * This function is called when the 'Update Node Configuration' form is posted
	 * from the Plugin Admin Panel
	 *
	 * @since    0.1.0
	 */
	public function handle_settings_form_post() {

		if(
			isset( $_REQUEST['lnd-update-settings'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			isset( $_REQUEST['lnd-conn-timeout'] ) &&
			is_numeric( $_REQUEST['lnd-conn-timeout'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'], 'lnd_update_node_settings' ) &&
			$_REQUEST['lnd-update-settings'] == "Y"
		){

			update_option( 'lnd-hostname', sanitize_text_field( $_POST['lnd-hostname'] ) );
			update_option( 'lnd-conn-timeout', sanitize_text_field( $_POST['lnd-conn-timeout'] ) );

			if(isset($_POST['lnd-force-ssl']) && $_POST['lnd-force-ssl'] == "on"){
				update_option( 'lnd-force-ssl', true );
			}else{
				update_option( 'lnd-force-ssl', false );
			}

			if(
				!empty( $_FILES ) &&
				current_user_can( 'upload_files' )
			){

				/* process macaroon file upload */
				if( $_FILES['lnd-attach-macaroon']['error'] != UPLOAD_ERR_NO_FILE ){

					$macaroon_file_name = sanitize_file_name( $_FILES["lnd-attach-macaroon"]['name'] );

					// check upload file size isnt above 400 bytes
					if( $_FILES['lnd-attach-macaroon']['size'] > 400 ){
						$this->redirect_with_message( "", $macaroon_file_name . __( " file size is too large", $this->plugin_name ) . "...", true );
						return;
					}

					// check if the upload file is of the correct type
					$allowed_mimes = array( 'macaroon' => 'application/octet-stream' );
					$file_info = wp_check_filetype( basename( $_FILES['lnd-attach-macaroon']['name'] ) , $allowed_mimes );

					if ( !empty( $file_info['type'] ) ) {
						$macaroon_data = file_get_contents( $_FILES["lnd-attach-macaroon"]["tmp_name"] );
						$macaroon_hex = strtoupper( bin2hex( $macaroon_data ) );
						update_option( 'lnd-macaroon-name', $macaroon_file_name );
						update_option( 'lnd-macaroon', $macaroon_hex );
					}else{
						$this->redirect_with_message( "", $macaroon_file_name . __( " has an invalid file type", $this->plugin_name ) . "...", true );
						return;
					}
				}

				/* process tls certificate file upload */
				if( $_FILES['lnd-attach-tls-cert']['error'] != UPLOAD_ERR_NO_FILE ){

					$tls_file_name = sanitize_file_name( $_FILES["lnd-attach-tls-cert"]['name'] );

					// check upload file size isnt above 1024 bytes
					if( $_FILES['lnd-attach-tls-cert']['size'] > 1024 ){
						$this->redirect_with_message( "", $tls_file_name . __( " file size is too large", $this->plugin_name) . "...", true );
						return;
					}

					// check if the upload file is of the correct type
					$allowed_mimes = array( 'cert' => 'application/octet-stream' );
					$file_info = wp_check_filetype( basename( $_FILES['lnd-attach-tls-cert']['name'] ) , $allowed_mimes );


					if ( !empty( $file_info['type'] ) ) {
						$tls_cert_path = plugin_dir_path( __FILE__ ) . 'cert/' . $tls_file_name;
						move_uploaded_file( $_FILES["lnd-attach-tls-cert"]["tmp_name"] , $tls_cert_path );
						update_option( 'lnd-tls-cert-name', $tls_file_name );
					}else {
						$this->redirect_with_message( "", $tls_file_name . __( " has an invalid file type", $this->plugin_name ) . "...", true );
						return;
					}

				}
			}

			$this->redirect_with_message( "", __( "Successfully updated node configuration", $this->plugin_name ) . "...", true );
			return;
		}

		if(
			isset( $_POST['lnd-mute-ssl-warning'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'], 'lnd_mute_ssl_warning' ) &&
			$_REQUEST['lnd-mute-ssl-warning'] == "Y"
		){
			update_option( 'lnd-ssl-warn', 'false' );
			$this->redirect_with_message( "", __( "SSL Warning dismissed", $this->plugin_name) . "...", true );
		}
	}

	/*
	 * Returns an object of standard class containing the remote lnd node
	 * connection & configuration details
	 *
	 * @since    0.1.0
	 */
	public function load_default_settings(){

		$settings = new stdClass();
		$settings->lnd_hostname = get_option( 'lnd-hostname', '172.0.0.1:8080' );
		$settings->lnd_macaroon = get_option( 'lnd-macaroon', getcwd() );
		$settings->lnd_macaroon_name = get_option( 'lnd-macaroon-name', 'None' );
		$settings->lnd_ssl_warn = get_option( 'lnd-ssl-warn', 'true' );
		$settings->lnd_conn_timeout = get_option( 'lnd-conn-timeout', '8' );
		$settings->hide_config = get_option( 'lnd-hide-config', false );
		$settings->lnd_tls_cert_name = get_option( 'lnd-tls-cert-name', 'None' );
		$settings->lnd_force_ssl = get_option( 'lnd-force-ssl', false );

		return $settings;

	}

	/*
	 * Returns view content for the relevant page
	 *
	 * @since    0.1.0
	 */
	public function render_console_content(){

		if(
			isset( $_REQUEST['f'] ) &&
			$_REQUEST['f'] == "unlock" &&
			!$this->lnd->is_node_reachable()
		){

			require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-unlock.php';

		}else if( $this->lnd->is_node_reachable() ){

			if(isset( $_REQUEST['f'] )){
				$lnd_wp_page_function = sanitize_text_field( $_REQUEST['f'] );
			}else{
				$lnd_wp_page_function = 'wallet';
			}

			switch( $lnd_wp_page_function ){
				case 'unlock':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-unlock.php';
					break;
				case 'funding':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-funding.php';
					break;
				case 'request':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-request.php';
					break;
				case 'payments':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-payments.php';
					break;
				case 'peers':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-peers.php';
					break;
				case 'channels':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-channels.php';
					break;
				case 'network':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-network.php';
					break;
				case 'wallet':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-wallet.php';
					break;
				case 'shortcodes':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-shortcodes.php';
					break;
				case 'transactions':
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-transactions.php';
					break;
				default:
					require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-wallet.php';
			}
		}else{
			require_once plugin_dir_path( __FILE__ ) . 'partials/lnd-for-wp-admin-unreachable.php';
		}
	}

	/**
	 * Redirect to a particular page by submitting a POST form.
	 * include a post variable 'message'.
	 *
	 * @since    0.1.0
	 */
	public function redirect_with_message($page, $message, $core_message = false) {

		$plugin_page = sanitize_text_field( $_REQUEST['page'] );
		$field_name = $core_message ? 'core_message' : 'message';

		$html = "<form method=\"post\" action=\"admin.php?page=$plugin_page&f=$page\" id=\"ln-redirect\">";
		$html .= "<input type=\"hidden\" name=\"$field_name\" value=\"$message\" />";
		$html .= "</form>";
		$html .= "<script>document.getElementById('ln-redirect').submit();</script>";

		echo $html;
	}

	/**
	 * This form is called when the 'Unlock Wallet' form is posted
	 * from the Plugin Admin Panel
	 *
	 * @since    0.1.0
	 */
	public function handle_form_unlock_wallet() {

		if(
			isset( $_POST['lnd-unlock-wallet'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'] , 'lnd-unlock-wallet' ) &&
			$_POST['lnd-unlock-wallet'] == "Y"
		){

			$wallet_password = base64_encode( sanitize_text_field( $_POST['lnd-wallet-password'] ));
			$response = $this->lnd->unlock_wallet( $wallet_password );

			// sleep for 4 seconds to give node a chance to unlock and begin responding to
			// rpc calls
			sleep(4);

			if(isset( $response->error )){
				$this->redirect_with_message( "unlock", __( ucfirst( $response->error ), $this->plugin_name ) . "..." );
			}else{
				$this->redirect_with_message( "unlock", __( "Sent wallet unlock request", $this->plugin_name ) . "..." );
			}

		}

	}

	/**
	 * This function is called when the 'Generate Payment Request' form is posted
	 * from the Plugin Admin Panel
	 *
	 * @since    0.1.0
	 */
	public function handle_payment_request_form_submit(){

		if(
			isset( $_REQUEST['lnd-request-submit'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			$_REQUEST['lnd-request-submit'] == "Y" &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'], 'lnd_request_invoice' )
		){

			if(
				isset( $_REQUEST['lnd-request-amount'] ) &&
				!empty( $_REQUEST['lnd-request-amount'] ) &&
				is_numeric( $_REQUEST['lnd-request-amount'] ) &&
				$_REQUEST['lnd-request-amount'] > 0
			){

				$request_amount = sanitize_text_field( $_REQUEST['lnd-request-amount'] );
				$request_memo = sanitize_text_field( $_REQUEST['lnd-request-memo'] );
				$payment_request = $this->lnd->get_new_invoice( $request_amount, $request_memo, false );

				return $payment_request;

			}else{
				$this->redirect_with_message( "request", __( "Generation failed. Invalid Satoshi amount" , $this->plugin_name) );
			}

		}

		return false;

	}

	/**
	 * Handle admin area form post logic to open a new channel
	 *
	 * @since    0.1.0
	 */
	public function handle_open_channel_form() {

		if(
			isset( $_REQUEST['lnd-open-channel-confirm'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'] , 'lnd-open-channel' ) &&
			$_REQUEST['lnd-open-channel-confirm'] == "Y"
		){

			if(!isset( $_REQUEST['lnd-open-channel-sat'] ) || !is_numeric( $_REQUEST['lnd-open-channel-sat'] )){
				$fail_sanity_check = true;
			}

			if(!isset( $_REQUEST['lnd-open-channel-pubkey'] ) || empty( $_REQUEST['lnd-open-channel-pubkey'] )){
				$fail_sanity_check = true;
			}

			if( $fail_sanity_check ){
				$this->redirect_with_message( "channels", __( "Invalid Satoshi amount or public key", $this->plugin_name ) );
			}else{

				$satoshi_amount = sanitize_text_field( $_REQUEST['lnd-open-channel-sat'] );
				$node_pub_key = sanitize_text_field( $_REQUEST['lnd-open-channel-pubkey'] );
				$response = $this->lnd->open_channel( $satoshi_amount, $node_pub_key );

				if(isset( $response->error )){
					$this->redirect_with_message( "channels", __( ucfirst( $response->error ), $this->plugin_name ) );
				}else{
					$this->redirect_with_message( "channels", __( "New channel opened" ) , $this->plugin_name );
				}
			}
		}

	}

	/**
	 * Handle admin area form post logic to close a channel
	 *
	 * @since    0.1.0
	 */
	public function handle_close_channel_form() {

		if(
			isset( $_REQUEST['lnd-close-channel'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'], 'lnd-close-channel' ) &&
			$_REQUEST['lnd-close-channel'] == "Y"
		){

			if(isset( $_REQUEST['lnd-close-channel-confirm'] ) && $_REQUEST['lnd-close-channel-confirm'] == "Y"){

				if(isset( $_REQUEST['lnd-close-channel-id'] ) && !empty( $_REQUEST['lnd-close-channel-id'] )){

					$channel_id = sanitize_text_field( $_REQUEST['lnd-close-channel-id'] );
					$this->lnd->close_channel( $channel_id );
					$this->redirect_with_message( "channels", __( "Requested channel close", $this->plugin_name ));
				}else{
					$this->redirect_with_message( "channels", __( "Invalid channel selected", $this->plugin_name ));
				}

			}

			return true;

		}

		return false;

	}

	/**
	 * This function is called when the 'Add New Peer Connection' form is posted
	 * from the Plugin Admin Panel
	 *
	 * @since    0.1.0
	 */
	public function handle_add_peer_form() {

		if(
			isset( $_REQUEST['lnd-add-peer-confirm'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'] , 'lnd-add-peer' ) &&
			$_REQUEST['lnd-add-peer-confirm'] == "Y"
		){

			$peer_address = sanitize_text_field( $_REQUEST['lnd-add-peer-id'] );

			if(!empty($peer_address)){

				$peer_details = explode( "@", $peer_address );

				if(!is_array( $peer_details )){
					$this->redirect_with_message( "peers", __( "Invalid peer address syntax", $this->plugin_name ) );
				}else{
					$peer_pubkey = $peer_details[0];
					$peer_host = $peer_details[1];
					$response = $this->lnd->connect_peer( $peer_pubkey, $peer_host );

					if(isset( $response->error )){
						$this->redirect_with_message( "peers", __(ucfirst( $response->error ), $this->plugin_name) );
					}else{
						$this->redirect_with_message( "peers", __( "Successfully connected to peer", $this->plugin_name) . "..." );
					}
				}

			}else{
				$this->redirect_with_message( "peers", __( "Unable to connect. Invalid node address"), $this->plugin_name );
			}

		}

	}

	/**
	 * This function is called when the 'Disconnect Remote Peer' form is posted
	 * from the Plugin Admin Panel

	 * @since    0.1.0
	 */
	public function handle_disconnect_peer_form() {

		if(
			isset( $_REQUEST['lnd-disconnect-peer'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'] , 'lnd-disconnect-peer' ) &&
			$_REQUEST['lnd-disconnect-peer'] == "Y"
		){

			if(isset( $_REQUEST['lnd-disconnect-peer-confirm'] ) && $_REQUEST['lnd-disconnect-peer-confirm'] == "Y"){
				$peer_pubkey = sanitize_text_field( $_REQUEST['lnd-disconnect-peer-id'] );
				$response = $this->lnd->disconnect_peer( $peer_pubkey );

				if(isset( $response->error )){
					$this->redirect_with_message( "peers", __(ucfirst( $response->error ), $this->plugin_name ) );
				}else{
					$this->redirect_with_message( "peers", __( "Successfully disconnected from peer", $this->plugin_name ) );
				}

			}else{

				$peer_pubkey = sanitize_text_field( $_REQUEST['lnd-disconnect-peer-id'] );

				if(!empty( $peer_pubkey )){
					return true;
				}

				return false;
			}

		}

		return false;

	}

	/**
	 * This function is called when the 'Pay Lightning Invoice' form is posted
	 * from the Plugin Admin Panel

	 * @since    0.1.0
	 */
	public function handle_pay_lightning_invoice_form(){

		if(
			isset($_REQUEST['lnd-pay-invoice']) &&
			isset($_REQUEST['lnd-post-nonce']) &&
			wp_verify_nonce($_REQUEST['lnd-post-nonce'], 'lnd_confirm_pay_invoice') &&
			$_REQUEST['lnd-pay-invoice'] == "Y"
		){

			$invoice = sanitize_text_field( $_REQUEST['lightning-invoice'] );

			if(empty( $invoice )){
				$this->redirect_with_message( "payments", __( "Payment failed. Invalid invoice supplied", $this->plugin_name ));
			}else{

				// check if the decoded invoice has been confirmed for payment
				if(isset( $_REQUEST['lnd-pay-confirm'] ) && $_REQUEST['lnd-pay-confirm'] == "true" ){

					$response =	$this->lnd->pay_invoice( $invoice );

					if(isset( $response->payment_error ) || isset( $response->error )){
						$this->redirect_with_message( "payments", __( "Payment failed: ", $this->plugin_name ) . $response->payment_error );
					}else{
						$this->redirect_with_message( "payments", __( "Payment success", $this->plugin_name ));
					}

				}else{
					$decoded_invoice = $this->lnd->decode_invoice( $invoice );
					return $decoded_invoice;
				}

			}

		}

		return false;
	}

	/**
	 * When the search network graph form is posted, this function handles the logic
	 * that requests the full graph description from LND, then searches this payload for
	 * nodes matched based on IP, Alias or Public Key.
	 *
	 * This function transforms both search terms and graph node details to lowercase
	 * to increase search hit rate.
	 *
	 * @since    0.1.0
	 */
	public function handle_search_graph_for_node() {

		if(
			isset( $_POST['lnd-search-nodes'] ) &&
			isset( $_REQUEST['lnd-post-nonce'] ) &&
			wp_verify_nonce( $_REQUEST['lnd-post-nonce'] , 'lnd-search-nodes' ) &&
			$_POST['lnd-search-nodes'] == "Y"
		){

			$search_node = strtolower( sanitize_text_field( $_POST['lnd-search-node'] ) );
			if( empty( $search_node ) ){ return 0; }
			$results = [];
			$lnd_network_graph = $this->lnd->get_network_graph();

			// loop through all network graph nodes
			// return the node as a search match if its ip, alias or public key
			// matches the supplied search term

			foreach( $lnd_network_graph->nodes as $node ){

				if(isset( $node->alias ) && strpos( strtolower( $node->alias ), $search_node ) !== false ){
					array_push( $results, $node );
				}

				if(isset( $node->pub_key) && strpos( strtolower( $node->pub_key ), $search_node ) !== false ){
					array_push( $results, $node );
				}

				if(isset( $node->addresses )){
					$addresses = $node->addresses;

					foreach( $addresses as $address ){
						if( strpos( $address->addr, $search_node ) !== false){
							array_push( $results, $node );
						}
					}
				}

			}

			if( count( $results ) == 0 ){
				return 0;
			}else{
				return $results;
			}

		}else{
			return false;
		}

	}

	/**
	 * Returns available channel balance as a percentage of total channel capacity
	 *
	 * @since    0.1.0
	 */
	public function get_channel_capacity_as_percentage($lnd_channel){

		$total_channel_capacity = $lnd_channel->capacity;
		$local_channel_balance = $lnd_channel->local_balance;
		$percentage_capacity = ( $local_channel_balance / $total_channel_capacity ) * 100;

		return round( $percentage_capacity );
	}

	/**
	 * Sort the transactions array into chronological order based on time_stamp
	 * so as to display newest transactions first
	 *
	 * @since    0.1.0
	 */
	public function sort_transactions_by_timestamp($transactions){

		function compare_tx_timestamp( $a, $b ){
			if( $a->time_stamp == $b->time_stamp ){
				return 0;
			}
			return ( $a->time_stamp > $b->time_stamp ) ? -1 : 1;
		}

		usort( $transactions, "compare_tx_timestamp" );
		return $transactions;
	}

	/**
	 * Check if the currenct browser connection uses SSL
	 *
	 * @since    0.1.0
	 */
	public function is_ssl() {

		if (isset( $_SERVER['HTTPS'] )) {
			return true;
		}else{
			return false;
		}

	}

	/**
	 * Update the Wordpress admin page footer text for this plugin
	 *
	 * @since    0.1.0
	 */
	public function admin_footer() {

		echo "LND For Wordpress Version $this->version <br />
		This software is released for free, but you can support the author by sending a few satoshis to: <a href='bitcoin:3PTj3wuauVLjnL4pU2y6qx84ek9hqAL8EN'>3PTj3wuauVLjnL4pU2y6qx84ek9hqAL8EN</a>
		";

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lnd-for-wp-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name .'theme', plugin_dir_url( __FILE__ ) . 'css/lnd-for-wp-admin-theme.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lnd-for-wp-admin.js', array( 'jquery' ), $this->version, false );

	    wp_localize_script( $this->plugin_name, 'ajax_object',
	    array( 'ajax_url' => admin_url( 'admin-ajax.php' )));

	}

}
