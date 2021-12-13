<?php
/*
Plugin Name: Multilingual Video Clips gallery
Description: Adds ability to include a video gallery in a post and choose language
Author: Nina Ripoll
*/

function vcg_plugin_init() {
  load_plugin_textdomain( 'vcg', false, 'capsules_videos/languages' );
}
add_action('init', 'vcg_plugin_init');

require_once plugin_dir_path(__FILE__) . 'includes/vcg-functions.php';
