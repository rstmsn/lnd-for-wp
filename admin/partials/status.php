				<br />
				LND Version:
				<strong><?php echo $this->lnd->get_node_version(); ?></strong>
				<br />
				Synced to Chain:
				<strong>
				<?php

					if($this->lnd->get_node_synced()){
						echo '100%';
					} else {
						echo 'Synchronising...';
					}

				?>
				</strong>
				<br />
				Active Peers:
				<strong><?php echo $this->lnd->get_node_num_peers(); ?></strong>
				<br />
				Active Channels:
				<strong><?php echo $this->lnd->get_node_num_channels(); ?></strong>

				<p>
				<?php echo $this->lnd->draw_qr($this->lnd->get_node_pubkey()); ?>
				</p>

				Public Key:
				<input type="text" class="form-control" value="<?php echo $this->lnd->get_node_pubkey(); ?>" />
				</p>
				<p>
				Connection String:
				<input type="text" class="form-control" value="<?php echo $this->lnd->get_node_pubkey(); ?>&commat;<?php echo $lnd_hostname; ?>" />
				</p>