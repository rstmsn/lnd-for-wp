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

?>

<h2>
	<a href="admin.php?page=lnd-for-wp">
		<?php echo $this->lnd->get_node_alias(); ?>
	</a> &rarr; <?php esc_html_e("Shortcodes", $this->plugin_name); ?>
</h2>

<div class="lnd-wp-status">
	<p>
		The following shortcodes allow you to include LND functionality on your Wordpress website. <br />
		Simply paste the relevant shortcode into a page or post and modify the options as necessary.
	</p>

	<p>
		<strong>[lnd current-version]</strong><br />
		Prints the current version of LND.
	</p>

	<p>
		<strong>[lnd lightning_invoice ajax=true amount=2000 memo="Web Donation"]</strong><br />
		Render a Lightning Invoice Request Dialog
	</p>

	<p>
		<strong>[lnd on_chain_address generate_new=true]</strong><br />
		Prints an on chain Bitcoin address.
	</p>
</div>