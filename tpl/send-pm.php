<script type="text/javascript">
    jQuery(document).ready(function () {
        
        

         jQuery("select#select_sender").change(function () {
            var get_method = "";
            jQuery("select#select_sender option:selected").each(
                function () {
                    get_method += jQuery(this).attr('id');
                }
            );
             if (get_method == 'wc_users') {
                jQuery(".wallmessage-value").hide();
                jQuery(".wallmessage-wc-users").fadeIn();
            }else if (get_method == 'wp_tellephone') {
                jQuery(".wallmessage-value").hide();
                jQuery(".wallmessage-numbers").fadeIn();
                jQuery("#wp_get_number").focus();
             } 
        });
        
    });
</script>


        <div class="wallmessage-tab-content" style="padding-top: 20px;">
            <div class="meta-box-sortables">
                <div class="postbox">
                    
                    <div class="inside">
					
					<?php $url = add_query_arg( array('page'=>'wallmessage-pm'), admin_url('admin.php')); ?>
                        <form method="post" action="<?php echo esc_url( $url ); ?>">
                            <?php wp_nonce_field('update-options'); ?>
                            <table class="form-table">
                                
                                <tr valign="top">
                                    <td>
                                        <select name="wp_send_to" id="select_sender">
                                            
											<option value="wp_tellephone" id="wp_tellephone"><?php _e('Number(s)', 'wallmessage'); ?></option>
                                            <option value="wc_users" id="wc_users">
                                                <?php _e('WooCommerce\'s Customers', 'wallmessage'); ?>
                                            </option>
                                            
                                        </select>

                                        <span class="wallmessage-value wallmessage-wc-users" style="display: none;">
                                            <span><?php echo wp_kses(sprintf(__('<b>%s</b> Customers have the mobile number.', 'wallmessage'), count($woocommerceCustomers)),array('b'=>array())); ?></span>
                                        </span>

                                        <span class="wallmessage-value wallmessage-numbers">
                                            <div class="clearfix"></div>
                                            <textarea cols="80" rows="5" style="direction:ltr;margin-top: 10px;" id="wp_get_number" name="wp_get_number"></textarea>
                                            <div class="clearfix"></div>
                                             <div style="font-size: 14px"><?php esc_html_e('Separate the numbers with comma (,) or enter in each lines.', 'wallmessage'); ?></div> 
                                            <?php if ($this->wapp_pm->validateNumber) : ?>
                                                <div ><?php echo esc_html($this->wapp_pm->validateNumber); ?></div>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                </tr>
                              
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="wp_get_message"><?php _e('Message', 'wallmessage'); ?>:</label>
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        <textarea dir="auto" cols="80" rows="5" name="wp_get_message" id="wp_get_message"></textarea><br/>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                        <p class="submit" style="padding: 0;">
                                            <input type="submit" class="button-primary" name="SendPM" value="<?php _e('Send PM', 'wallmessage'); ?>"/>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>

