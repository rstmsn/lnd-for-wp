<?php

/**
 * Markup for the Request Payment view
 *
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      0.1.0
 *
 * @package    LND_For_WP
 */

$payment_request = $this->handle_payment_request_form_submit();

?>
<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Request", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">

	<?php if(isset($_REQUEST['message'])){ ?>
		<div class="lnd-wp-alert"><?php echo $_REQUEST['message']; ?></div>
	<?php } ?>

	<?php if($payment_request){ ?>

		<p>
			<strong>
			<?php esc_html_e("Successfully generated invoice for ", $this->plugin_name); ?>
			<?php echo $payment_request->amount; ?>
			<?php esc_html_e(" Satoshi", $this->plugin_name); ?>:
			</strong>
		</p>

		<p class="lnd-p-center">
			<?php echo $this->lnd->draw_qr($payment_request->payment_request); ?>
		</p>

		<p id="lnd-pay-req"><?php echo trim($payment_request->payment_request); ?></p>

		<button type="button" class="btn btn-secondary" id="lnd-copy-invoice-clip">
			<?php esc_html_e("Copy to Clipboard", $this->plugin_name); ?>
		</button>

	<?php } else { ?>

		<form method="post" action="?page=<?php echo $_REQUEST['page']?>&f=request">

			<input type="hidden" name="lnd-request-submit" value="Y" />

			<p class="lnd-receive-sat">
				<?php esc_html_e("Invoice Amount (Satoshi)", $this->plugin_name); ?>:
			</p>
			<input type="text" name="lnd-request-amount" class="form-control" placeholder="0" />

			<p class="lnd-receive-memo">
				<?php esc_html_e("Invoice Description (optional)", $this->plugin_name); ?>:
			</p>
			<input type="text" name="lnd-request-memo" class="form-control" />

			<button type="submit" class="btn btn-secondary">
				<?php esc_html_e("Generate Invoice", $this->plugin_name); ?>
			</button>
		</form>

	<?php } ?>

</div>