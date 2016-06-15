<?php
/**
 * @package Cokies Lock
 * @version 1.0
 */
/*
Plugin Name: Cookies Lock
Plugin URI: http://wordpress.org/plugins/cookies-lock
Description: Hello, we just allow to go to admin section only for trusted peoples.
Author: Denis Tolochko
Version: 1.0
Author URI: http://taanab.com/
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$cl_key = get_option('cl_key');
if(empty($cl_key)){
	add_option('cl_key', substr( md5(rand()), 0, 3));
	$cl_key = get_option('cl_key');
}

$cl_value = get_option('cl_value');
if(empty($cl_value)){
        add_option('cl_value', substr( md5(rand()), 0, 5));
        $cl_value = get_option('cl_value');
}

$cl_curl = get_option('cl_url');
if(empty($cl_url)){
        add_option('cl_url', substr( md5(rand()), 0, 7));
        $cl_url = get_option('cl_url');
}


function checkcookieredirect() {
	$cl_value = get_option('cl_value');
	$cl_key = get_option('cl_key');
	if($_COOKIE[$cl_key] !== $cl_value){
		wp_redirect(home_url('error-404.php'), 302); exit;
		exit();
	}
}

function setcookieforadmin() {
	$cl_key = get_option('cl_key');
	$cl_value = get_option('cl_value');
	setcookie($cl_key,$cl_value,time() + ( 10 * 365 * 24 * 60 * 60 ));
}

function deactivateplugin() {
	unset( $_COOKIE[$cl_key] );
	setcookie( $cl_key, '', time() - ( 15 * 60 ) );
	delete_option('cl_key');
	delete_option('cl_value');
	delete_option('cl_url');
}

function activateplugin() {
	setcookieforadmin();
}

function hidenurl() {
	$cl_url = get_option('cl_url');

	$uri = $_SERVER['REQUEST_URI'];
	$uri = trim($uri, '/');
	if($uri == $cl_url){
	        setcookieforadmin();
	}
}

function admin ()
{
    if ( function_exists('add_options_page') ) 
    {
        add_options_page('Cookies Lock Options','Cookies Lock', 8, __FILE__, 'admin_form' );
    }
}

function admin_form() {
$cl_key = get_option('cl_key');
$cl_value = get_option('cl_value');
$cl_url = get_option('cl_url');

    if ( isset($_POST['submit']) ) 
    {   
       if ( function_exists('current_user_can') && 
            !current_user_can('manage_options') )
                die ( _e('Hacker?', 'cookieslock') );

        if (function_exists ('check_admin_referer') )
        {
            check_admin_referer('cookieslock_form');
        }

        $cl_url = $_POST['cl_url'];
        $cl_key = $_POST['cl_key'];
        $cl_value = $_POST['cl_value'];

        update_option('cl_url', $cl_url);
        update_option('cl_key', $cl_key);
        update_option('cl_value', $cl_value);
    }
    ?>
    <div class='wrap'>
        <h2><?php _e('Cookies Lock Settings', 'cookieslock'); ?></h2>

        <form name="cookieslock" method="post" 
            action="<?php echo $_SERVER['PHP_SELF']; ?>?page=cookieslock%2Fcookiechecker.php&amp;updated=true">

            <?php 
                if (function_exists ('wp_nonce_field') )
                {
                    wp_nonce_field('cookieslock_form'); 
                }
            ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Secret url:', 'cookiesclock'); ?></th>

                    <td>
                        <input type="text" name="cl_url" 
                        size="80" value="<?php echo $cl_url; ?>" />
			<?php if($cl_url){?>
			<p class="description" id="tagline-description"><?php _e( 'To set cookie go to: ' . get_site_url() . "/" . $cl_url  ) ?></p>
			<?php } ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Secret Key:', 'cookieslock'); ?></th>

                    <td>
                        <input type="text" name="cl_key" 
                            size="80" value="<?php echo $cl_key; ?>" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Secret Value:', 'cookieslock'); ?></th>

                    <td>
                        <input type="text" name="cl_value"
                            size="80" value="<?php echo $cl_value; ?>" />
                    </td>
                </tr>
            </table>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="cl_key,cl_value,cl_utl" />
            <p class="submit">
            <input type="submit" name="submit" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
    </div>
    <?php
}

function settings($links) { 
  $settings_link = '<a href="options-general.php?page=cookieslock%2Fcookiechecker.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}

$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'settings' );
add_action( 'login_head', 'checkcookieredirect' );
register_activation_hook( __FILE__, 'activateplugin' );
register_deactivation_hook( __FILE__, 'deactivateplugin' );
add_action( 'wp', 'hidenurl' );
add_action('admin_menu',  'admin' );
?>
