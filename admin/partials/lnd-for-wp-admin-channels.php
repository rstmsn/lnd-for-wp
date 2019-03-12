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

$this->handle_open_channel_form();
$close_channel_confirm = $this->handle_close_channel_form();
$lnd_open_channels = $this->lnd->get_node_open_channels();
$lnd_pending_channels = $this->lnd->get_node_pending_channels();
$lnd_closed_channels = $this->lnd->get_node_closed_channels();

?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e( "Channels", $this->plugin_name ); ?>
</h2>

<div class="lnd-wp-status">

	<?php if(isset( $_REQUEST['message'] )){ ?>
		<div class="lnd-wp-alert"><?php echo esc_html( $_REQUEST['message'] ); ?></div>
	<?php } ?>

	<?php
		if(
			!isset( $_REQUEST['lnd-open-channel'] ) &&
			!isset( $_REQUEST['lnd-close-channel'] )
		){
	?>
		<p>
			<span class="lnd-channels-open">
				<?php esc_html_e( "There are currently ", $this->plugin_name ); ?>
				<strong>
					<?php echo isset( $lnd_open_channels ) ? count( $lnd_open_channels ) : 0 ; ?>
				</strong>
				<?php esc_html_e( "Open, ", $this->plugin_name ); ?>

				<strong>
					<?php echo isset( $lnd_pending_channels->pending_open_channels ) ? count( $lnd_pending_channels->pending_open_channels ) : 0; ?>
				</strong>

				<?php esc_html_e( " Pending Open, ", $this->plugin_name ); ?>

				<strong>
					<?php echo isset( $lnd_pending_channels->pending_closing_channels ) ? count( $lnd_pending_channels->pending_closing_channels) : 0; ?>
				</strong>

				<?php esc_html_e( " Pending Close, and ", $this->plugin_name ); ?>

				<strong>
					<?php echo isset( $lnd_closed_channels ) ? count( $lnd_closed_channels ) : 0; ?>
				</strong>

				<?php esc_html_e( " Closed Channels", $this->plugin_name ); ?>.
			</span>
		</p>
	<?php } ?>

	<?php

	if($lnd_open_channels){

		if($close_channel_confirm){

			$close_channel_id = sanitize_text_field( $_REQUEST['lnd-close-channel-id'] );
	?>

			<p class="lnd-ui-p">
				<strong>
				<?php esc_html_e( "Are you sure you want to close the channel with the following ID", $this->plugin_name ); ?>:
				</strong>
			</p>

			<p class="lnd-ui-p">
				<h3><?php echo $close_channel_id; ?></h3>
			</p>

			<form class="lnd-channel-form" method="post" action="?page=<?php echo esc_html( $_REQUEST['page'] ); ?>&f=channels">
				<input type="hidden" name="lnd-close-channel" value="Y" />
				<input type="hidden" name="lnd-close-channel-id" value="<?php echo $close_channel_id; ?>" />
				<input type="hidden" name="lnd-close-channel-confirm" value="Y" />
				<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce( 'lnd-close-channel' ); ?>" />

				  <button type="submit" class="btn btn-danger">
					  <?php esc_html_e( "Close Channel", $this->plugin_name ); ?>
				  </button>
			</form>

		<?php }else if(isset( $_REQUEST['lnd-open-channel'] ) && $_REQUEST['lnd-open-channel'] == "Y") { ?>

			<?php
				if(isset( $_REQUEST['lnd-open-peer-pubkey'] ) && !empty( $_REQUEST['lnd-open-peer-pubkey'] )){
					$remote_pubkey = sanitize_text_field( $_REQUEST['lnd-open-peer-pubkey'] );
				}
			?>

			<p>
				<?php esc_html_e( "Satoshis in Channel", $this->plugin_name ); ?>:
			</p>

			<form method="post" action="?page=<?php echo esc_html( $_REQUEST['page'] ); ?>&f=channels">
				<input type="hidden" name="lnd-open-channel-confirm" value="Y" />
				<input type="text" name="lnd-open-channel-sat" class="form-control" placeholder="0" />
				<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce( 'lnd-open-channel' ); ?>" />

				<p>
					<?php esc_html_e( "Node Public Key", $this->plugin_name ); ?>:
				</p>
				<input type="text" name="lnd-open-channel-pubkey" class="form-control" placeholder="pubkey" value="<?php echo isset( $remote_pubkey ) ? $remote_pubkey : ''; ?>" />

				<button type="submit" class="btn btn-secondary">
					&plus; <?php esc_html_e( "Open Channel", $this->plugin_name ); ?>
				</button>
			</form>

		<?php }else{ ?>

			<div class="lnd-wp-channels">

			<?php foreach( $lnd_open_channels as $lnd_channel ){ ?>

				<div class="lnd-channel-contain">
					<h3 title="<?php echo $lnd_channel->remote_pubkey; ?>">
						<?php echo $this->lnd->get_peer_alias( $lnd_channel->remote_pubkey ); ?>
					</h3>

					<p class="lnd-ui-p">
						<strong>
							<?php esc_html_e( "Channel ID", $this->plugin_name ); ?>:
						</strong>
						<span class="lnd-peer-pubkey">
							<?php echo $lnd_channel->chan_id; ?>
						</span>
					</p>

					<p class="lnd-ui-p">
						<strong>
							<?php esc_html_e( "Local Balance", $this->plugin_name ); ?>:
						</strong>
						<span class="lnd-peer-pubkey">
							<?php echo number_format( $lnd_channel->local_balance ); ?>
						</span>

						<span class="lnd-channel-available">
							<strong>
								<?php esc_html_e("Available to Receive", $this->plugin_name); ?>:
							</strong>
							<span class="lnd-peer-pubkey">
								<?php echo number_format( $lnd_channel->capacity - $lnd_channel->local_balance ); ?>
							</span>
						</span>

						<span class="lnd-channel-capacity-contain">
							<span class="lnd-channel-capacity" style="width: <?php echo $this->get_channel_capacity_as_percentage( $lnd_channel ) ?>%;"></span>
						</span>
					</p>

					<form method="post" action="?page=<?php echo esc_html( $_REQUEST['page'] ); ?>&f=channels">
						<input type="hidden" name="lnd-close-channel" value="Y" />
						<input type="hidden" name="lnd-close-channel-id" value="<?php echo $lnd_channel->chan_id; ?>" />
						<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce( 'lnd-close-channel' ); ?>" />
						  <button type="submit" class="btn btn-danger">
							  <?php esc_html_e( "Close Channel", $this->plugin_name ); ?>
						  </button>
					</form>
				</div>

			<? } ?>

		</div>

		<button type="submit" class="btn btn-secondary lnd-wp-expand-channels">
			<span class="lnd-wp-expand-channel-show">
				<?php esc_html_e( "View Open Channels", $this->plugin_name ); ?>
			</span>
			<span class="lnd-wp-expand-channel-hide">
				<?php esc_html_e( "Hide Open Channels", $this->plugin_name ); ?>
			</span>
		</button>

		<?php } ?>

	<?php } ?>

	<?php if( !isset( $_REQUEST['lnd-open-channel'] ) && !$close_channel_confirm ){ ?>

		<form class="lnd-peer-form" method="post" action="?page=<?php echo esc_html( $_REQUEST['page'] ); ?>&f=channels">
			<input type="hidden" name="lnd-open-channel" value="Y" />
			<input type="hidden" name="lnd-post-nonce" value="<?php echo wp_create_nonce( 'lnd-open-channel' ); ?>" />

			<button type="submit" class="btn btn-secondary">
			&plus; <?php esc_html_e( "Open Channel", $this->plugin_name ); ?>
			</button>
		</form>

	<?php } ?>

</div>