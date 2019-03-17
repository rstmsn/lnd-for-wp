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

$on_chain_funding_address = $this->handle_funding_address();

?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Fund Wallet", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">

	<p class="lnd-p-center"><?php esc_html_e("On Chain Funding Address:", $this->plugin_name); ?></p>

	<?php if(isset( $_REQUEST['message'] )){ ?>
		<div class="lnd-wp-alert"><?php echo esc_html( $_REQUEST['message'] ); ?></div>
	<?php } ?>

	<?php echo $this->lnd->draw_qr($on_chain_funding_address); ?>

	<p class="lnd-p-center"><strong><?php echo $on_chain_funding_address; ?></strong></p>

	<form method="post" action="?page=<?php echo esc_html($_REQUEST['page']); ?>&f=funding&new=Y">
		<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce('lnd_gen_funding_address'); ?>" />
		<button type="submit" class="btn btn-secondary">
			<?php esc_html_e("Generate New Address", $this->plugin_name); ?>
		</button>
	</form>
</div>