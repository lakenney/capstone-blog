/* 
 * Backwp Trigger Function
*/

(function($){

	$.fn.backwpTrigger = function(options){   
	     $.ajax({
           type : "post",
           url : backwpParams.ajaxurl,
           data : {action: "backwp_action", db: options.db, files: options.files}           
       });      			
       
       setInterval( function() {
         $.ajax({
           type : "post",
           url : backwpParams.ajaxurl,
           data : {action: "backwp_monitor"},
           success: function(response) {
             //console.log(response);           
             var obj = jQuery.parseJSON(response);
             console.log(obj.size);
             console.log(obj.status);
             //jQuery( '#backwp_messages' ).append( "\n" + response );            
             jQuery( '#backwp_messages' ).val(obj.logs);
						 textareaelem = document.getElementById( 'backwp_messages' );
						 textareaelem.scrollTop = textareaelem.scrollHeight;
						 if(obj.status == true){						 	
						 	jQuery('#backwp_loading').css("display", "none");						                     
             }            
              jQuery('#backwp_archive_size').text(obj.size);
            	
           }          
         })         
       }, 5000);
 	
	}; // End backwp trigger Function

  $.fn.backwpMonitor = function(options){ 
      console.log(options.messages);    
      jQuery( '#backwp_messages' ).append( "\n" + options.messages );
		  textareaelem = document.getElementById( 'backwp_messages' );
			textareaelem.scrollTop = textareaelem.scrollHeight;         			
 	
	}; // End backwp monitor Function
  
})(jQuery); // End Plugin