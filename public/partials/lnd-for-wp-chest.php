<div class="lnd-for-wp">
	<form>
		<fieldset class="amount-field">
			<p class="amount-sat"><?php echo $attributes['amount']; ?> Satoshis</p>
			<p class="amount-fiat">This amount of Satoshis currently corresponds to approximately <?php echo $amount_fiat ?> Euro.</p>
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
		<input type="hidden" class="invoice-amount" value="<?php echo $attributes['amount']; ?>" />
		<input type="hidden" class="invoice-memo" value="<?php echo $attributes['memo']; ?>" />
		<input type="hidden" class="content" value="<?php echo $encrypted; ?>" />
		<input type="hidden" class="lnd-post-nonce" value="<?php echo wp_create_nonce( 'lnd_request_invoice' ); ?>" />
		<button class="btn-invoice-request" type="button">Please Pay</button>
	</form>
	<div class="funded-field-content"></div>
</div>
