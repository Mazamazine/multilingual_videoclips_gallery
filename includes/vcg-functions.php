<?php
/**
 * Enqueue stylesheets / JS
 */
add_action( 'wp_enqueue_scripts', 'vcg_enqueue_scripts' );
function vcg_enqueue_scripts() {
    wp_register_style( 'custom-gallery', plugins_url( '/css/gallery.css' , __FILE__ ) );
    wp_enqueue_style( 'custom-gallery' );
    wp_register_script( 'ajaxHandle', plugins_url( '/js/update_gallery.js' , __FILE__ ), array('jquery'), false, true );
    wp_enqueue_script( 'ajaxHandle' );
    wp_localize_script( 'ajaxHandle', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
// pour inclure dans admin
// add_action( 'enqueue_block_editor_assets', 'legit_block_editor_styles', 999 );

/*
 * create a meta box on posts editor pages
 */
function vcg_add_custom_box() {
    $screens = [ 'post', 'outils', 'nouvelles' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'vcg_box_id',                      // Unique ID
            __('Video clips gallery', 'vcg'),  // Box title
            'vcg_custom_box_html',             // Content callback, must be of type callable
            $screen                            // Post type
        );
    }
}
add_action( 'add_meta_boxes', 'vcg_add_custom_box' );

/*
 * Meta box content
 */
function vcg_custom_box_html( $post ) {

    // Retrieve post metadata
    $enabled = get_post_meta( $post->ID, '_vcg_enable', true );
    $attachedVids = array();
    $attachedVidsSerialized = get_post_meta( $post->ID, '_vcg_attachments', true );
    if(!empty($attachedVidsSerialized)) $attachedVids = unserialize($attachedVidsSerialized);
    ?>

    <script>
    // JS script to open medialibrary and add/remove media
    jQuery(document).ready(function() {
    var $ = jQuery;

    // Set existing attached medias from php
    var existingAttachments = <?php echo json_encode($attachedVids); ?>;
    $('#vidIds').val(existingAttachments);

    // Add attachment
    if ($('.set_custom_images').length > 0) {
        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $('.set_custom_images').on('click', function(e) {

                e.preventDefault();
                var button = $(this);

                wp.media.editor.send.attachment = function(props, attachment) {

                    var vidID = attachment.id;

                    // Check if media is already attached
                    if ($.inArray(vidID.toString(), existingAttachments) == -1) {
                        existingAttachments.push(vidID.toString());
                    }
                    else {
                        $('#messages').show();
                        $('#messages').html('This video is already included');
                        setTimeout(function() {
                            $("#messages").fadeOut(1500);
                        },3000);
                        return;
                    }

                    // Check if media is a video
                    var attachmentType = wp.media.attachment(attachment.id).get("type");
                    if(attachmentType != 'video') {
                        $('#messages').show();
                        $('#messages').html('You must choose a video');
                        setTimeout(function() {
                            $("#messages").fadeOut(1500);
                        },3000);
                        return;
                    }

                    // Show medias table if not yet visible
                    if($('#includedVids:visible').length == 0) {
                      $('#includedVids').show();
                      $('#noAttachTxt').hide();
                    }

                    // Add attachment infos to table
                    var attachmentURL = wp.media.attachment(attachment.id).get("url");
                    var attachmentTitle = wp.media.attachment(attachment.id).get("title");
                    $("#vidUrl").val(attachmentURL);
                    $('#includedVids tr:last').after('\
                        <tr>\
                          <td>'+vidID+'</td>\
                          <td>'+attachmentTitle+'</td>\
                          <td>'+attachmentURL+'</td>\
                          <td><button class=\"removeBtn\" data-value=\"'+vidID+'\">&#x274C</button>\
                        </tr>'
                    );

                    // Update form with attachment IDs
                    $('#vidIds').val(existingAttachments);
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    }

    // Remove attachment
    $(document).on('click','.removeBtn', function(e) {
        id = $(this).data("value");
        existingAttachments = jQuery.grep(existingAttachments, function(value) {
            return value != id;
        });
        $(this).parents('tr').remove();
        // Update form with attachment IDs
        $('#vidIds').val(existingAttachments);
        // Hide table if empty
        if(existingAttachments.length == 0) {
          $('#includedVids').hide();
          $('#noAttachTxt').show();
        }
    });

    });
    </script>

    <style>td {padding: 3px}</style>

    <!-- Content metabox-->
    <label for="vcg_enabled"><?php _e('Display video clips gallery', 'vcg'); ?>: </label>
    <input name="vcg_enabled" type="radio" value="0" <?php checked( $enabled, '0' ); ?>> <?php _e('no', 'vcg'); ?>
    <input name="vcg_enabled" type="radio" value="1" <?php checked( $enabled, '1' ); ?>> <?php _e('yes', 'vcg'); ?>
    <input type="hidden" id="vidIds" name="vcg_attachments" value="">
    <br/>
    <br/>
    <button class="set_custom_images button"><?php _e('Attach media', 'vcg'); ?></button>
    <span id="messages" style="display: none; color: red"></span>
    <?php
    if(empty($attachedVids)) {
        echo '<br/><br/><span id="noAttachTxt">'.__('No attachment yet', 'vcg').'</span><br/>';
        $tableDisplay = 'none';
        $tableContent = '';
    } else { 
        echo '<br/><br/>'.__('Included medias', 'vcg').':<br/>';
        $tableDisplay = 'block';
        $tableContent = '';
        foreach($attachedVids as $mediaId) {
            $url = wp_get_attachment_url($mediaId);
            $title = get_the_title($mediaId);
            $tableContent .= '<tr id="vcg_tr_'.$mediaId.'">';
            $tableContent .= "<td>$mediaId</td><td>$title</td><td>$url</td>";
            $tableContent .= "<td><button class='removeBtn' data-value='$mediaId'>&#x274C</button></td>";
            $tableContent .= '</tr>';
        }
    }
    ?>
    <table id="includedVids" style="display: <?php echo $tableDisplay; ?>; text-align: left;">
      <th>ID</th><th><?php _e('Title', 'vcg'); ?></th><th>Url</th><th>Action</th>
      <?php echo $tableContent; ?>
    </table>
    <?php
    echo '<br/><i>';
    _e('Included medias must have at least one language to be displayed (e.g.: categories>vcg_languages>français)', 'vcg');
    echo '</i><br/><i>'.__('Default category on page load: français', 'vcg').'</i>';
}

/*
 * Saving meta box data
 */
function vcg_save_postdata( $post_id ) {
    if ( array_key_exists( 'vcg_enabled', $_POST ) ) {
        update_post_meta(
            $post_id,
            '_vcg_enable',
            $_POST['vcg_enabled']
        );
    }
    if ( array_key_exists( 'vcg_attachments', $_POST ) ) {
        if(empty($_POST['vcg_attachments'])) $attachmentIds ='';
        else $attachmentIds = serialize(explode(',',$_POST['vcg_attachments']));
        update_post_meta(
            $post_id,
            '_vcg_attachments',
            $attachmentIds
        );
    }
}
add_action( 'save_post', 'vcg_save_postdata' );

/*
 * Get available languages
 */
function vcg_get_languages() {
    //$parentCatID = get_cat_ID('vcg_languages');
    global $vcg_language_catId;
    $taxonomies = array('category');
    $args = array('parent' => $vcg_language_catId);
    $terms = get_terms($taxonomies, $args);

    $languages = array();
    foreach($terms as $term) {
      $languages[] = ['id'=> $term->term_id, 'name'=>$term->name];
    }
    return $languages;
}

/*
 * Get post videos
 */
 function vcg_get_videos( $language = 'français', $postID='' ) {

    if(empty($postID)) $postID = get_the_ID();
    $attachedVidsSerialized = get_post_meta( $postID, '_vcg_attachments', true );
    if(empty($attachedVidsSerialized)) return false;

    $attachedVids = unserialize($attachedVidsSerialized);
    $displayedVids = array();
    foreach($attachedVids as $vidId) {
        $cats = wp_get_post_categories($vidId);
        foreach($cats as $catID) {
          $vidLanguage = get_the_category_by_ID($catID);
          if($vidLanguage == $language) $displayedVids[] = $vidId;
        }
    }
    return $displayedVids;
}

/*
 * Build gallery
 */
function vcg_build_gallery() {

    $attachedVids = vcg_get_videos();
    if(empty($attachedVids)) { return __('No media found', 'vcg'); }

    $gallery = '<div class="vcg_main">';
    $gallery .= '<h3 class="vcg_title">' . __('Audiovisual clips', 'vcg') . '</h3>';
    $gallery .= '<select name="vcg_language" id="vcg_language">';
    $gallery .= '<option value="0">' . __('Choose language', 'vcg') . '</option>';
    // Populate languages
    $languages = vcg_get_languages();
    foreach($languages as $language) {
        $gallery .='<option value="'.$language['id'].'">'.$language['name'].'</option>';
    }
    $gallery .= '</select>';
    $gallery .= '<input type="hidden" id="postID" value="'.get_the_ID().'" /><br/>';

    $gallery .= '<div class="vcg_video_main">';
    // Populate videos
    foreach($attachedVids as $vidId) {
        $caption = wp_get_attachment_caption($vidId);
        $vidUrl = wp_get_attachment_url($vidId);
        $title = get_the_title($vidId);
        $gallery .= '<div class="vcg_video_container"';
        $gallery .= '<figure class="wp-block-video"><video controls="" src="'.$vidUrl.'"></video></figure>';
        $gallery .= '<div class="vcg_video_title">' . $title . " ($caption)</div>";
        $gallery .= '</div>';
    }
    $gallery .= '</div>';

    $gallery .= '</div>';

    return $gallery;
}

/**
 * Update gallery using AJAX
 */
add_action( 'wp_ajax_myaction', 'vcg_update_gallery' );
add_action( 'wp_ajax_nopriv_myaction', 'vcg_update_gallery' );
function vcg_update_gallery(){
  // Use data posted through ajax (see js/update_gallery.js)
  // To send back a response we need to echo the result
  $language = get_the_category_by_ID($_POST['languageID']);
  $vidsIDs = vcg_get_videos( "$language", $_POST['postID'] );
  if(empty($vidsIDs)) {
     _e('No media found', 'vcg');
     wp_die();
  }
  $divContent = '';
  foreach($vidsIDs as $vidId) {
    $caption = wp_get_attachment_caption($vidId);
    $vidUrl = wp_get_attachment_url($vidId);
    $title = get_the_title($vidId);
    $divContent .= '<div class="vcg_video_container"';
    $divContent .= '<figure class="wp-block-video"><video controls="" src="'.$vidUrl.'"></video></figure>';
    $divContent .= '<div class="vcg_video_title">' . $title . " ($caption)</div>";
    $divContent .= '</div>';
  }
  echo $divContent;
  wp_die(); // ajax call must die to avoid trailing 0 in response
}

/*
 * Display gallery
 */
add_filter('the_content', 'show_gallery');
function show_gallery($content) {
    // Check if gallery is enabled
    $enabled = get_post_meta( get_the_ID(), '_vcg_enable', true );
    if(!$enabled) return $content;
    // Append gallery
    $gallery = vcg_build_gallery();
    $fullcontent = $content . $gallery;
    return $fullcontent;
}
