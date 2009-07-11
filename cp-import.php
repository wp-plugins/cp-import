<?php
/*
 * Plugin Name: CP Import
 * Version: 1.0
 * Plugin URI: http://johnluetke.net/software/cp-import
 * Description: CP Import allows you to import your <b>articles, authors, categories, and media<b> from a College Publisher export file <br />[<a href="tools.php?page=cp-import/cp-import.php">Import</a> | <a href="http://johnluetke.net/software/cp-import">Help</a>]
 * Author: John Luetke
 * Author URI: http://johnluetke.net
 *
 * @package net.johnluetke.software.wordpress.cpimport
 *
 * @copyright 2009 John Luetke < john@johnluetke.net >
 *
 * $URL$
 * $Revision$
 * $Date$
 * $Author$
 *
 *
 * @filesource
 */
global $wpdb;

require("cp-import.class.php");

/**
 * 
 */
function cp_import_admin_menu() {
	require_once (ABSPATH . '/wp-admin/admin-functions.php');
	add_management_page('CP Import', 'CP Import', 9, __FILE__, 'cp_import_init');
}

/**
 *
 */
function cp_import_init() {
	$CPImporter = new CP_Import();
	
	@$CPImporter->go();
}

// Adds the menu item for CP Import
add_action('admin_menu', 'cp_import_admin_menu');

// Add options. If they already exist, nothing will happen.
add_option("cp_import_from", "4", "", "no");
add_option("cp_import_user", "accounts", "", "no");
add_option("cp_import_paper_id", "", "", "no");
add_option("cp_import_default_user", "1", "", "no");
add_option("cp_import_username_before", "", "", "no");
add_option("cp_import_username_after", "", "", "no");
add_option("cp_import_media_file", "", "", "no");
?>
