<?php
/*
Plugin Name: CP Import
Version: 1.0
Plugin URI: http://johnluetke.net/software/cp-import
Description: CP Import allows you to import your <b>articles, authors, categories, and media<b> from a College Publisher export file <br />[<a href="tools.php?page=cp-import/cp-import.php">Import</a> | <a href="http://johnluetke.net/software/cp-import">Help</a>]
Author: John Luetke
Author URI: http://johnluetke.net
*/

global $wpdb;

function cp_import_admin_menu() {
	require_once ABSPATH . '/wp-admin/admin-functions.php';
	add_management_page('CP Import', 'CP Import', 9, __FILE__, 'cp_import_init');
}


function cp_import_init() {
	$CPImporter = new CP_Import();
	
	$CPImporter->go();
}

add_action('admin_menu', 'cp_import_admin_menu');
?>
