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
define (CP_IMPORT_DIR, dirname(__FILE__));

require("classes/cp-import.class.php");
require("classes/cp-import-prepare.class.php");
require("classes/cp-import-settings.class.php");

$CP_Import = new CP_Import();
$CP_Import_Settings = new CP_Import_Settings();


/**
 * 
 */
function cp_import_admin_menu() {
	require_once (ABSPATH . '/wp-admin/admin-functions.php');
	add_menu_page('CP Import', 'CP Import', 'manage_options', 'cp-import', 'cp_import_init');
	add_submenu_page('cp-import', 'CP Import &raquo; Import', 'Import', 'manage_options', 'cp-import/import', 'cp_import_import');
	add_submenu_page('cp-import', 'CP Import &raquo; Settings', 'Settings', 'manage_options', 'cp-import/settings', 'cp_import_settings');
	add_submenu_page('cp-import', 'CP Import &raquo; Uninstall', 'Uninstall', 'manage_options', 'cp-import/uninstall', 'cp_import_uninstall');
	
}

function cp_import_uninstall() {
	global $CP_Import_Settings;
	$CP_Import_Settings->ui_uninstall();

}

function cp_import_settings() {
        global $CP_Import_Settings;
	$CP_Import_Settings->ui_screen();
}

function cp_import_import() {
	global $CP_Import, $CP_Import_Settings;;
	$CP_Import->load_settings(&$CP_Import_Settings);
	$CP_Import->ui_import();
}

/**
 *
 */
function cp_import_init() {
	global $CP_Import, $CP_Import_Settings;
	$CP_Import_Settings->purge();	
	$CP_Import_Settings->upgrade();

	$CP_Import->load_settings(&$CP_Import_Settings);
	$CP_Import->ui_welcome_screen();
}

// Adds the menu item for CP Import
add_action('admin_menu', 'cp_import_admin_menu');
?>
