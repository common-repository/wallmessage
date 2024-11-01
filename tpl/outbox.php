<div class="wrap wallmessage-wrap">
	<?php require_once WPWHATSAPPPM_TPL . 'header.php'; ?>
    <div class="wallmessage-wrap__main">
		<div class="wallmessage-wrap_outbox">
			<form id="outbox-filter" method="get">
				<?php $_request_page = sanitize_text_field($_REQUEST['page']) ?>
				<input type="hidden" name="page" value="<?php echo esc_attr($_request_page); ?>"/>
				<?php $list_table->search_box(__('Search', 'wallmessage'), 'search_id'); ?>
				<?php $list_table->display(); ?>
			</form>
		</div>
	</div>
</div>
