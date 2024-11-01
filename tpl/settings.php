
		<?php echo settings_errors('wallmessage-notices',true,false); ?>
		<div class="wallmessage-tab-content">
			<form method="post" action="options.php">
				<table class="form-table">
					<?php
					settings_fields($this->setting_name);
					do_settings_fields("{$this->setting_name}_{$active_tab}", "{$this->setting_name}_{$active_tab}");
					?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
	