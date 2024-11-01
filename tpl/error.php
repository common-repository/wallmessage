<div class="wrap wallmessage-wrap">
    <?php require_once WPWHATSAPPPM_TPL . 'header.php'; ?>
    <div class="wallmessage-wrap__main">
        <div class="fullbox-container " >            
			<div class="error-page">
				<p>
					<?php echo wp_kses(
										sprintf(
												__('Set WallMessage API configuration first!<a href="%sadmin.php?page=wallmessage-configs&tab=wallmessageapi"><span>Click Here!</span></a>'
													, 'wallmessage'
												)
											, admin_url()
										)
									,array(
										'a'=>array(
											'href'=>array()
										),
										'span'=>array()
									)
								);?>
				</p>
			</div>
        </div>
    </div>
</div>


