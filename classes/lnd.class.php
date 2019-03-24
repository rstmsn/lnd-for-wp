<?php

/**
 * php-Lnd
 *
 * A PHP class for interacting with the Bitcoin
 * Lightning Network Daemon (LND) REST API using WP_Http.
 *
 * @author     RSTMSN
 * @link       http://github.com/rstmsn/php-lnd
 * @version    0.2.0
 *
 * @package    php-lnd
 */

class lnd {

	/*
	 * version of the lnd rest api that we are communicating with
	 */
	private $lnd_api_version = 'v1';

	/*
	 * lnd rest endpoint
	 * (includes lnd host ip&port & api version, formatted as url)
	 */
	private $lnd_end_point = '';

	/*
	 * local disk path to tls.cert
	 * (required when sending SSL/TLS encrypted requests to lnd)
	 */
	private $tls_certificate_path = '';

	/*
	 * forces use of SSL/TLS when communicating with lnd
	 * (requires that tls_certificate_path is set)
	 */
	public $use_ssl = false;

	/*
	 * the hexadecimal representation of the lnd macaroon file
	 * (this is sent in the header of every api request we make
	 * and is used by lnd for authentication)
	 */
	protected $macaroon_hex;

	/*
	 * the maximum number of seconds to wait while attempting to
	 * connect to the remote lnd node
	 */
	private $connection_timeout = 5;

	/*
	 * if the initial is_node_reachable() method call returns false,
	 * we set $is_node_reachable to false. this variable acts
	 * as a simple state cache and is used to block further api calls if
	 * the remote node is unreachable. this aims to reduce page load times
	 */
	private $cached_is_node_reachable;

	/*
	 * a simple state cache for wether or not the node is online. When caching is
	 * turned on this variable is accessed instead of making additional api calls
	 */
	private $cached_is_node_online;

	/*
	 * a simple state cache for wether or not the node is locked. When caching is
	 * turned on this variable is accessed instead of making additional api calls
	 */
	private $cached_is_node_locked;

	/*
	 * a simple state cache for the node alias. When caching is
	 * turned on this variable is accessed instead of making additional api calls
	 */
	private $cached_node_alias;

	/*
	 * If the remote node is unreachable after the initial api request,
	 * $is_node_reachable is set to false, and some further api calls are blocked, to
	 * decrease page load time and avoid incurring the connection request timeout period.
	 * when set to true, this property is used to override the above caching, and
	 * forces all api requests to be sent, even if the remote host is unreachable after
	 * the first request
	 */
	private $force_disable_cache = false;

	/*
	 * Path to Root Certificate
	 */
	private $cacert = "/cert/cacert.pem";


	public function __construct( $lnd_host = '' ){

		if( !empty( $lnd_host ) ){
			$this->set_host( $lnd_host );
		}

	}

	/*
	 * Formats the lnd host details into an endpoint URL
	 * This will be the URL to which all our requests are sent
	 */
	public function set_host( $lnd_host ){
    	// run a basic regex check to ensure the provided host
    	// string is in the format of host:port
    	$regex = "([a-z0-9\-\.]*)\.(([a-z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))";
	    $regex .= "(:[0-9]{2,5})?";

	    if( preg_match( "~^$regex$~i", $lnd_host ) ){
			$this->lnd_end_point = 'https://' . $lnd_host . '/' . $this->lnd_api_version . '/';
	    }else{
			throw new Exception( "Invalid host. Use host:port syntax." );
	    }
	}

	/*
	 * Set the maximum number of seconds to wait while attempting to connect
	 * to the remote lnd node
	 */
	public function set_connection_timeout( $seconds ){

		if( is_numeric( $seconds ) ){
			$this->connection_timeout = $seconds;
		}

	}

	/*
	 * Set the file path to the root ca certificate
	 */
	public function set_cacert_file( $file_path ){

		if( file_exists( $file_path ) ){
			$this->cacert = $file_path;
		}else{
			return false;
		}

	}

	/*
	 * construct a new lnd api request and send it to our lnd endpoint.
	 * decode the JSON response and return an object of stdClass
	 */
	public function request( $path, $options = '', $delete = false, $timeout = '' ){

		$request_url = $this->lnd_end_point . $path;
		$request_method = $options ? 'POST' : 'GET';
		$request_method = $delete ? 'DELETE' : $request_method;
		$request_timeout = $timeout ? $timeout : $this->connection_timeout;

		// include lnd authentication macaroon (hex representation) in our request
		// header and set the request content type to JSON
		$request_header = array( "Grpc-Metadata-macaroon" => $this->macaroon_hex,
		 						 "Content-Type" => "application/json" );

		$request_body = $options ? json_encode( $options ) : '';

		$request_arguments = array(	"headers" => $request_header,
									"timeout" => $request_timeout,
									"method" => $request_method,
									"sslverify" => $this->use_ssl,
									"sslcertificates" => $this->tls_certificate_path,
									"body" => $request_body
								);

		$request = new WP_Http();
		$request_response = $request->request( $request_url , $request_arguments );

		#echo "sent request: " . $request_url . var_export(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2),true);

		if( is_wp_error( $request_response ) ){
			$exception_string = $request_response->get_error_message();
			throw new Exception( $exception_string );
		}else{
			$request_response = json_decode( $request_response['body'] );

			if($request_response == null){
				$exception_string = "locked";
				throw new Exception( $exception_string );
			}else{
				return $request_response;
			}

		}

	}

	/*
	 * Set the macaroon hex value using raw data
	 */
	public function load_macaroon_from_data( $macaroon_data ){

		if( !empty( $macaroon_data ) ){
			$this->macaroon_hex = $macaroon_data;
		}else{
			throw new Exception( 'Macaroon data is empty' );
		}
	}

	/*
	 * Check lnd TLS certificate exists on disk and
	 * store its path for later use (in constructing request headers)
	 */
	public function load_tls_cert( $tls_certificate_path ){

		if( file_exists( $tls_certificate_path ) ){
			$this->tls_certificate_path = $tls_certificate_path;
			$this->use_ssl = true;
		}else{
			throw new Exception( 'TLS Certificate not found' );
		}

	}


	/**
	 * Check if lnd node is reachable
	 *
	 * returns true or false.
	 *
	 * @since    0.1.0
	 */
	public function is_node_reachable() {

		// if the node is flagged as unreachable, block further api calls
		// to reduce page load times
		if( !$this->force_disable_cache && isset( $this->cached_is_node_reachable ) && !$this->cached_is_node_reachable ){
			return false;
		}

		try {
			$lnd_info = $this->request( 'getinfo' );

			if( isset( $lnd_info->error ) ){
				$this->cached_is_node_reachable = false;
				return false;
			}else{
				return true;
			}

		} catch( Exception $e ){
			$this->cached_is_node_reachable = false;
			return false;
		}

		return false;
	}

	/**
	 * Check if lnd node is online
	 *
	 * returns true or false.
	 *
	 * @since    0.1.0
	 */
	public function is_node_online() {

		// if the node is flagged as offline, block further api calls
		// to reduce page load times

		if( !$this->force_disable_cache &&
			isset( $this->cached_is_node_online )
		){
			return $this->cached_is_node_online;
		}

		try {
			$lnd_info = $this->request( 'getinfo' );
			$this->cached_is_node_online = true;
			return true;

		} catch( Exception $e ){
			if( $e->getMessage() == "locked" ){
				$this->cached_is_node_online = true;
				return true;
			}

		}

		$this->cached_is_node_reachable = false;
		$this->cached_is_node_online = false;
		return false;
	}

	/**
	 * Check if lnd node is online & locked
	 *
	 * returns true or false.
	 *
	 * @since    0.1.0
	 */
	public function is_node_locked( $override_cache = false ) {

		if( !$this->force_disable_cache &&
			isset( $this->cached_is_node_locked ) &&
			$override_cache == false
		){
			return $this->cached_is_node_locked;
		}

		try {
			$lnd_info = $this->request( 'getinfo' );
			$this->cached_is_node_locked = false;
			return false;

		} catch( Exception $e ){
			if( $e->getMessage() == "locked" ){
			$this->cached_is_node_locked = true;
				return true;
			}

		}

		$this->cached_is_node_locked = false;
		return false;
	}

	/**
	 * Query lnd node for its version
	 *
	 * @since    0.1.0
	 */
	public function get_node_version() {

		try {
			$lnd_info = $this->request( 'getinfo' );
			if( isset( $lnd_info->error ) ){
				$node_version = 'Error: ' . $lnd_info->error;
			}else{
				$node_version_full = explode( " ", $lnd_info->version );
				$node_version = $node_version_full[0];
			}

		} catch( Exception $e ){
			$node_version = $e->getMessage();
		}


		return $node_version;
	}

	/**
	 * Request a new lightning network invoice from lnd
	 *
	 * @since    0.1.0
	 */
	public function get_new_invoice( $amount, $memo, $return_json = true, $include_qr = false ) {

		try {

			$invoice_options = array( "memo" => $memo, "value" => $amount );
			$invoice = $this->request( 'invoices', $invoice_options, false, true );

			$return_invoice = new stdClass();
			$return_invoice->payment_request = $invoice->payment_request;
			$return_invoice->r_hash = $invoice->r_hash;
			$return_invoice->amount = $amount;
			$return_invoice->memo = $memo;

			if( $include_qr ){
				$return_invoice->qr = $this->draw_qr( $return_invoice->payment_request );
			}

			if( $return_json ){
				return json_encode( $return_invoice );
			}else{
				return $return_invoice;
			}


		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Request payment of a lightning invoice
	 *
	 * @since    0.1.0
	 */
	public function pay_invoice( $invoice ) {

		try {

			$payment_options = array( "payment_request" => $invoice );
			$payment = $this->request( 'channels/transactions', $payment_options );

			return $payment;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Return invoices stored in the lnd database
	 *
	 * @since    0.1.0
	 */
	public function get_invoices() {

		try {

			$invoices = $this->request( 'invoices', $options );
			return $invoices;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Open a new channel
	 *
	 * @since    0.1.0
	 */
	public function open_channel( $satoshi_amount, $remote_node_pubkey ) {

		try {

			$channel_options = array( "node_pubkey_string" => $remote_node_pubkey,
									  "local_funding_amount" => $satoshi_amount );

			$new_channel = $this->request( 'channels', $channel_options );

			return $new_channel;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Close a channel
	 *
	 * @since    0.1.0
	 */
	public function close_channel( $channel_id ) {

		try {

			$channels = $this->request( 'channels' )->channels;

			foreach( $channels as $channel ){
				if( $channel->chan_id == $channel_id ){
					$channel_info = explode( ":" , $channel->channel_point );
					$funding_tx_id = $channel_info[0];
					$index = $channel_info[1];

					if( isset( $funding_tx_id ) && isset( $index ) ){
						$this->request( 'channels/' . $funding_tx_id . '/' . $index, '', true  );
						return true;
					}
				}
			}

			return false;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Request all open channel data
	 *
	 * @since    0.1.0
	 */
	public function get_node_open_channels() {

		try {

			$channels = $this->request( 'channels' );
			return $channels->channels;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Request all closed channel data
	 *
	 * @since    0.1.0
	 */
	public function get_node_closed_channels() {

		try {

			$channels = $this->request( 'channels/closed' );
			if( isset( $channels->channels ) ){
				return $channels->channels;
			}else{
				return array(0);
			}

		} catch( Exception $e ){
			$error = $e->getMessage();
			return $error;
		}

		return false;
	}

	/**
	 * Request all pending channel data
	 *
	 * @since    0.1.0
	 */
	public function get_node_pending_channels() {

		try {

			$channels = $this->request( 'channels/pending' );

			return $channels;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Takes an encoded Lightning Network invoice payment request parameter
	 * and decodes it into an object with its constituent properties.
	 *
	 * @since    0.1.0
	 */
	public function decode_invoice( $invoice ){

		try {

			$payment = $this->request( 'payreq/' . $invoice );
			return $payment;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;
	}

	/**
	 * Attempt to disconnect an active peer connection
	 *
	 * @since    0.1.0
	 */
	public function disconnect_peer( $peer ){

		try {

			$response = $this->request( 'peers/' . $peer, '', true );

			return $response;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;

	}

	/**
	 * Attempt to disconnect an active peer connection
	 *
	 * @since    0.1.0
	 */
	public function connect_peer( $pubkey, $host ){

		try {

			$lightning_address['pubkey'] = $pubkey;
			$lightning_address['host'] = $host;
			$options = array( "addr" => $lightning_address );
			$response = $this->request( 'peers', $options );

			return $response;

		} catch( Exception $e ){
			$error = $e->getMessage();
		}

		return $error;

	}

	/**
	 * Query lnd node for its alias
	 *
	 * @since    0.1.0
	 */
	public function get_node_alias() {

		// if the node is cache flagged as offline, skip get alias api request
		if( !$this->force_disable_cache && isset( $this->cached_is_node_online ) && !$this->cached_is_node_online ){
			return 'Host Unreachable';
		}

		// if the node is cache flagged as locked, skip get alias api request
		if( !$this->force_disable_cache && isset( $this->cached_is_node_locked ) && $this->cached_is_node_locked ){
			return 'Locked';
		}

		// if the node alias cache var is set, return it instead of making a new request
		if( !$this->force_disable_cache && isset( $this->node_alias ) ){
			return $this->node_alias;
		}

		try {
			$lnd_info = $this->request( 'getinfo' );
			if( isset( $lnd_info->error ) ){
				$node_alias = 'Error: ' . $lnd_info->error;
			}else{
				$this->node_alias = $lnd_info->alias;
				return $this->node_alias;
			}

		} catch( Exception $e ){
			$error_alias = $e->getMessage();
		}

		return $error_alias;
	}

	/**
	 * Query lnd node for its pub key
	 *
	 * @since    0.1.0
	 */
	public function get_node_pubkey() {

		try {
			$lnd_info = $this->request( 'getinfo' );
			if( isset( $lnd_info->error ) ){
				$node_pubkey = 'Error: ' . $lnd_info->error;
			}else{
				$node_pubkey = $lnd_info->identity_pubkey;
			}

		} catch( Exception $e ){
			$node_pubkey = $e->getMessage();
		}


		return $node_pubkey;
	}

	/**
	 * Fetch the alias for a given node by its pubkey
	 *
	 * @since    0.1.0
	 */
	public function get_peer_alias( $pubkey ) {

		try {

			$node_info = $this->request( 'graph/node/' . $pubkey );

			if( isset( $node_info->error ) ){
				$node_alias = 'Alias Unavailable';
			}else{
				if( !empty( $node_info->node->alias ) ){

					$node_alias = $node_info->node->alias;
				}else{
					$node_alias = 'Alias Unavailable';
				}

			}

		} catch( Exception $e ){
			$node_alias = $e->getMessage();
		}


		return $node_alias;
	}

	/**
	 * Query lnd node to check if its synced to chain
	 *
	 * @since    0.1.0
	 */
	public function get_node_synced() {

		try {
			$lnd_info = $this->request( 'getinfo' );
			if( isset( $lnd_info->error ) ){
				$node_synced = 'Error: ' . $lnd_info->error;
			}else{
				if( isset( $lnd_info->synced_to_chain ) ){
					$node_synced = $lnd_info->synced_to_chain;
				}else{
					$node_synced = 'Synchronising...';
				}
			}

		} catch( Exception $e ){
			$node_synced = $e->getMessage();
		}

		return $node_synced;
	}

	/**
	 * Query lnd node for the block height
	 *
	 * @since    0.1.0
	 */
	public function get_node_blockheight() {

		try {
			$lnd_info = $this->request( 'getinfo' );
			if( isset( $lnd_info->error ) ){
				$blockheight = 'Error: ' . $lnd_info->error;
			}else{
				$blockheight = $lnd_info->block_height;
			}

		} catch( Exception $e ){
			$blockheight = $e->getMessage();
		}

		return $blockheight;
	}

	/**
	 * Query lnd node for number of active peers
	 *
	 * @since    0.1.0
	 */
	public function get_node_num_peers() {

		try {
			$lnd_info = $this->request( 'getinfo' );
			if( isset( $lnd_info->error ) ){
				$node_peers = 'Error: ' . $lnd_info->error;
			}else{
				if( !isset($lnd_info->num_peers) ){
					return 0;
				}
				$node_peers = $lnd_info->num_peers;
			}

		} catch( Exception $e ){
			$node_peers = $e->getMessage();
		}

		return $node_peers;
	}

	/**
	 * Query lnd node for number of active channels
	 *
	 * @since    0.1.0
	 */
	public function get_node_num_channels() {

		try {
			$lnd_info = $this->request( 'getinfo' );
			if( isset( $lnd_info->error ) ){
				$node_chans = 'Error: ' . $lnd_info->error;
			}else{
				if( !isset( $lnd_info->num_active_channels ) ){
					return 0;
				}
				$node_chans = $lnd_info->num_active_channels;
			}

		} catch( Exception $e ){
			$node_chans = $e->getMessage();
		}


		return $node_chans;
	}

	/**
	 * Query lnd node for total satoshis available across all channels
	 *
	 * @since    0.1.0
	 */
	public function get_total_channel_balance() {

		try {
			$lnd_info = $this->request( 'balance/channels' );
			if( isset( $lnd_info->error ) ){
				$channel_balance = 'Error: ' . $lnd_info->error;
			}else{
				if( !$lnd_info->balance ){
					return 0;
				}
				$channel_balance = $lnd_info->balance;
			}

		} catch( Exception $e ){
			$channel_balance = $e->getMessage();
		}


		return $channel_balance;
	}

	/**
	 * Query lnd node for total blockchain balance (layer 1)
	 *
	 * @since    0.1.0
	 */
	public function get_total_blockchain_balance() {

		try {
			$lnd_info = $this->request( 'balance/blockchain' );
			if( isset( $lnd_info->error ) ){
				$total_balance = 'Error: ' . $lnd_info->error;
			}else{
				if( !$lnd_info->total_balance ){
					return 0;
				}
				$total_balance = $lnd_info->total_balance;
			}

		} catch( Exception $e ){
			$total_balance = $e->getMessage();
		}


		return $total_balance;
	}

	/**
	 * Query lnd node for total unconfirmed blockchain balance (layer 1)
	 *
	 * @since    0.1.0
	 */
	public function get_unconfirmed_balance() {

		try {
			$lnd_info = $this->request( 'balance/blockchain' );
			if( isset( $lnd_info->error ) ){
				$unconfirmed_balance = 'Error: ' . $lnd_info->error;
			}else{
				if( !$lnd_info->unconfirmed_balance ){
					return 0;
				}
				$unconfirmed_balance = $lnd_info->unconfirmed_balance;
			}

		} catch( Exception $e ){
			$unconfirmed_balance = $e->getMessage();
		}


		return $unconfirmed_balance;
	}

	/**
	 * Query lnd node for total confirmed blockchain balance (layer 1)
	 *
	 * @since    0.1.0
	 */
	public function get_confirmed_balance() {

		try {
			$lnd_info = $this->request( 'balance/blockchain' );
			if( isset( $lnd_info->error ) ){
				$confirmed_balance = 'Error: ' . $lnd_info->error;
			}else{
				if( !$lnd_info->confirmed_balance ){
					return 0;
				}
				$confirmed_balance = $lnd_info->confirmed_balance;
			}

		} catch( Exception $e ){
			$confirmed_balance = $e->getMessage();
		}


		return $confirmed_balance;
	}

	/**
	 * Query lnd node and request a new on chain bitcoin address
	 *
	 * @since    0.1.0
	 */
	public function get_node_chain_address() {

		try {
			$address = $this->request( 'newaddress?type=1' );
			return $address;
		} catch ( Exception $e ){
			return $e->getMessage();
		}
	}

	/**
	 * Check if an invoice has been paid
	 *
	 * @since    0.1.0
	 */
	public function invoice_is_paid( $payment_hash ) {

		$r_hash = bin2hex( base64_decode( $payment_hash ) );

		try {
			$invoice = $this->request( 'invoice/' . $r_hash );

			if( $invoice->settled ){
				return true;
			}else{
				return false;
			}

		} catch ( Exception $e ){
			return false;
		}
	}

	/**
	 * Query lnd node to unlock wallet
	 *
	 * @since    0.1.0
	 */
	public function unlock_wallet( $wallet_password ) {

		$unlock_options = array( "wallet_password" => $wallet_password );
		try {
			$response = $this->request( 'unlockwallet', $unlock_options );
			return $response;
		} catch ( Exception $e ){
			return $e->getMessage();
		}
	}

	/**
	 * Query lnd node to get active connected peers
	 *
	 * @since    0.1.0
	 */
	public function get_node_peers() {
		try {
			$peers = $this->request( 'peers' )->peers;
			return $peers;
		} catch ( Exception $e ){
			return $e->getMessage();
		}
	}

	/**
	 * Fetch summary details describing network graph
	 *
	 * @since    0.1.0
	 */
	public function get_network_details() {
		try {
			$graph = $this->request( 'graph/info' );

			return $graph;
		} catch ( Exception $e ){
			return $e->getMessage();
		}
	}

	/**
	 * Fetch the entire network graph description
	 *
	 * This is an expensive time consuming call
	 *
	 * @since    0.1.0
	 */
	public function get_network_graph() {
		try {
			$graph = $this->request( 'graph' , '' , false, 60 );
			return $graph;
		} catch ( Exception $e ){
			return $e->getMessage();
		}
	}

	/**
	 * Fetch all transactions known to the wallet
	 *
	 * @since    0.1.0
	 */
	public function get_transactions() {
		try {
			$transactions = $this->request( 'transactions' )->transactions;

			if( is_array( $transactions ) ){
				$transactions = array_reverse( $transactions );
			}

			return $transactions;
		} catch ( Exception $e ){
			return $e->getMessage();
		}
	}

	/**
	 * Returns QR image with src data encoded as base64
	 *
	 * @since    0.1.0
	 */
	public function draw_qr( $data_string ) {
		$qr_image_path = plugin_dir_path( __FILE__ ) . '../admin/img/qr/qr.png';
		$qr = new QRcode();
		$qr->png( $data_string, $qr_image_path, 'L', 5, 2);
		$image_data = base64_encode( file_get_contents($qr_image_path) );
		$img = '<img src="data:image/gif;base64,'. $image_data .'" />';

		return $img;
	}

}