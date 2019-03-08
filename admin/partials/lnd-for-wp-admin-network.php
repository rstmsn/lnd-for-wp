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

$lnd_network_info = $this->lnd->get_network_details();
$lnd_node_results = $this->handle_search_graph_for_node();

?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Network", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status lnd-wp-network">
	<span>
		<?php esc_html_e("LND Version", $this->plugin_name); ?>:
		<strong><?php echo $this->lnd->get_node_version(); ?></strong>
	</span>
	<span>
		<?php esc_html_e("Active Peers", $this->plugin_name); ?>:
		<strong><?php echo $this->lnd->get_node_num_peers(); ?></strong>
	</span>
	<span>
		<?php esc_html_e("Active Channels", $this->plugin_name); ?>:
		<strong><?php echo $this->lnd->get_node_num_channels(); ?></strong>
	</span>
	<span>
		<?php esc_html_e("Public Key", $this->plugin_name); ?>:
		<strong><?php echo $this->lnd->get_node_pubkey(); ?></strong>
	</span>
	<span>
		<?php esc_html_e("Current block height", $this->plugin_name); ?>:
		<strong>
			<?php echo $this->lnd->get_node_blockheight(); ?>
		</strong>
	</span>
	<span>
		<?php esc_html_e("Synced to chain", $this->plugin_name); ?>:
		<strong>
			<?php echo $this->lnd->get_node_synced() ? '100%' : 'Synchronising...';  ?>
		</strong>
	</span>
	<span>
		<?php esc_html_e("Visible Network Nodes", $this->plugin_name); ?>:
		<strong><?php echo $lnd_network_info->num_nodes ? $lnd_network_info->num_nodes : 0; ?></strong>
	</span>
	<span>
		<?php esc_html_e("Visible Network Channels", $this->plugin_name); ?>:
		<strong>
			<?php echo $lnd_network_info->num_channels ? $lnd_network_info->num_channels : 0; ?>
		</strong>
	</span>

	<p>
		<form method="post" action="?page=<?php echo $_REQUEST['page']?>&f=network">
			<input type="hidden" name="lnd-search-nodes" value="Y" />

			<div class="form-group">
		    	<label for="lnd-search-node">
			    	<?php esc_html_e("Search the Network Graph", $this->plugin_name); ?>:
				</label>
				<input type="text" class="form-control" name="lnd-search-node" id="lnd-search-node" placeholder="<?php esc_html_e("Node alias, IP or public key", $this->plugin_name); ?>...">
		  	</div>

		  	<button type="submit" class="btn btn-secondary">
				<?php esc_html_e("Search", $this->plugin_name); ?>
			</button>
		</form>
	</p>

	<?php if($lnd_node_results === 0){ ?>
		<p>0 <?php esc_html_e("Results", $this->plugin_name); ?></p>
	<?php }else if($lnd_node_results){ ?>
		<p>
			<?php
				esc_html_e("Found", $this->plugin_name);
				echo ' ' . count($lnd_node_results) . ' ';
				esc_html_e("Matching Nodes", $this->plugin_name);
			?>...
		</p>

		<?php foreach($lnd_node_results as $lnd_node){ ?>
			<div class="lnd-peer-contain lnd-network-nodes">
				<h3>
					<?php echo $lnd_node->alias ? $lnd_node->alias : 'Alias Unknown'; ?>
				</h3>

				<?php echo $this->lnd->draw_qr($lnd_node->pub_key); ?>

				<p>
					<?php esc_html_e("Public Key", $this->plugin_name); ?>:<br />
					<strong><?php echo $lnd_node->pub_key; ?></strong>
				</p>

				<form method="post" action="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=peers">
					<input type="hidden" name="lnd-add-peer-confirm" value="Y" />
					<input type="hidden" name="lnd-add-peer-id" value="<?php echo $lnd_node->pub_key; ?>@<?php echo $lnd_node->addresses[0]->addr; ?>" />

					<button type="submit" class="btn btn-primary btn-connect">
						&plus; <?php esc_html_e("Connect to Peer", $this->plugin_name); ?>
					</button>
				</form>

			</div>
		<?php } ?>

	<?php } ?>
</div>