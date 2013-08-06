<?php

/*
  Plugin Name: rtMedia Widgets
  Plugin URI: http://rtcamp.com/buddypress-media/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
  Description: This plugin adds missing media rich features like photos, videos and audio uploading to BuddyPress which are essential if you are building social network, seriously!
  Version: 1.0
  Author: rtCamp
  Text Domain: rtmedia
  Author URI: http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media
 */

/**
 * Main file, contains the plugin metadata and activation processes
 *
 * @package BuddyPressMedia
 * @subpackage Main
 */
if ( ! defined ( 'RTMEDIA_WIDGETS_PATH' ) ) {

    /**
     *  The server file system path to the plugin directory
     *
     */
    define ( 'RTMEDIA_WIDGETS_PATH', plugin_dir_path ( __FILE__ ) );
}


if ( ! defined ( 'RTMEDIA_WIDGETS_URL' ) ) {

    /**
     * The url to the plugin directory
     *
     */
    define ( 'RTMEDIA_WIDGETS_URL', plugin_dir_url ( __FILE__ ) );
}

function rtmedia_widget_autoloader ( $class_name ) {
    $rtlibpath = array(
        'app/main/widgets/' . $class_name . '.php'
    );
    foreach ( $rtlibpath as $path ) {
        $path = RTMEDIA_WIDGETS_PATH . $path;
        if ( file_exists ( $path ) ) {
            include $path;
            break;
        }
    }
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register ( 'rtmedia_widget_autoloader' );

function rtmedia_register_widgets () {
    register_widget ( 'RTMediaGalleryWidget' );
}

function rtmedia_gallery_stylesheet () {
    wp_register_style ( 'rtmedia-gallery-widget-css', trailingslashit ( RTMEDIA_WIDGETS_URL ) . 'app/assets/css/rtmedia_gallery_widget.css' );
    wp_enqueue_style ( 'rtmedia-gallery-widget-css' );
}

function rtmedia_gallery_javascript () {
    wp_register_script ( 'rtmedia-gallery-widget-js', trailingslashit ( RTMEDIA_WIDGETS_URL ) . 'app/assets/js/rtmedia_gallery_widget_script.js' );
    wp_enqueue_script ( 'rtmedia-gallery-widget-js' );
}

add_filter ( 'rtmedia_class_construct', 'rtmedia_widgets_init' );

if ( ! function_exists ( "rtmedia_widgets_init" ) ) {

    function rtmedia_widgets_init ( $class_construct ) {
        require_once RTMEDIA_WIDGETS_PATH . 'app/main/RTMediaWidgets.php';
        $class_construct[ 'widgets' ] = false;
        return $class_construct;
    }

}
?>