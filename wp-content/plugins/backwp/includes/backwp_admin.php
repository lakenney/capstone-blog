<?php
/*
 *  Admin functions to set and save settings of the plugin
*/
require_once('backwp_pages.php');
require_once('backwp_meta_boxs.php');
require_once('backwp_list_table.php');
require_once('backwp_tools.php');

/* Initialize the plugin admin functions */
add_action('init', 'backwp_admin_init');

// Process ajax request
add_action('wp_ajax_backwp_action', 'backwp_action_handler');
add_action('wp_ajax_nopriv_backwp_action', 'backwp_action_handler');
add_action('wp_ajax_backwp_monitor', 'backwp_monitor_handler');
add_action('wp_ajax_nopriv_backwp_monitor', 'backwp_monitor_handler');

// Backwp ajax request handler
function backwp_action_handler(){	 
   backwp_backup_now($_POST['db'], $_POST['files']);   
   die();
}

// Backwp ajax request handler
function backwp_monitor_handler(){
	 $options = get_option(BACKWP_OPTIONS);
	 $log_file = WP_CONTENT_DIR . '/uploads/backwp_backups_log/log.txt';	  
   // check log file
   if ( file_exists( $log_file ) ) {
			 $messages = file( $log_file );						
			 foreach( $messages as $message ) {
				  $logs .= $message;
			 }			
	 }else{
	   $logs = "can't log!";	
	 }   
   $logs = "Start Backup...\n".$logs;

   // check backup temp directory
   $backup_temp_dir = WP_CONTENT_DIR . '/uploads/backwp_backups_temp/';	
   $backup_dir = WP_CONTENT_DIR . '/uploads/backwp_backups/';     
   
   if (glob($backup_temp_dir . "*.zip.*") != false){
     // zip progress running
     $status = false;     
     $size = get_temp_file_size($backup_temp_dir);
   }else if(glob($backup_temp_dir . "*.zip") != false){
   	 // zip progress complete
   	 $status = true;   	  
     $size = get_temp_file_size($backup_temp_dir); 
     // save archive size to plugin option     
     $options['archive_size'] = $size;       
	   update_option(BACKWP_OPTIONS, $options);
   	 $logs .= date("H:i:s").': Backup Complete!';
   	 // rename backup filename from temp directory to archives directory
   	 rename_backup_file($backup_temp_dir, $backup_dir);
   	 // append message to the end of file
   	 backwp_backup_log('action', 'Backup Complete!', $log_file);
   }else{
     $status = true;     
     $size = $options['archive_size'];    	
   } 
  
   $response = array('status'=>$status, 'logs'=>$logs, 'size'=>$size);
   echo json_encode($response);   
   die();	
}

// Define icon styles for the custom post type
function backwp_icons() {
?>
<style type="text/css" media="screen">
        #toplevel_page_backwp .wp-menu-image{
            background: url(<?php echo BACKWP_URL; ?>includes/images/backwp-menu.png) no-repeat;
        }
        #toplevel_page_backwp:hover .wp-menu-image{
            background: url(<?php echo BACKWP_URL; ?>includes/images/backwp-menu-hover.png) no-repeat;
        }        
</style>
<?php
}
add_action( 'admin_head', 'backwp_icons' );

function backwp_admin_init(){
			
    add_action('admin_menu', 'backwp_settings_init');
    add_action('admin_init', 'backwp_save_options_handler');
    add_action('admin_init', 'backwp_admin_style');
    add_action('admin_init', 'backwp_admin_script');   
}

// add menu pages
function backwp_settings_init(){
   global $backwp; 
   $icon=  BACKWP_URL .'/includes/images/backwp-menu.png';
   $options = get_option(BACKWP_OPTIONS);
	// Declare new class to fix the PHP Warning.
	if (!isset($backwp))
		$backwp = new stdClass();
   add_menu_page('Backwp', 'Backwp', 'manage_options', 'backwp', 'backwp_main_page', 'div'); 
   $backwp->main = add_submenu_page('backwp', 'Backup', 'Backup', 'manage_options', 'backwp', 'backwp_main_page' );
   $backwp->archives = add_submenu_page('backwp', 'Backup Archives', 'Archives', 'manage_options', 'backwp-archives', 'backwp_archives_page' );  
   $backwp->premium = add_submenu_page('backwp', 'BackWP Premium Extensions', 'Premium Extensions', 'manage_options', 'backwp-premium-extensions', 'backwp_premium_extensions_page' );  
   
   if(file_exists(BACKWP_EXTENSIONS.'backwp_remote_storage_extension.php')){
     $backwp->remote_storage = add_submenu_page('backwp', 'Backwp Remote Storage', 'Remote Storage', 'manage_options', 'backwp-remote-storage', 'backwp_remote_storage_page' );
     add_action( "load-{$backwp->remote_storage}", 'backwp_remote_storage_settings');
   }   
   if(file_exists(BACKWP_EXTENSIONS.'backwp_cron_jobs_extension.php')){
     $backwp->cron_jobs = add_submenu_page('backwp', 'Backwp Cron Jobs', 'Cron Jobs', 'manage_options', 'backwp-cron-jobs', 'backwp_cron_jobs_page' );
     add_action( "load-{$backwp->cron_jobs}", 'backwp_cron_jobs_settings');
   }   
   if(file_exists(BACKWP_EXTENSIONS.'backwp_email_notice_extension.php')){
     $backwp->email_notice = add_submenu_page('backwp', 'Backwp Email Notice', 'Email Notice', 'manage_options', 'backwp-email-notice', 'backwp_email_notice_page' );
   }
   
   if(file_exists(BACKWP_EXTENSIONS.'backwp_exclusions_extension.php')){
     $backwp->exclusions = add_submenu_page('backwp', 'Backwp Exclusions', 'Exclusions', 'manage_options', 'backwp-exclusions', 'backwp_exclusions_page' );
   }
   
   /* Make sure the meta boxes are loaded. */
   add_action( "load-{$backwp->main}", 'backwp_main_settings');  
}

// handle admin save options
function backwp_save_options_handler(){
	 $options = get_option(BACKWP_OPTIONS);
	 	 
	 // backup action
	 if(isset($_POST['backup_now'])){	 	  
	 	  $param = '&db='.$_POST['database'].'&files='.$_POST['files'];
	 	  $redirect =  admin_url( 'admin.php?page=backwp&running=true'.$param );
	    wp_redirect($redirect);	    		    
	 }
	
	 // delete backup file
	 if(isset($_GET['action']) && $_GET['action']=='delete-backup'){
	 	  $selected_backup_file = WP_CONTENT_DIR . '/uploads/backwp_backups/' . $_GET['name'];
	    if (file_exists($selected_backup_file)) {
				 unlink($selected_backup_file);
			}
	    $redirect = admin_url( 'admin.php?page=backwp&deleted=true' ); 
      wp_redirect($redirect); 	   	
	 } 
	 
	 // save remote storage settings
	 if(isset($_POST['backwp_save_remote_storage'])){
	    $options['ftp_enabled'] = $_POST['ftp_enabled'];
	    $options['ftp_hostname'] = $_POST['ftp_hostname'];
	    $options['ftp_username'] = $_POST['ftp_username'];
	    $options['ftp_password'] = $_POST['ftp_password'];
	    $options['remote_directory'] = $_POST['remote_directory'];       
	    update_option(BACKWP_OPTIONS, $options);
	    $redirect =  admin_url( 'admin.php?page=backwp-remote-storage&updated=true' );
	    wp_redirect($redirect);	 			
	 }
	 
	 // save settings
	 if(isset($_POST['backwp_save_email_notice'])){	    
	    $options['send_email_when'] = $_POST['send_email_when'];
	    $options['email_address'] = $_POST['email_address'];	         
	    update_option(BACKWP_OPTIONS, $options);
	    $redirect =  admin_url( 'admin.php?page=backwp-email-notice&updated=true' );
	    wp_redirect($redirect);	 			
	 }
	 
	 // save exclusions settings
	 if(isset($_POST['backwp_save_exclusions'])){	    
	    $options['excluded_tables'] = $_POST['excluded_tables'];
	    $options['excluded_dirs'] = $_POST['excluded_dirs'];       
	    update_option(BACKWP_OPTIONS, $options);
	    $redirect =  admin_url( 'admin.php?page=backwp-exclusions&updated=true' );
	    wp_redirect($redirect);	 			
	 }
	 
	 // save cron job settings
	 if(isset($_POST['backwp_save_cron_jobs'])){
	    $options['cron_backup_type'] = $_POST['cron_backup_type'];
	    $options['cron_backup_interval'] = $_POST['cron_backup_interval']; 
	    $options['wp_internal_cron'] = $_POST['wp_internal_cron']; 
	    if($_POST['wp_internal_cron'] == 'enable'){
	      $options['wp_internal_cron_next_run'] = time() + 60 * 60 * $options['cron_backup_interval'];	         
	    }else if($_POST['wp_internal_cron'] == 'disable'){
	      $options['wp_internal_cron_next_run'] = 'inactive';	
	    }
	    update_option(BACKWP_OPTIONS, $options);
	    $redirect =  admin_url( 'admin.php?page=backwp-cron-jobs&updated=true' );
	    wp_redirect($redirect);		
	 }
}

// add css style to admin
function backwp_admin_style(){
    $plugin_data = get_plugin_data( BACKWP_DIR . 'index.php' );	
	  wp_enqueue_style( 'backwp-admin', BACKWP_URL . 'includes/style.css', false, $plugin_data['Version'], 'screen' );	
}

// add js script to admin
function backwp_admin_script(){
    wp_enqueue_script('postbox');
	  wp_enqueue_script('dashboard');
	  wp_enqueue_script('jquery');	  
}

//sucess message
function backwp_sucess_message(){
   echo '<div class="updated fade">
		<p>Backup successfully!</p>
  </div>';  	
}

//deleted message
function backwp_deleted_message(){
   echo '<div class="updated fade">
		<p>Backups Deleted!</p>
  </div>';  	
}

//display message when updated
function backwp_updated_message(){
   echo '<div class="updated fade">
		<p>Settings Updated</p>
  </div>';  	
}
?>