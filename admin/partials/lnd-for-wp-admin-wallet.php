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

$lnd_hostname = get_option( 'lnd-hostname', 'Undefined' );

?>
<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr;

	<?php esc_html_e("Wallet", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">

	<?php if($this->lnd->is_node_reachable()){ ?>

		<a href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=funding">
			<span class="lnd-wallet-funding"></span>
			<?php esc_html_e("Fund Wallet", $this->plugin_name); ?>
		</a>

		<span class="lnd-wallet-balance lnd-balance">
			<span class="lnd-balance-amount"><?php echo number_format($this->lnd->get_total_channel_balance()); ?></span><span class="lnd-wallet-currency lnd-balance-currency">SAT</span>
		</span>

		<p class="lnd-chain-balance-label">
			<?php esc_html_e("Chain Balance", $this->plugin_name); ?>
		</p>

		<span class="lnd-chain-balance lnd-balance">
			<span class="lnd-balance-amount"><?php echo number_format($this->lnd->get_confirmed_balance()); ?></span><span class="lnd-chain-currency lnd-balance-currency">SAT</span>
		</span>

		<a class="lnd-wallet-transactions" href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=transactions">
			<?php esc_html_e("Transactions", $this->plugin_name); ?>
		</a>

	<?php } ?>

</div>
