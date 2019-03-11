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

$this->handle_add_peer_form();
$lnd_peers = $this->lnd->get_node_peers();
$dc_peer_confirm = $this->handle_disconnect_peer_form();
?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Peers", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">

	<?php if(isset($_REQUEST['message'])){ ?>
		<div class="lnd-wp-alert"><?php echo sanitize_text_field($_REQUEST['message']); ?></div>
	<?php } ?>

	<?php

	if($lnd_peers){

		if($dc_peer_confirm){

			$disconnect_peer_id = sanitize_text_field($_REQUEST['lnd-disconnect-peer-id']); 				?>

			<p class="lnd-ui-p">
				<strong>
					<?php esc_html_e("Are you sure you want to disconnect from the following peer", $this->plugin_name); ?>:
				</strong>
			</p>

			<p class="lnd-ui-p">
				<h3><?php echo $this->lnd->get_peer_alias($disconnect_peer_id); ?></h3>
				<?php echo $disconnect_peer_id; ?>
			</p>

			<form class="lnd-peer-form" method="post" action="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=peers">
				<input type="hidden" name="lnd-disconnect-peer" value="Y" />
				<input type="hidden" name="lnd-disconnect-peer-id" value="<?php echo $disconnect_peer_id; ?>" />
				<input type="hidden" name="lnd-disconnect-peer-confirm" value="Y" />

				<button type="submit" class="btn btn-danger">
					<?php esc_html_e("Disconnect", $this->plugin_name); ?>
				</button>
			</form>

		<?php }else if(!isset($_REQUEST['lnd-add-peer'])){ ?>

			<p>
				<?php esc_html_e("Currently connected to", $this->plugin_name); ?>:
				<strong><?php echo count($lnd_peers); ?> peers</strong>
			</p>

			<div class="lnd-wp-peers">

			<?php foreach($lnd_peers as $lnd_peer){ ?>

				<div class="lnd-peer-contain">

					<h3><?php echo $this->lnd->get_peer_alias($lnd_peer->pub_key); ?></h3>

					<p class="lnd-ui-p">
						<strong>
							<?php esc_html_e("Public Key", $this->plugin_name); ?>:
						</strong>
					</p>
					<p class="lnd-ui-p">
						<span class="lnd-peer-pubkey">
							<?php echo $lnd_peer->pub_key; ?>@<strong><?php echo $lnd_peer->address; ?></strong>
						</span>
					</p>

					<table class="lnd-peer">
						<thead>
							<tr>
								<th><?php esc_html_e("Sent", $this->plugin_name); ?></th>
								<th><?php esc_html_e("Received", $this->plugin_name); ?></th>
								<th><?php esc_html_e("Sat Sent", $this->plugin_name); ?></th>
								<th><?php esc_html_e("Ping", $this->plugin_name); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php echo $lnd_peer->bytes_sent; ?></td>
								<td><?php echo $lnd_peer->bytes_recv; ?></td>
								<td><?php echo isset($lnd_peer->sat_sent) ? $lnd_peer->sat_sent : 0; ?></td>
								<td><?php echo isset($lnd_peer->ping_time) ? $lnd_peer->ping_time : 0; ?></td>
							</tr>
						</tbody>
					</table>

					<form class="lnd-in-line" method="post" action="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=channels">
						<input type="hidden" name="lnd-open-channel" value="Y" />
						<input type="hidden" name="lnd-open-peer-pubkey" value="<?php echo $lnd_peer->pub_key; ?>" />

						<button type="submit" class="btn btn-connect">
							+ <?php esc_html_e("Open Channel", $this->plugin_name); ?>
						</button>
					</form>

					<form class="lnd-in-line" method="post" action="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=peers">
						<input type="hidden" name="lnd-disconnect-peer" value="Y" />
						<input type="hidden" name="lnd-disconnect-peer-id" value="<?php echo $lnd_peer->pub_key; ?>" />

						<button type="submit" class="btn btn-danger">
							<?php esc_html_e("Disconnect", $this->plugin_name); ?>
						</button>
					</form>

				</div>

			<? } ?>

		</div>

		<button type="submit" class="btn btn-secondary lnd-wp-expand-peers">
			<span class="lnd-wp-expand-peer-show">
				<?php esc_html_e("View Connected Peers", $this->plugin_name); ?>
			</span>
			<span class="lnd-wp-expand-peer-hide">
				<?php esc_html_e("Hide Connected Peers", $this->plugin_name); ?>
			</span>
		</button>

		<?php } ?>


	<?php }else if(!isset($_REQUEST['lnd-add-peer'])){ ?>

		<p class="lnd-ui-p">
			<?php esc_html_e("There are currently no active peer connections", $this->plugin_name); ?>...
		</p>
		<p class="lnd-ui-p">
			<?php esc_html_e("Synced to Chain", $this->plugin_name); ?>:
			<?php echo $this->lnd->get_node_synced() ? "100%" : "Syncing..."; ?>
		</p>

	<?php } ?>

	<?php if(!isset($_REQUEST['lnd-add-peer']) && !$dc_peer_confirm ){ ?>

		<form class="lnd-peer-form" method="post" action="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=peers">
			<input type="hidden" name="lnd-add-peer" value="Y" />

			<button type="submit" class="btn btn-secondary">
			&plus; <?php esc_html_e("Add Peer", $this->plugin_name); ?>
			</button>
		</form>

	<?php }else if(isset($_REQUEST['lnd-add-peer']) && $_REQUEST['lnd-add-peer'] == "Y") { ?>

			<p>
				<?php esc_html_e("Enter the address of the peer you wish to connect to", $this->plugin_name); ?>:
			</p>

			<form method="post" action="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=peers">
				<input type="hidden" name="lnd-add-peer-confirm" value="Y" />
				<input type="text" name="lnd-add-peer-id" class="form-control" placeholder="pubkey@ip:port" />

				<button type="submit" class="btn btn-secondary">
					&plus; <?php esc_html_e("Connect to Peer", $this->plugin_name); ?>
				</button>
			</form>

	<?php } ?>
</div>