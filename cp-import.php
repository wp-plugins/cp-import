<?php
/*
 * Plugin Name: CP Import
 * Version: 1.5
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
require("cp-import.class.php");
require("cp-import-prepare.class.php");

/**
 * 
 */
function cp_import_admin_menu() {
	require_once (ABSPATH . '/wp-admin/admin-functions.php');
	add_menu_page('CP Import', 'CP Import', 'manage_options', 'cp-import', 'cp_import_init');
	add_submenu_page(__FILE__, 'CP Import &raquo; Import', 'CP Import &raquo; Import', 'Import', 'manage_options', 'cp_import_init');
	add_management_page('CP Import', 'CP Import', 9, __FILE__, 'cp_import_init');
}

/**
 *
 */
function cp_import_init() {
	$options =  array (
		'paper_id' => '', 
		'from_version' => 4,
		'users' => 'accounts',
		'default_user' => 1,
		'verbose' => false,
		'date_fmt' =>'Y-m-d H:i:s',
		'cp4url' => '"/media/storage/paper%s/news/%year%/%monthnum%/%day%/%category%/%postname%-%post_id%.shtml',
		'cp5url' => '/%category%/%postname%-1.%post_id%"',
		'media_dir' => WP_CONTENT_DIR."/cp-import/",
		'media_dir_hr' => basename(dirname(WP_CONTENT_DIR."/cp-import/"))."/" . basename(WP_CONTENT_DIR."/cp-import/")."/",
		'temp_dir' => plugin_dir_path(__FILE__)."tmp/",
		'media_file' => "",
		'archive_file' => "",
	);

	add_option("cp-import-options", $options);


	$CPImporter = new CP_Import();
	
	$CPImporter->go();
}

// Adds the menu item for CP Import
add_action('admin_menu', 'cp_import_admin_menu');
?>
