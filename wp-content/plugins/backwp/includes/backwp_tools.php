<?php
/*
 *Backup action function
 */
function backwp_backup_now($database, $files){ 
  
  $options = get_option(BACKWP_OPTIONS);
  $backup_dir = WP_CONTENT_DIR . '/uploads/backwp_backups/';
  $backup_db_dir = WP_CONTENT_DIR . '/uploads/backwp_backups_db/';
  $backup_temp_dir = WP_CONTENT_DIR . '/uploads/backwp_backups_temp/';
  $backup_log_dir = WP_CONTENT_DIR . '/uploads/backwp_backups_log/';
  
  // make backups dir if not exists 
  if (!file_exists($backup_dir)) {
	  mkdir($backup_dir);
  } 
  // make backups dir if not exists 
  if (!file_exists($backup_db_dir)) {
	  mkdir($backup_db_dir);
  } 
  // make backups temp dir if not exists 
  if (!file_exists($backup_temp_dir)) {
	  mkdir($backup_temp_dir);
  }  
  // make backups log dir if not exists 
  if (!file_exists($backup_log_dir)) {
	  mkdir($backup_log_dir);
  }
  
  // log filename
  $log_filename = $backup_log_dir . 'log.txt';
  // clear last log
  if(file_exists($log_filename)){file_put_contents($log_filename, '');}
  
  // clear backup temp directory
  $dh = opendir($backup_temp_dir);
  while (false !== ($obj = readdir($dh))) {
   if($obj=='.' || $obj=='..'){ 
   	 continue;
   }else{
   	 @unlink($backup_temp_dir.$obj);
   }
  }
  closedir($dh);
  
  // create backup zip filename base on selection
  if($database == 'enable' && $files == 'enable'){
  	 $prefix = 'backwp-full-';
  }else if($database == 'disable' && $files == 'enable'){
     $prefix = 'backwp-files-';	
  }else if($database == 'enable' && $files == 'disable'){
  	 $prefix = 'backwp-database-';
  }else{
     exit();	
  }  
  $current_time = time();
  
  // backup filename
  $filename = $prefix . $current_time. '.zip';
  $zipfile = $backup_temp_dir . $filename;    
	//$zip = new PclZip($zipfile);
	$zip = new ZipArchive();
	$zip->open($zipfile, ZIPARCHIVE::CREATE);
	
	$backup_files_array = array();
	   
  if($database == 'enable'){  	
  	$backup_database_file_path = backwp_backup_database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, $backup_db_dir);
    backwp_backup_log('action', 'Create database sql file on '.$backup_database_file_path, $log_filename);
    $backup_files_array[] = $backup_database_file_path;
    backwp_backup_log('action', 'Save database sql file to backup queue.', $log_filename);
	} 
  
  if($files == 'enable'){
    $backup_files_array = backwp_backup_files(rtrim(ABSPATH,"/"));
    backwp_backup_log('action', 'Save wordpress files to backup queue.', $log_filename);  
  }	
  
  backwp_backup_log('action', 'Start add files to backup zip archive.', $log_filename);
  foreach ($backup_files_array as $item) {	 
  	   $local_name = str_replace(ABSPATH, "", $item);    
	     $zip->addFile($item,$local_name);
	     //$zip->add($item);
	     backwp_backup_log('action', 'Add file '.$local_name, $log_filename);
	}
	
	backwp_backup_log('action', 'Creating Backup Archive File......', $log_filename);
	
	// send backup file to remote ftp server
	if($options['ftp_enabled'] == 'yes'){
	   backwp_send_via_ftp(rtrim($options['remote_directory'], "/")."/".$filename, WP_CONTENT_URL.'/uploads/backwp_backups/'.$filename); 	      
     backwp_backup_log('action', 'Send backup zip archive file to remote ftp server', $log_filename);		
  }
  
  // send email notification
	if($options['send_email_when'] == 'success' && !empty($options['email_address'])){
	   $subject = get_bloginfo('name')." Backwp Notification";
	   $message = "Your site's backup have been saved on <a href='".WP_CONTENT_URL.'/uploads/backwp_backups/'.$filename."'>".$filename."</a>";
	   wp_mail($options['email_address'], $subject, $message);
	   backwp_backup_log('action', 'Send backup successfully email to '.$options['email_address'], $log_filename);		
	}
		  
	// close zip file
  $zip->close(); 
}

/*
 * Backup Database Function
 */
function backwp_backup_database($host,$user,$pass,$name,$dbbackup) {
    // establish databse connection
    $link = mysql_connect($host,$user,$pass);
    mysql_select_db($name,$link);
    
    // select all tables
    $tables = array();
    $result = mysql_query('SHOW TABLES');
    // get excluded tables option
    $options = get_option(BACKWP_OPTIONS);
    $original_excluded_tables = explode(",", $options['excluded_tables']);
    foreach($original_excluded_tables as $excluded){
      	$excluded_tables[] = trim($excluded);
    }
    // exclude selected tables from all tables
    while($row = mysql_fetch_row($result)) {
        if(in_array($row[0], $excluded_tables)){
           continue;	
        }
        $tables[] = $row[0];
    }    
    
    foreach($tables as $table){
        $result = mysql_query('SELECT * FROM '.$table);
        $num_fields = mysql_num_fields($result);
        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        
        for ($i = 0; $i < $num_fields; $i++){
            while($row = mysql_fetch_row($result)){
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = ereg_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }
    $dbbackup .= "db.sql";
    if (fwrite(fopen($dbbackup,'w+'),$return)) {
    	return $dbbackup;
    	fclose($handle);
    }
    else {
    	return false;
    }
    
}

/*
 * Backup Files Function
 */
function backwp_backup_files($dir) {
	  // get excluded dirs option
    $options = get_option(BACKWP_OPTIONS);
    $original_excluded_dirs = explode(",", $options['excluded_dirs']);
    foreach($original_excluded_dirs as $excluded){
      	$excluded_dirs[] = rtrim(ABSPATH, '/').trim($excluded);
    }
	 
	  global $filenames;
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
        	  $current_file_path = $dir.'/'.$file;    
        	    	  
        	  //exclude dirs from backup files
        	  if(in_array($current_file_path, $excluded_dirs)){
        	    continue;	
        	  }   
        	              	          	  
            if ($file != "." && $file != ".." && $file != "backwp_backups" && is_file($current_file_path)) {
            	$filenames[] = $current_file_path;
            }
            else if ($file != "." && $file != ".." && $file != "backwp_backups" && is_dir($current_file_path)) {
                backwp_backup_files($current_file_path);
            }
        }
        closedir($handle);
    }
    return $filenames;
}

/*
 * Backup log funciton
 */
function backwp_backup_log($type, $message = '', $log_filename){
   $handle = fopen($log_filename, 'a'); 
   $message = date("H:i:s").": ".$message."\n";
   fwrite($handle, $message); 
   fclose($handle);  
} 

/**
 * Adds a set of custom intervals to the cron schedule list
 * @param  $schedules
 * @return array
 */
function backwp_cron_schedules($schedules) {
	$new_schedules = array(
	  'every_ten_seconds' => array(
			'interval' => 10,
			'display' => 'every_ten_seconds'
		),
		'every_min' => array(
			'interval' => 60,
			'display' => 'every_min'
		),
		'daily' => array(
			'interval' => 86400,
			'display' => 'Daily'
		),
		'weekly' => array(
			'interval' => 604800,
			'display' => 'Weekly'
		),
		'fortnightly' => array(
			'interval' => 1209600,
			'display' => 'Fortnightly'
		),
		'monthly' => array(
			'interval' => 2419200,
			'display' => 'Once Every 4 weeks'
		),
		'two_monthly' => array(
			'interval' => 4838400,
			'display' => 'Once Every 8 weeks'
		),
		'three_monthly' => array(
			'interval' => 7257600,
			'display' => 'Once Every 12 weeks'
		),
	);
	return array_merge($schedules, $new_schedules);
}

  /*
   * Format Bytes
   */
function formatBytes($bytes, $precision = 2) { 
      $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

      $bytes = max($bytes, 0); 
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
      $pow = min($pow, count($units) - 1); 

      // Uncomment one of the following alternatives
      // $bytes /= pow(1024, $pow);
      $bytes /= (1 << (10 * $pow)); 

      return round($bytes, $precision) . ' ' . $units[$pow]; 
}

/*
 * Get the temp filename from backup temp directory
 */
function get_temp_file_size($backup_temp_dir){
     $dh = opendir($backup_temp_dir);
     while (false !== ($obj = readdir($dh))) {
        if($obj=='.' || $obj=='..'){ 
   	     continue;
        }else{
   	     $temp_filename = $backup_temp_dir.$obj;;   	     
        }
     }
     closedir($dh);	
     $size = formatBytes(filesize($temp_filename));
     return $size;
}

/*
 * Rename backup filename from temp directory to archives directory 
 */
function rename_backup_file($backup_temp_dir, $backup_dir){
    $dh = opendir($backup_temp_dir);
     while (false !== ($obj = readdir($dh))) {
        if($obj=='.' || $obj=='..'){ 
   	     continue;
        }else{
   	     $temp_filename = $backup_temp_dir.$obj;
   	     $new_filename = $backup_dir.$obj;
        }
     }
     closedir($dh);
     rename($temp_filename, $new_filename); 	
}
?>