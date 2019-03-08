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

$this->handle_form_unlock_wallet();

?>
			<h2>
				<a href="admin.php?page=lnd-for-wp">
					<?php echo $this->lnd->get_node_alias(); ?>
				</a> &rarr; <?php esc_html_e("Unlock Wallet", $this->plugin_name); ?>
			</h2>

			<div class="lnd-wp-status">

				<span class="lnd-unreachable"></span>

				<div class="lnd-wp-links">
					<a href="?page=<?php echo $_REQUEST['page']?>&f=unlock">
						<?php esc_html_e("Unlock Wallet", $this->plugin_name); ?>
					</a>
				</div>

			</div>