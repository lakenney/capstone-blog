<?php
/*************************************************************
 * 
 * BackWP Class
 *  
 **************************************************************/
class BackWP {

    var $wpdb;
    var $options;
    
    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->options = get_option(BACKWP_OPTIONS);        
    }
    
    function activate(){
        //plugin default opts
        $init_opts = array(
		    'version' => BACKWP_VERSION		
        );
	
	    if(!empty($this->options)){	  
	         // update existed pb options 
	         update_option(BACKWP_OPTIONS, $init_opts); 	
	     }else{
	         // add the init options
	         add_option(BACKWP_OPTIONS, $init_opts);   	
	     }	
	     
	     if (!(wp_next_scheduled('backwp_cron')))
	         wp_schedule_event(time(), 'backwp_intervals', 'backwp_cron'); 
	  }
    
	  function initialize(){
    }
 
    function deactivate(){ 
    	  wp_clear_scheduled_hook('backwp_cron');	    	 
	  }
}
?>