<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      0.1.0
 *
 * @package    LND_For_WP
 */

// load the most recently generated on chain wallet address
$on_chain_funding_address = get_option( 'lnd-on-chain-address' );

// if no on chain funding address has been previously stored, or if
// user has requested the generation of a new address, generate a new
// on chain address and store it as a wordpress option
if(!$on_chain_funding_address || isset($_REQUEST['new'])){
	$on_chain_funding_address =	$this->lnd->get_node_chain_address();
	update_option( 'lnd-on-chain-address', $on_chain_funding_address );
}

?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Fund Wallet", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">
	<p class="lnd-p-center"><?php esc_html_e("On Chain Funding Address:", $this->plugin_name); ?></p>

	<?php echo $this->lnd->draw_qr($on_chain_funding_address); ?>

	<p class="lnd-p-center"><strong><?php echo $on_chain_funding_address; ?></strong></p>

	<form method="post" action="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=funding&new=Y">
		<button type="submit" class="btn btn-secondary">
			<?php esc_html_e("Generate New Address", $this->plugin_name); ?>
		</button>
	</form>
</div>