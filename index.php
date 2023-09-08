<?php
/*
Plugin Name: Multilingual Video Clips Gallery
Description: Adds ability to include a video gallery in a post and choose a language to filter videos. This plugins needs Media Library Categories to work properly. Tested under Wordpress 6.2.2 with Media Library Categories 2.0.1.
Author: Nina Ripoll
Author URI: https://maze-photo.com
Version: 1.1
License: GPLv2
Text Domain: vcg
*/

$vcg_language_catId = false;

function vcg_plugin_init() {
  // Check if Media Library Categories plugin is active
  include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  if ( !is_plugin_active( 'wp-media-library-categories/index.php' ) ) {
    add_action( 'admin_notices', 'vcg_admin_notice_error' ) ;
    $plugin = dirname(__FILE__) . '/index.php';
    deactivate_plugins($plugin);
    return;
  }
  // Load translations
  load_plugin_textdomain( 'vcg', false, 'capsules_videos/languages' );
  // We need the 'vcg_languages' categorie for medias languages
  // Create it if not present
  $newTerm = wp_insert_term( 'vcg_languages', 'category' );
  if(is_array($newTerm)) $parentCat = $newTerm['term_id'];
  else $parentCat = $newTerm->error_data['term_exists'];
  global $vcg_language_catId;
  $vcg_language_catId = $parentCat;
  // Create french subcategory if not present
  // It's the default category
  include_once( ABSPATH . 'wp-admin/includes/taxonomy.php' );
  wp_create_category('français', $vcg_language_catId);
}
add_action('init', 'vcg_plugin_init');

function vcg_admin_notice_error() {
    $class = 'notice notice-error';
    $message = __( 'This plugin needs Media Library Categories to work properly. Please install and activate it first! The plugin has been deactivated.', 'vcg' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

require_once plugin_dir_path(__FILE__) . 'includes/vcg-functions.php';
