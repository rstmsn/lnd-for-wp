<div class="lnd-for-wp">
	<form>
		<fieldset class="amount-field">
			<span><?php echo $attributes['amount']; ?> Satoshis</span>
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
		<button class="btn-invoice-request" type="button">Please Pay</button>
	</form>
	<div class="funded-field-content"></div>
</div>
