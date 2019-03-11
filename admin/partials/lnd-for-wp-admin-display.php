<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://github.com/rstmsn/lnd-for-wp
 * @since      1.0.0
 *
 * @package    LND_For_WP
 */

$this->handle_settings_form_post();
$settings = $this->load_default_settings();

?>

<?php if($settings->hide_config){ ?>
	<form><input type="hidden" class="lnd-hide-config" value="true" /></form>
<?php } ?>

<div class="lnd-wp-contain">
	<h1><?php esc_html_e("LND For WP", $this->plugin_name); ?></h1>
	<hr />
	<p>
		<?php esc_html_e("Welcome to LND for WordPress.", $this->plugin_name); ?><br />
		<?php esc_html_e("Integrate, manage & use your LND Node right from your WordPress website.", $this->plugin_name); ?>
	</p>
	<p>
		<?php esc_html_e("Get started by configuring your LND node connection details below.", $this->plugin_name); ?>
	</p>

	<div class="lnd-wp-scroll-marker"></div>

	<div class="lnd-wp-console-wrapper">

	<?php if(isset($_REQUEST['core_message'])){ ?>
		<div class="lnd-wp-alert">
			<?php esc_html_e(sanitize_text_field($_REQUEST['core_message']), $this->plugin_name); ?>
		</div>
	<?php } ?>

	<?php if(!$this->is_ssl() && $settings->lnd_ssl_warn == 'true'){ ?>

		<div class="lnd-wp-alert lnd-wp-alert-critical">
			<form method="post" action="">
			<input type="hidden" name="lnd-mute-ssl-warning" value="Y" />
				<p>

					<?php esc_html_e("No HTTPS / SSL detected. Your connection to WordPress may be insecure...", $this->plugin_name); ?>

					<button type="submit" class="btn btn-primary">
						<?php esc_html_e("Remind me Later", $this->plugin_name); ?>
					</button>
				</p>
			</form>
		</div>

	<?php } ?>

	<div class="lnd-wp-configure-button-wrap">
		<div class="lnd-wp-node-settings-expand">
			<a class="expand">
				<?php esc_html_e("Configure Node", $this->plugin_name); ?> &darr;
			</a>
		</div>
	</div>

		<div class="lnd-wp-configure">
			<div class="lnd-wp-node-settings">

				<div class="settings-left">
					<h2><?php esc_html_e("Configure Node", $this->plugin_name); ?></h2>
				</div>
				<div class="settings-right">
					<a class="collapse">&uarr;</a>
				</div>

				<form action="" method="post" enctype="multipart/form-data" id="lnd-node-settings">
					<input type="hidden" name="lnd-update-settings" value="Y" />
					<input type="hidden" name="MAX_FILE_SIZE" value="30000" />

					<p>
						<label for="lnd-hostname">
							<?php esc_html_e("Node Hostname", $this->plugin_name); ?>:
						</label>
						<input type="text" name="lnd-hostname" placeholder="ip:port" class="med" value="<?php echo $settings->lnd_hostname; ?>"
					</p>

					<p>
						<label for="lnd-attach-macaroon">
							<?php esc_html_e("Macaroon File", $this->plugin_name); ?>:
						</label>
						<input type="text" name="lnd-macaroon-name" class="lnd-browse-file" value="<?php echo $settings->lnd_macaroon_name; ?>">
						<input class="lnd-inputfile lnd-input-macaroon" name="lnd-attach-macaroon" type="file" />
						<button type="button" class="btn btn-primary btn-browse-file lnd-upload-macaroon">
							<?php esc_html_e("&larr;", $this->plugin_name); ?>
						</button>
					</p>

					<p>
						<label for="lnd-attach-tls-ct">
							<?php esc_html_e("TLS Certificate", $this->plugin_name); ?>:
						</label>
						<input type="text" name="lnd-tls-cert-name" class="lnd-browse-file" value="<?php echo $settings->lnd_tls_cert_name; ?>">
						<input class="lnd-inputfile lnd-input-tls-cert" name="lnd-attach-tls-cert" type="file" />
						<button type="button" class="btn btn-primary btn-browse-file lnd-upload-tls">
							<?php esc_html_e("&larr;", $this->plugin_name); ?>
						</button>
					</p>

					<p>
						<label for="lnd-t">
							<?php esc_html_e("Connection Timeout", $this->plugin_name); ?>:
						</label>
						<input type="text" name="lnd-conn-timeout" placeholder="10" class="small" value="<?php echo $settings->lnd_conn_timeout; ?>" />
					</p>

					<p>
						<label for="lnd-force-ssl">
							<?php esc_html_e("Force SSL", $this->plugin_name); ?>:
						</label>
						<input name="lnd-force-ssl" type="checkbox" name="SSL" <?php echo $settings->lnd_force_ssl ? 'checked' : ''; ?> /><br />
					</p>

					<button type="submit" class="btn btn-primary">
						<?php esc_html_e("Save Configuration", $this->plugin_name); ?> &rarr;
					</button>
				</form>

			</div>
		</div>

		<div class="lnd-wp-console">
			<div class="lnd-wp-console-content">

				<?php $this->render_console_content(); ?>

				<?php if($this->lnd->is_node_reachable()){ ?>

					<div class="lnd-wp-links">
						<a href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=payments">
							<?php esc_html_e("Pay", $this->plugin_name); ?>
						</a>
						<a href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=request">
							<?php esc_html_e("Request", $this->plugin_name); ?>
						</a>

						<div class="lnd-wp-links-more">
							<a href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=channels">
								<?php esc_html_e("Channels", $this->plugin_name); ?>
							</a>
							<a href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=peers">
								<?php esc_html_e("Peers", $this->plugin_name); ?>
							</a>
							<a href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=network">
								<?php esc_html_e("Network", $this->plugin_name); ?>
							</a>
							<a href="?page=<?php echo sanitize_text_field($_REQUEST['page']); ?>&f=shortcodes">
								<?php esc_html_e("Shortcodes", $this->plugin_name); ?>
							</a>
						</div>

						<div class="lnd-wp-links-expand">
							<a><?php esc_html_e("More Options", $this->plugin_name); ?><br />&darr;</a>
						</div>
					</div>

				<?php } ?>

			</div>
		</div>

	</div>
</div>