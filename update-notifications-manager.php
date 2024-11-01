<?php

/*
Plugin Name: Update Notifications Manager
Plugin URI: http://wordpress.org/plugins/update-notifications-manager/
Description: This plugin allows you to disable notifications for updates to plugins, themes, and new versions of WordPress
Version: 1.1.2
Author: GeekPress
Author URI: http://www.geekpress.fr/

	Copyright 2011 Jonathan Buttigieg
	
	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

class Update_Notifications {

	
	private $status = array(); // Set $status in array
	private $checkboxes = array(); // Set $checkboxes in array
	
	function Update_Notifications() 
	{
		

		// Add translations
		if (function_exists('load_plugin_textdomain'))
			load_plugin_textdomain( 'update-notifications-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
		
		// Add menu page
		add_action('admin_menu', array(&$this, 'add_submenu'));
		
		// Settings API
		add_action('admin_init', array(&$this, 'register_setting'));
		
		
		// load the values recorded
		$this->load_update_notifications();
		
	}
	
	
	
	/* Register settings via the WP Settings API */
	function register_setting() 
	{
		register_setting('_update_notifications', '_update_notifications', array(&$this, 'validate_settings'));		
	}
	
	
	/**
	*  method validate_settings
	*
	* @since 1.1
	*/
	function validate_settings( $input ) {

		$options = get_option( '_update_notifications' );
		
		foreach ( $this->checkboxes as $id ) {
			if ( isset( $options[$id] ) && !isset( $input[$id] ) )
				unset( $options[$id] );
		}
		
		return $input;
	}
	
	
	/**
	*  method add_submenu
	*
	* @since 1.0
	*/	
	function add_submenu() 
	{
		
		// Add submenu in menu "Settings"
		add_submenu_page( 'options-general.php', 'Update Notifications', __('Update Notifications','update-notifications-manager'), 'administrator', __FILE__, array(&$this, 'display_page') );
	}
	
	
	/**
	*  method load_update_notifications
	*
	* @since 1.0
	*/
	function load_update_notifications()
	{

		$this->status = get_option('_update_notifications');
		
		if( !$this->status ) return;
		
		foreach( $this->status as $id => $value ) {
		
			switch( $id ) {
				
				case 'plugin' :
					
					// Disable plugin updates
					remove_action( 'load-update-core.php', 'wp_update_plugins' );
					add_filter( 'pre_site_transient_update_plugins', create_function( '', "return null;" ) );
					wp_clear_scheduled_hook( 'wp_update_plugins' );
					
					break;
				
				case 'theme' :
					
					// Disable theme updates
					remove_action( 'load-update-core.php', 'wp_update_themes' );
					add_filter( 'pre_site_transient_update_themes', create_function( '', "return null;" ) );
					wp_clear_scheduled_hook( 'wp_update_themes' );

					break;
				
				case 'core' :
					
					// Disable WordPress core update
					add_filter( 'pre_site_transient_update_core', create_function( '', "return null;" ) );
					wp_clear_scheduled_hook( 'wp_version_check' );
					
					break;
			}
		}
		
	}
	
	
	/**
	*  method display_page
	*
	* @since 1.O
	*/
	function display_page() 
	{ 
		
		// Check if user can access to the plugin
		if (!current_user_can('update_core'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		
		?>
		
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php _e('Update notifications Manager','update-notifications-manager'); ?></h2>
			
			<form method="post" action="options.php">
				
			    <?php settings_fields('_update_notifications'); ?>
			    			    
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Check to disable notifications', 'update-notifications-manager') ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e('Check to disable notifications', 'update-notifications-manager') ?></span></legend>
								<label for="plugins_notify">
									<input type="checkbox" <?php checked(1, (int)$this->status['plugin'], true); ?> value="1" id="plugins_notify" name="_update_notifications[plugin]"> <?php _e('Disable plugin updates', 'update-notifications-manager') ?>
								</label>
								<br>
								<label for="themes_notify">
									<input type="checkbox" <?php checked(1, (int)$this->status['theme'], true); ?> value="1" id="themes_notify" name="_update_notifications[theme]"> <?php _e('Disable theme updates', 'update-notifications-manager') ?>
								</label>
								<br>
								<label for="core_notify">
									<input type="checkbox" <?php checked(1, (int)$this->status['core'], true); ?> value="1" id="core_notify" name="_update_notifications[core]"> <?php _e('Disable WordPress core update', 'update-notifications-manager') ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
				
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
				
			</form>
		</div>
			
	<?php
	}	
}

// Start this plugin once all other plugins are fully loaded
global $Update_Notifications; $Update_Notifications = new Update_Notifications();