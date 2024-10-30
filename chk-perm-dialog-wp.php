<?php
/**
 * @package Chk_Perm_Dialog
 * @version 2023.08
 */
/*
Plugin Name: Check Permission Dialogue
Description: This plugin offers a simple dialogue for opting-in to tracking cookies.  It ensures that known trackers for google, facebook, and crazyegg will not be loaded until the user explicitly allows it.  Dialogues are modelled on browser permission requests (such as those for location data) for visual consistency.  
Author: Dan M
Version: 2023.08
Author URL: https://danmazzei.com
*/
?>
<?php
//load the function library which we will bind
//this function library is not wordpress-dependent and can work on any php site
include('chk-perm-functions.php');

//before output, enable the filter listener and check user settings
//all output will be redirected to an output buffer until chk_perm_load_end is called
//filters will be applied to any text output after this function is called
function chk_perm_wp_load_start(){
	//save setting if this was a setting request for permissions
	chk_perm_handle_getback('cookie');
	chk_perm_load_start();
}
add_action('wp_loaded','chk_perm_wp_load_start',9);

//display the dialogue (in the DOM this is just above the footer but because it is position:fixed it should display at the top)
function chk_perm_wp_show_widget(){
	chk_perm_show_widget('cookie','','','fixed',plugin_dir_url(__FILE__).'img/',false);
}
add_action('wp_footer','chk_perm_wp_show_widget',20);
function chk_perm_wp_load_js(){
	chk_perm_load_js('cookie');
}
add_action('wp_footer','chk_perm_wp_load_js',21);

//after output, apply filters and relay the output to the screen (it was redirected to a buffer prior to this)
function chk_perm_wp_load_end(){
	chk_perm_load_end();
}
//NOTE: this is the latest in the wordpress processing that I can call this load_end function
//any tracking scripts therefore MUST be placed prior to the wp_footer() call or this won't work!!!!!
add_action('wp_footer','chk_perm_wp_load_end',100);


//enable a shortcode for clearing permission settings
function chk_perm_wp_clear_link($attrs,$content,$shortcode_tag){
	//set sane defaults for options that can be customized
	$attrs = shortcode_atts( array(
		'perm_key' => 'cookie',
		'link_text' => 'Clear Permissions'
	), $attrs, 'chk_perm_clear_link' );
	
	//NOTE: as of wordpress 5.9 it looks like wordpress now expects a string to be returned by add_shortcode
	//so in order to acheive that I'm using a buffer around the output function
	//and then returning the output as a string
	ob_start(NULL,0,(PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE));
	chk_perm_clear_link($attrs['perm_key'],$attrs['link_text']);
	$clear_link_html=ob_get_clean();
	return $clear_link_html;
}
add_shortcode('chk_perm_clear_link','chk_perm_wp_clear_link');

//load css with the header
function chk_perm_wp_load_css() {
	wp_register_style( 'chk-perm-widget-css',  plugin_dir_url( __FILE__ ) . 'chk-perm-widget.css' );
	wp_enqueue_style( 'chk-perm-widget-css' );
	wp_register_style( 'chk-perm-links-css',  plugin_dir_url( __FILE__ ) . 'chk-perm-links.css' );
	wp_enqueue_style( 'chk-perm-links-css' );
}
add_action('wp_enqueue_scripts','chk_perm_wp_load_css',10);

//TODO: add admin_init hooks for any admin-configurable settings when/if such settings are made

//configure the wp admin display to include this settings page in the menu
function chk_perm_settings_menu() {
	chk_perm_wp_load_css();
	return add_options_page(
		'Permission Dialog Settings', //page <title></title>
		'Permission Dialog', //navbar display within the "Settings" menu
		'administrator', //required capability to view
		'chk_perm_dialog', //slug (must be unique)
		'chk_perm_dialog_settings_html' //function to output the settings page
	);
}
add_action('admin_menu','chk_perm_settings_menu');

?>
