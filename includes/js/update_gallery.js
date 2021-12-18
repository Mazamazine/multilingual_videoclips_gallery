jQuery(document).ready(function(){
    jQuery("#vcg_language").change( function() {
        selectedLanguageID = jQuery( "#vcg_language" ).val();
        if(selectedLanguageID == 0) return;
        postID = jQuery("#postID").val();
        jQuery.ajax({
          type:'POST',
          data:{
            action: 'myaction', // function triggered, declared in vcg-functions.php
            languageID: selectedLanguageID,
            postID: postID
          },
          url: ajax_object.ajaxurl, // this is the object instantiated in wp_localize_script function
          success: function(data) {
            //Do something with the result from server
            videoDivContent = data;
          },
          complete: function(data){
            jQuery(".vcg_video_main").html(videoDivContent);
          }
        });
    });
});
