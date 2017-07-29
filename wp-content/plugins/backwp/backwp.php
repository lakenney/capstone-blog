<?php
/*
Plugin Name: Backwp
Plugin URI: http://backwp.com
Description: Backup your wordpress site files and databases.
Version: 2.0.2
Author: Darell Sun
Author URI: http://backwp.com
*/


// Set max execution time
// ini_set('max_execution_time', 120);
//disable user abort
ignore_user_abort(true);

// Set constants
define('BACKWP_DIR', plugin_dir_path(__FILE__));
define('BACKWP_STORAGE', BACKWP_DIR.'/storage/'); 
define('BACKWP_INC', BACKWP_DIR.'/includes/');   
define('BACKWP_EXTENSIONS', BACKWP_DIR.'extensions/');
define('BACKWP_URL', plugin_dir_url(__FILE__));
define('BACKWP_OPTIONS', 'backwp_opts');
define('BACKWP_VERSION', '2.0.2');

//include extension files
if(file_exists(BACKWP_EXTENSIONS.'backwp_remote_storage_extension.php')){
  require_once(BACKWP_EXTENSIONS.'backwp_remote_storage_extension.php');	
}
if(file_exists(BACKWP_EXTENSIONS.'backwp_cron_jobs_extension.php')){
  require_once(BACKWP_EXTENSIONS.'backwp_cron_jobs_extension.php'); 
  add_filter('cron_schedules', 'backwp_intervals');
  //Actions for Cron job
  add_action('backwp_cron', 'backwp_cron_hook'); 
}
if(file_exists(BACKWP_EXTENSIONS.'backwp_email_notice_extension.php')){
  require_once(BACKWP_EXTENSIONS.'backwp_email_notice_extension.php');	
}
if(file_exists(BACKWP_EXTENSIONS.'backwp_exclusions_extension.php')){
  require_once(BACKWP_EXTENSIONS.'backwp_exclusions_extension.php');	
}
// include files
require_once('includes/backwp_tools.php');
require_once('includes/backwp_init.php');
require_once('includes/backwp_auto_update.php');
require_once('includes/backwp_extension_manager.php');
require_once('includes/pclzip.lib.php');

/*
// include all extenison files
foreach (glob(BACKWP_EXTENSIONS."*.php") as $filename)
{
    require_once $filename;
    //echo $filename."<br>";
}
*/
if(is_admin()){
   require_once('includes/backwp_admin.php');
}

// Initalize this plugin
$BackWP = new BackWP();

// When admin active this plugin
register_activation_hook(__FILE__, array(&$BackWP, 'activate'));
// When admin deactive this plugin
register_deactivation_hook(__FILE__, array(&$BackWP, 'deactivate'));

// Run the plugins initialization method
add_action('init', array(&$BackWP, 'initialize'));
// send html email filter
add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
?>