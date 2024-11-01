<ul class="wallmessage-tab">
	<?php
	$allowed_html_tag = array(
							'li'=>array(
								'class'=>array(),
							),
							'a'=>array(
								'href'=>array(),
								'title'=>array(),
								'class'=>array(),
							)
						);
	foreach ($settings->get_tabs() as $tab_id => $tab_name) {
		$tab_url = add_query_arg(array(
			'settings-updated' => false,
			'page'			   => ($tab_id == 'sendpm')? 'wallmessage-pm':'wallmessage-configs',
			'tab'              => $tab_id,
		));

		$active      = $active_tab == $tab_id ? 'active' : '';
		$proLockIcon = '';


		$tab_node = '';
		$tab_node .='<li class="tab-' . $tab_id  . '"><a href="' . esc_url($tab_url) . '" title="' . esc_attr($tab_name) . '" class="' . $active . '">';
		$tab_node .= $tab_name;
		$tab_node .= '</a>' . $proLockIcon . '</li>';
		echo wp_kses($tab_node,$allowed_html_tag);
	}
	?>

	<li class="tab-link"><a target="_blank" href="<?php echo esc_url(WALLMESSAGE_HOW_TO_USE); ?>"><?php esc_html_e('How to use Plugin', 'wallmessage'); ?></a></li>
	<li class="tab-link"><a target="_blank" href="<?php echo esc_url(WALLMESSAGE_ABOUT_SITE); ?>"><?php esc_html_e('Plugin website', 'wallmessage'); ?></a></li>
	<li class="plugin-version"><p  class="alignright"><?php echo esc_html(sprintf(__('Wallmessage plugin V%s','wallmessage'), WPWHATSAPPPM_VERSION)); ?>	</p></li>
	<?php if(!empty(WALLMESSAGE_LEFT_MENU_IFRAME)): ?>
		<li>	
			<div class="wallmessage-iframe-container">
				<iframe id="wallmessage-iframe" class="wallmessage-iframe"
					src="<?php echo esc_url(WALLMESSAGE_LEFT_MENU_IFRAME); ?>"
					scrolling="no"
					seamless="seamless">
				</iframe>
			</div>
		</li>
	<?php endif; ?>
</ul>


