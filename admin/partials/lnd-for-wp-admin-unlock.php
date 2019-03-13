<?php

/**
 *
 * Unlock Wallet View
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      0.1.0
 *
 * @package    LND_For_WP
 */

$this->handle_form_unlock_wallet();

?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e( "Unlock Wallet", $this->plugin_name ); ?>
</h2>

<div class="lnd-wp-status">

	<?php if( $this->lnd->is_node_locked() ){ ?>

		<?php if( isset( $_REQUEST['message'] ) ){ ?>
			<div class="lnd-wp-alert">
				<?php esc_html_e( $_REQUEST['message'] , $this->plugin_name ); ?>
			</div>
		<?php } ?>

		<p>Your wallet is currently locked. To unlock, enter your wallet password and Press 'Unlock Wallet'.</p>

		<form method="post" action="?page=<?php echo esc_html( $_REQUEST['page'] ); ?>&f=unlock">
		<input type="hidden" name="lnd-unlock-wallet" value="Y" />
		<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce( 'lnd-unlock-wallet' ); ?>" />
		  <div class="form-group">
		    <label for="lnd-wallet-password">
		    	<?php esc_html_e( "Wallet Password", $this->plugin_name ); ?>:
		    </label>
		    <input type="password" class="form-control" name="lnd-wallet-password" id="lnd-wallet-password" placeholder="Password">
		  </div>

		  <button type="submit" class="btn btn-primary">
			  <?php esc_html_e( "Unlock Wallet", $this->plugin_name ); ?>
		  </button>
		</form>

	<?php }else{ ?>

		<p>
			<strong>
				<?php esc_html_e( "Wallet is unlocked", $this->plugin_name ); ?>. Redirecting...
				<?php $this->redirect_with_message( "", __( "Wallet is unlocked") ); ?>
			</strong>
		</p>

	<?php } ?>

</div>