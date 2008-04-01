<?php
/*
Plugin Name: Minty Fresh
Plugin URI: http://www.skullbit.com/
Description: Integrate the Mint Statistic package within your WordPress Admin.  Requires an installation of <a href="http://haveamint.com">Mint</a>.
Author: Skullbit
Version: 1.0
Author URI: http://www.skullbit.com
*/
if( !class_exists('MintyFreshPlugin') ){
	class MintyFreshPlugin{
		function MintyFreshPlugin() { //contructor
			add_action( 'admin_menu', array($this,'AddPanel') );
			if( $_POST['action'] == 'minty_fresh_update' )
				add_action( 'init', array($this,'SaveSettings') );
			if( !get_option('minty_panel') )
				add_action( 'init', array($this, 'DefaultSettings') );
			if( $_GET['page'] == 'minty-fresh' )
				wp_enqueue_script('jquery');
			if( get_option('minty_logging') )
				add_action( 'wp_head', array($this, 'MintyLoggingJS') );
			if( get_option('minty_panel') == 'top-window' && $_GET['page'] == 'minty-fresh')
				add_action( 'init', array($this, 'MintyWindow') );
				
			register_deactivation_hook( __FILE__, array($this, "UnsetSettings") );
		}
		
		function AddPanel(){
			add_options_page( 'Minty Fresh', 'Minty Fresh', 10, 'minty-fresh-settings', array($this, 'MintySettings') );
			if( get_option('minty_panel') == 'top-iframe' || get_option('minty_panel') == 'top-window' )
				add_menu_page( get_option('minty_panel_name'), get_option('minty_panel_name'), get_option('minty_user_role'), 'minty-fresh', array($this, 'MintyPanel') );
		}
		
		function DefaultSettings () {
			if( !get_option("minty_panel") )
			  	add_option("minty_panel","none");
			
			if( !get_option("minty_panel_name") )
			  	add_option("minty_panel_name","Mint");
			
			if( !get_option("minty_user_role") )
			  	add_option("minty_user_role","10");
				
			if( !get_option("minty_logging") )
			  	add_option("minty_logging","0");
				
			if( !get_option("minty_dir") )
			  	add_option("minty_dir","mint");
				
			if( !get_option("minty_script_location") )
			  	add_option("minty_script_location","head");
		}
		function UnsetSettings () {
			delete_option("minty_panel");
			delete_option("minty_panel_name");
			delete_option("minty_user_role");
			delete_option("minty_logging");
			delete_option("minty_dir");
			delete_option("minty_script_location");
		}
		function SaveSettings(){
			check_admin_referer('minty-fresh-update-options');
			update_option("minty_panel", $_POST['minty_panel']);
			update_option("minty_panel_name", $_POST['minty_panel_name']);
			update_option("minty_user_role", $_POST['minty_user_role']);
			update_option("minty_logging", $_POST['minty_logging']);
			update_option("minty_dir", $_POST['minty_dir']);
			update_option("minty_script_location", $_POST['minty_script_location']);
		}
		
		function MintySettings(){
			if( $_POST['notice'] )
				echo '<div id="message" class="updated fade"><p><strong>' . $_POST['notice'] . '.</strong></p></div>';
			?>
            <div class="wrap">
            	<h2><?php _e('Minty Fresh Settings', 'mintyfresh')?></h2>
                <form method="post" action="">
                	<?php if( function_exists( 'wp_nonce_field' )) wp_nonce_field( 'minty-fresh-update-options'); ?>
                    <table class="form-table">
                        <tbody>
                        	<tr valign="top">
                       			 <th scope="row"><label for="panel"><?php _e('Mint Panel', 'mintyfresh');?></label></th>
                        		<td><select name="minty_panel" id="panel">
                                  		<option value="top-iframe" <?php if( get_option('minty_panel') == "top-iframe" ) echo 'selected="selected"';?>><?php _e('Mint iFrame', 'mintyfresh');?></option>
                                        <option value="top-window" <?php if( get_option('minty_panel') == "top-window" ) echo 'selected="selected"';?>><?php _e('Open Mint in a new window', 'mintyfresh');?></option>
                                        <option value="none" <?php if( get_option('minty_panel') == "none" ) echo 'selected="selected"';?>><?php _e('No Panel', 'mintyfresh');?></option>
                                    </select></td>
                        	</tr>
                            <tr valign="top">
                            	<th scope="row"><label for="panel_name"><?php _e('Panel Name', 'mintyfresh');?></label></th>
                                <td><input type="text" name="minty_panel_name" id="panel_name" value="<?php echo get_option('minty_panel_name');?>" /></td>
                            </tr>
                            <tr valign="top">
                       			 <th scope="row"><label for="user_role"><?php _e('User Role', 'mintyfresh');?></label></th>
                        		<td><select name="minty_user_role" id="user_role">
                                  		<option value="10" <?php if( get_option('minty_user_role') == "10" ) echo 'selected="selected"';?>><?php _e('Administrator', 'mintyfresh');?></option>
                                        <option value="7" <?php if( get_option('minty_user_role') == "7" ) echo 'selected="selected"';?>><?php _e('Editor', 'mintyfresh');?></option>
                                        <option value="2" <?php if( get_option('minty_user_role') == "2" ) echo 'selected="selected"';?>><?php _e('Author', 'mintyfresh');?></option>
                                        <option value="1" <?php if( get_option('minty_user_role') == "1" ) echo 'selected="selected"';?>><?php _e('Contributor', 'mintyfresh');?></option>
                                        <option value="0" <?php if( get_option('minty_user_role') == "0" ) echo 'selected="selected"';?>><?php _e('Subscriber', 'mintyfresh');?></option>   
                                    </select><br><small><?php _e('User Role that can access the Mint Admin Panel','mintyfresh');?></small></td>
                        	</tr>
                            <tr valign="top">
                       			 <th scope="row"><label for="logging"><?php _e('Mint Logging', 'mintyfresh');?></label></th>
                        		<td><select name="minty_logging" id="logging">
                                  		<option value="1" <?php if( get_option('minty_logging') == "1" ) echo 'selected="selected"';?>><?php _e('Enabled', 'mintyfresh');?></option>
                                        <option value="0" <?php if( get_option('minty_logging') == "0" ) echo 'selected="selected"';?>><?php _e('Disabled', 'mintyfresh');?></option>
                                    </select></td>
                        	</tr>
                            <tr valign="top">
                            	<th scope="row"><label for="dir"><?php _e('Mint Directory', 'mintyfresh');?></label></th>
                                <td><input type="text" name="minty_dir" id="dir" value="<?php echo get_option('minty_dir');?>" /><br />
                                <strong style="color:#777;font-size:12px;"><?php _e('Mint Directory','mintyfresh');?>:</strong> <span style="font-size:0.9em;color:#999999;"><?php echo trailingslashit( get_option('siteurl') ); ?><span style="background-color: #fffbcc;"><?php echo get_option('minty_dir');?></span> <a href="<?php echo trailingslashit( get_option('siteurl') ) . get_option('minty_dir'); ?>"><?php _e('[preview]', 'mintyfresh');?></a></span></td>
                            </tr>
                            <tr valign="top">
                            	<th scope="row"><label for="script_location"><?php _e('Mint Script Location', 'mintyfresh');?></label></th>
                                <td><label><input type="radio" name="minty_script_location" id="script_location" value="head" <?php if( get_option('minty_script_location') == 'head' ) echo 'checked="checked"';?> /> <strong>head</strong></label><br />
                                <label><input type="radio" name="minty_script_location" id="script_location" value="footer"<?php if( get_option('minty_script_location') == 'footer' ) echo 'checked="checked"';?> /> <strong>footer</strong></label></td>
                            </tr>
                        </tbody>
                 	</table>
                    <p class="submit"><input name="Submit" value="<?php _e('Save Changes','mintyfresh');?>" type="submit" />
                    <input name="action" value="minty_fresh_update" type="hidden" />
                </form>
              
            </div>
           <?php
		}
		
		function MintyPanel(){
			
			if( get_option('minty_panel') == 'top-window'){
			echo '<script type="text/javascript">
					<!--
					window.open ("' . trailingslashit( get_option('siteurl') ) . get_option('minty_dir') . '")
					-->
					</script>';
			echo '<div class="wrap"><h2>' . get_option('minty_panel_name') . '</h2>';
			echo '<p>' . __('You\'ve set your ', 'mintyfresh') . get_option('minty_panel_name') . __(' Panel to open in a new window.  If a new window has not opened, ', 'mintyfresh') . '<a href="' . trailingslashit( get_option('siteurl') ) . get_option('minty_dir') . '" target="_blank">' . __('click here', 'mintyfresh') . '</a>.</p></div>';
			}else{
			
			?>
            <script type="text/javascript">
				
				jQuery(document).ready(function() {
					var h = jQuery(window).height();
					jQuery('#mintyfresh').height(h-250);
					jQuery(window).resize(function(){
						jQuery('#mintyfresh').height(h-250);
					});
				});
			</script>
            <iframe id="mintyfresh" width="100%" height="400px" frameborder="0" scrolling="auto" src="<?php echo trailingslashit( get_option('siteurl') ) . get_option('minty_dir');?>"></iframe>
            <?php
			}
		}
		
		function MintyLoggingJS(){
			echo '<script src="'.trailingslashit( str_replace('http', 'https', get_option('siteurl')) ) . get_option('minty_dir').'/?js" type="text/javascript"></script>';
		}
		
		function MintyWindow(){
			
		}
	}
} // END Class MintyFreshPlugin

if( class_exists('MintyFreshPlugin') ){
	$mintyfresh = new MintyFreshPlugin();
}
?>