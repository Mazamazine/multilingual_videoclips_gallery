<?php
/*
Plugin Name: Multilingual Video Clips Gallery
Description: Adds ability to include a video gallery in a post and choose a language to filter videos. This plugins needs Media Library Categories to work properly. Tested under Wordpress 5.8.2 with Media Library Categories 1.9.9.
Author: Nina Ripoll
Author URI: https://maze-photo.com
Version: 1.0
License: GPLv2
Text Domain: vcg
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
  // We need the 'français' cat which is the default
  wp_insert_term(
    'français',
    'category',
    array(
        'slug'        => 'fr',
        'parent'      => $vcg_language_catId,
    )
  );
}
add_action('init', 'vcg_plugin_init');

require_once plugin_dir_path(__FILE__) . 'includes/vcg-functions.php';
