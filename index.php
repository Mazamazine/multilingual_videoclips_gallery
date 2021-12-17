<?php
/*
Plugin Name: Multilingual Video Clips gallery
Description: Adds ability to include a video gallery in a post and choose language
Author: Nina Ripoll
*/

$vcg_language_catId = false;

function vcg_plugin_init() {
  // Load translations
  load_plugin_textdomain( 'vcg', false, 'capsules_videos/languages' );
  // We need the 'vcg_languages' categorie for medias languages
  // Create it if not present
  $newTerm = wp_insert_term( 'vcg_languages', 'category' );
  if(is_array($newTerm)) $parentCat = $newTerm['term_id'];
  else $parentCat = $newTerm->error_data['term_exists'];
  global $vcg_language_catId;
  $vcg_language_catId = $parentCat;
}
add_action('init', 'vcg_plugin_init');

require_once plugin_dir_path(__FILE__) . 'includes/vcg-functions.php';
