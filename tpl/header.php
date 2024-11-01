<?php $option = get_option('wallmessage_settings'); ?>
<div class="wallmessage-header-banner">

	<div class="banner-box">
            <h3><?php esc_html_e('Welcome to', 'wallmessage');?> </h3>
			<img class="header-logo" src="<?php echo  esc_url( WPWHATSAPPPM_IMG .'/logo.svg'); ?>" alt="<?php esc_attr_e('Wallmessage', 'wallmessage')?>">
			<h5><?php esc_html_e('Advanced plugin to intergrate Whatsapp API and Woocommerce', 'wallmessage') ?></h5>
    </div>
	<div class="page-title">
			<img class="title-shape" src="<?php echo  esc_url( WPWHATSAPPPM_IMG .'/title_shape.png'); ?>" alt="Title Shape">
            <h3><?php echo esc_html(wp_unslash(isset($page_title)?$page_title:'')); ?></h3>
    </div>

</div>
