<div class="lnd-for-wp">
	<form>
		<fieldset class="amount-field">
			<label>Satoshis</label>
		<input type="text" class="invoice-amount" value="<?php echo $attributes['amount']; ?>" />
		</fieldset>
		<fieldset class="invoice-field">
			<span class="lnd-wp-invoice-qr">
				<img src="" class="invoice-qr" />
			</span>
			<span class="lightning-invoice"></span>
			<a href="#" class="wallet-link">Open With Wallet</a>
		</fieldset>
		<fieldset class="funded-field">
			<label><strong>Invoice funded. Thank-you for your payment</strong></label>
		</fieldset>
		<input type="hidden" class="invoice-memo" value="<?php echo $attributes['memo']; ?>" />
		<input type="hidden" class="lnd-post-nonce" value="<?php echo wp_create_nonce( 'lnd_request_invoice' ); ?>" />
		<button class="btn-invoice-request" type="button">Request Invoice</button>
	</form>
</div>
