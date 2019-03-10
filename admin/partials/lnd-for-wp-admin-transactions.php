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

$transactions = $this->sort_transactions_by_timestamp($this->lnd->get_transactions());

?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Transactions", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">

	<?php foreach($transactions as $transaction){ ?>

		<div class="lnd-wp-transaction">
			<h4>TXID: <?php echo $transaction->tx_hash; ?></h4>
			<?php echo esc_html_e($transaction->amount < 0 ? '&uarr; Sent' : '&darr; Received', $this->plugin_name); ?>
			<?php echo number_format(abs($transaction->amount)); ?> SAT

			<?php if($transaction->amount < 0){
				echo esc_html_e("for a fee of ", $this->plugin_name);
				echo number_format($transaction->total_fees);
			} ?>

			<?php echo esc_html_e("on", $this->plugin_name); ?>
			<?php echo date("d/m/Y \a\\t H:m A\.", $transaction->time_stamp); ?>

			<br />
			<?php echo esc_html_e("Confirmations: ", $this->plugin_name); ?>

			<?php $transaction->num_confirmations > 10 ? $css = 'confirmed' : $css = 'unconfirmed'; ?>

			<span class="lnd-tx-<?php echo $css; ?>">
				<?php echo number_format($transaction->num_confirmations); ?>
			</span>
		</div>

	<?php } ?>

</div>