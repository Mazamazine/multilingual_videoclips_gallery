<?php
$attachedVidsSerialized = get_post_meta( get_the_ID(), '_vcg_attachments', true );
if(empty($attachedVidsSerialized)) {
    _e('No media found', 'vcg');
    return;
}
$attachedVids = unserialize($attachedVidsSerialized);
?>

<div class="vcg_main">
    <h3 class="vcg_title"><?php _e('Audiovisual clips', 'vcg');?></h3>
    <select name="vcg_language">
        <option><?php _e('Choose language', 'vcg');?></option>
        <option value="fr">Français</option>
        <option value="en">English</option>
    </select>
    <br/>
    <?php
    foreach($attachedVids as $vidId) {
        $vidUrl = wp_get_attachment_url($vidId);
        $title = get_the_title($vidId);
        echo '<div class="vcg_video_container"';
        echo '<figure class="wp-block-video"><video controls="" src="'.$vidUrl.'"></video></figure>';
        echo '<div class="vcg_video_title">'.$title.'</div>';
        echo '</div>';
    }
    ?>
</div>
