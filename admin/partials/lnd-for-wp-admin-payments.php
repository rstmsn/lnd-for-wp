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

$response = $this->handle_pay_lightning_invoice_form();

?>
<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Payments", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">

	<?php if(isset($_REQUEST['message'])){ ?>
		<div class="lnd-wp-alert"><?php echo esc_html($_REQUEST['message']); ?></div>
	<?php } ?>

	<?php if($response){ ?>

		<p>
			<form method="post" action="?page=<?php echo esc_html($_REQUEST['page']); ?>&f=payments">
				<input type="hidden" name="lnd-pay-invoice" value="Y" />
				<input type="hidden" name="lnd-pay-confirm" value="true" />
				<input type="hidden" name="lightning-invoice" value="<?php echo sanitize_text_field($_REQUEST['lightning-invoice']); ?>" />
				<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce('lnd_confirm_pay_invoice'); ?>" />

				<p>
					<strong>
						<?php esc_html_e("Confirm payment of ", $this->plugin_name); ?>
						<?php echo number_format($response->num_satoshis); ?>
						<?php esc_html_e("Satoshi to:", $this->plugin_name); ?>
					</strong>
					<h3><?php echo $this->lnd->get_peer_alias($response->destination); ?>
				</p>
				<p class="lnd-p-center"><?php echo $response->destination; ?></p>
				<p class="lnd-p-center">
					<cite>
						<?php if( isset( $response->description ) ){ echo $response->description; } ?>
					</cite>
				</p>

				<button type="submit" class="btn btn-secondary">
					<?php esc_html_e("Pay Invoice", $this->plugin_name); ?>
				</button>
			</form>
		</p>

<?php }else{ ?>

	<p>
		<form method="post" action="?page=<?php echo esc_html($_REQUEST['page']); ?>&f=payments">
			<fieldset>
				<input type="hidden" name="lnd-pay-invoice" value="Y" />
				<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce('lnd_confirm_pay_invoice'); ?>" />
				<label for="lightning-invoice">
					<?php esc_html_e("Pay Lightning Invoice", $this->plugin_name); ?>:
				</label>

				<div class="lnd-wp-fixed">
					<input type="file" id="lnd-qr-image" />
					<input id="lnd-read-qr" type="button" class="btn btn-secondary" value="" />
			  	</div>
			  	<div class="lnd-wp-fluid">
			  		<input type="text" class="form-control" name="lightning-invoice" id="lightning-invoice" />
			  	</div>

				<button type="submit" class="btn btn-secondary">
					<?php esc_html_e("Pay Invoice", $this->plugin_name); ?>
				</button>
			</fieldset>
		</form>
	</p>

<?php } ?>

</div>