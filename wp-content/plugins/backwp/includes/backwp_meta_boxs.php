<?php
/*
 * Meta Box Functions
 */
// backwp main meta box
function backwp_main_meta_box(){
	$default_modes = array('enable'=>'Enable', 'disable'=>'Disable');
?>
      <table class="form-table">
		  <tr>
			<th>
            	<label for="database"><?php _e( 'Database Backup:', 'backwp' ); ?></label> 
            </th>
            <td>
            	<select name="database" >
                  <?php foreach($default_modes as $key => $value) { ?>
                   <option value="<?php echo $key; ?>" > <?php echo $value; ?></option> 
                 <?php } ?>
                </select>
            </td>
		  </tr>	
		  <tr>
		    <th>
            	<label for="files"><?php _e( 'Files Backup:', 'backwp' ); ?></label> 
          </th>
          <td>
		       <select name="files" >
                  <?php foreach($default_modes as $key => $value) { ?>
                   <option value="<?php echo $key; ?>" > <?php echo $value; ?></option> 
                 <?php } ?>
           </select>
		   </td>
		</tr>
	  </table><!-- .form-table --> 
<?php      
}

// backwp exclusions meta box
function backwp_exclusions_meta_box(){
	$options = get_option(BACKWP_OPTIONS);
?>
	  <table class="form-table">
		  <tr>
			<th class='excluded_tables'>
        	<label for="excluded_tables"><?php _e( 'Excluded Tables:', 'backwp' ); ?></label> 
      </th>
      <td>
          <textarea rows="3" cols="65" name="excluded_tables"><?php if(!empty($options['excluded_tables'])){echo $options['excluded_tables'];} ?></textarea>     	
          <div class="description">Enter exclued tables name when backup. Separate each table with comma, For example: wp_posts, wp_options</div>
      </td>
		  </tr>	
		  <tr>
		    <th class='excluded_dirs'>
            	<label for="excluded_dirs"><?php _e( 'Excluded Directories:', 'backwp' ); ?></label> 
          </th>
          <td>
		       <textarea rows="3" cols="65" name="excluded_dirs"><?php if(!empty($options['excluded_dirs'])){echo $options['excluded_dirs'];} ?></textarea>     	
		       <div class="description">Enter exclude directories when backup. Separate each directory with comma, for example: /wp-content/uploads, /wp-content/plugins</div>
		   </td>
		</tr>
	  </table><!-- .form-table -->
<?php
}

// backwp notice meta box
function backwp_notice_meta_box(){
	$options = get_option(BACKWP_OPTIONS);
	$send_email_when = array('success'=>'Backup Success', 'failure'=>'Backup Failure');
?>
  <table class="form-table">
		<tr>
			<th>
        <label for="send_email_when"><?php _e( 'Send Notification When:', 'backwp' ); ?></label> 
      </th>
      <td>
      	<select name="send_email_when" class="wide">
           <?php foreach($send_email_when as $key => $value) { ?>
              <option value="<?php echo $key; ?>" <?php if(isset($options['send_email_when'])){selected($key, $options['send_email_when']);} ?> > <?php echo $value; ?></option> 
           <?php } ?>
        </select>
      </td>
		</tr>
		<tr>
			<th>
        <label for="email_address"><?php _e( 'Email Address:', 'backwp' ); ?></label> 
      </th>
      <td>
      	<input id="email_address" name="email_address" type="text" value="<?php if(isset($options['email_address'])){echo $options['email_address'];} ?>" />
      </td>
		</tr>		
	</table><!-- .form-table -->  
<?php   
}
?>