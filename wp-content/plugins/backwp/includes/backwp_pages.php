<?php
/*
 * Admin Functions to render pages
 */
 
// Main page settings
function backwp_main_settings(){
   global $backwp;    
   add_meta_box( 'backwp-main-meta-box', __( 'Backup Selection', 'backwp' ), 'backwp_main_meta_box', $backwp->main, 'backup', 'high' );   
}

// Main page
function backwp_main_page(){
	 global $backwp;
   $plugin_data = get_plugin_data( BACKWP_DIR . 'index.php' );
?>

	<div class="wrap">
		
    <a href="http://backwp.com"><img src="<?php echo BACKWP_URL . 'includes/images/logo.png'; ?>" alt="BackWP"></a>           	
		<form id="backup" method="post"> 
		<div id="poststuff">			               
				<div class="metabox-holder">
					<div class="post-box-container column-backup-selection"><?php do_meta_boxes( $backwp->main, 'backup', $plugin_data ); ?></div>			
				</div>						
		</div><!-- #poststuff -->
		<br class="clear">
    <input class="button button-primary" type="submit" value="<?php _e('Backup Now'); ?>" name="backup_now" />
    </form>
    
    <?php if(isset( $_GET['running'] ) && 'true' == esc_attr( $_GET['running'] )){ 
          // trigger backwp ajax event
          wp_enqueue_script('backwp-action-js', BACKWP_URL . 'includes/js/backwp-action.js', false, 1.0, 'screen' );
          wp_localize_script('backwp-action-js', 'backwpParams', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));       
          $db = $_GET['db'];
          $files = $_GET['files'];
    ?>
      <script language="javascript">					
				jQuery(document).ready(function() {				
              jQuery().backwpTrigger({
						   // Configure display of popup
						   db: "<?PHP echo $db; ?>",
						   files: "<?PHP echo $files; ?>"
					    });        				
				});				
			</script>		
			<br class="clear">
			<div id="backwp_monitor">
			<div id="backwp_status">Status<span id="backwp_loading"><img width="16" height="16" style="vertical-align: -3px;" title="Loading..." alt="Loading..." src="<?php echo BACKWP_URL.'includes/images/loading.gif';?>"></span></div>
      <div id="backwp_archive">Archives Size:<span id="backwp_archive_size">0MB</span></div>
      </div>
      <br class="clear">
			<textarea id="backwp_messages" cols="75" rows="14" style="width: 700px;"><?php _e('Start Backup...', 'backwp');?></textarea>	
    <?php	} ?> 
      
	</div><!-- .wrap -->  	
<?php 
}

// Backup Archives page
function backwp_archives_page(){
	 global $backwp;
   $plugin_data = get_plugin_data( BACKWP_DIR . 'index.php' );
   
   //Create an instance of Backups List Table class...
   $backupsListTable = new Backups_List_Table();
   //Fetch, prepare, sort, and filter data...
   $backupsListTable->prepare_items();
?>

	<div class="wrap">		
    <a href="http://backwp.com"><img src="<?php echo BACKWP_URL . 'includes/images/logo.png'; ?>" alt="BackWP"></a>
    <?php if ( isset( $_GET['deleted'] ) && 'true' == esc_attr( $_GET['deleted'] ) ) backwp_deleted_message(); ?>
    <?php if ( isset( $_GET['_wpnonce'] ) ) backwp_deleted_message(); ?>	  
    <div id="poststuff">
    <form id="backups-list" method="get">
       <!-- For plugins, we also need to ensure that the form posts back to our current page -->
       <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
       <!-- Now we can render the completed list table -->
       <?php $backupsListTable->display() ?>
    </form>  
    </div><!-- #poststuff -->    
	</div><!-- .wrap -->  	
<?php 
}

// Backwp Premium Extensions page
function backwp_premium_extensions_page(){
	 global $backwp;
   $plugin_data = get_plugin_data( BACKWP_DIR . 'index.php' );
   
   $manager = BackWP_Extension_Manager::construct();

   $backwp = $manager->get_url();
   $key = $manager->get_key();
   $installUrl = $manager->get_install_url();
   $buyUrl = $manager->get_buy_url();  
	 
	 $extensions = $manager->get_extensions();

?>
	<div class="wrap">		
    <a href="http://backwp.com"><img src="<?php echo BACKWP_URL . 'includes/images/logo.png'; ?>" alt="BackWP"></a>
    <h3><?php _e('Premium Extensions', 'backwp'); ?></h3>
	  <div id="premium_extensions">
		 <p>
			 <?php _e('Please choose an premium extension below according to your requirement.', 'backwp'); ?>
			 <?php _e('Installing a premium extensions is easy:', 'backwp'); ?>
		 </p>
		 <ol class="instructions">
			 <li><?php _e('Click Buy Now and make the payment using PayPal', 'backwp'); ?></li>
			 <li><?php _e('You will receive a email with this extension file', 'backwp'); ?></li>
			 <li><?php _e('Upload this file on your /wp-content/plugins/backwp/extensions directory.', 'backwp'); ?></li>
			 <li><?php _e('Thats it, options for your extension will be available in the menu on the left', 'backwp'); ?></li>
		 </ol>
		 <a class="paypal" href="#" onclick="javascript:window.open('https://www.paypal.com/au/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350');">
			 <img  src="https://www.paypalobjects.com/en_AU/i/bnr/horizontal_solution_PP.gif" border="0" alt="Solution Graphics">
		 </a>

		 <a class="moneyback" href="http://backwp.com/money-back-guarantee">
			 <img src="<?php echo BACKWP_URL . 'includes/images/guarantee.gif'; ?>" alt="<?php _e('100% money back guarantee') ?>"/>
		 </a>
	  </div>
	  <table id="extensions">
		<tr>
			<th><?php _e('Name') ?></th>
			<th><?php _e('Description') ?></th>
			<th><?php _e('Price') ?></th>
			<th></th>
		</tr>

		<?php if (is_array($extensions)) foreach ($extensions as $extension): ?>
		<tr>
			<td><?php echo $extension['name'] ?></td>
			<td><?php echo $extension['description'] ?></td>
			<td>$<?php echo $extension['price'] ?> USD</td>
			<td>
				<a class="button" href="<?php echo $extension['buy_url']?>">Buy Now</a>
		  </td>
		</tr>
		<?php endforeach; ?>
	</table>
	<p id="premium_notes">
		<strong><?php _e('Please Note:') ?></strong>&nbsp;
		<?php echo sprintf(__('If you have more custom requirement, please contact us by %s.'), '<a href="http://backwp.com/contact/">' . __('custom services') . '</a>') ?>
	</p>
	     
	</div><!-- .wrap -->  	
<?php 
}
?>