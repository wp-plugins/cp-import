<?php
/**
 * CP Import
 *
 * @package net.johnluetke.software.wordpress.cp-import
 *
 * @copyright 2009 John Luetke < john@johnluetke.net >
 *
 * $URL$
 * $Revision$
 * $Date$
 * $Author$
 *
 * @filesource
 */

define(CP_IMPORT_DB_OPTIONS, 'cp-import-options');

/**
 * Creates a new CP Import Settings object. This class contains all the functions for
 * manipulating the configureable option for CP Import
 *
 * @author John Luetke
 */
class CP_Import_Settings {

	private var $default_options = array(
		new CP_Import_Option('from_version', 4, 'Which version of College Publisher are you importing from?', true, 'radio', array (4, 5)),
		new CP_Import_Option('users', 'accounts', 'CP Import can either create a Wordpress User account for each author that it finds, or add that information to each post as a custom field.<br /><br />More options are below if you choose "Wordpress Account". If you choose "Custom Field", author data will be imported as-is from you Archive file.', true, 'radio', array('accounts' => 'Accounts', 'custom_field' => 'Custom Field', 'none' => 'None')),
		new CP_Import_Option('default_user', 1, 'If any kind of error occurs while attempting to import author data, or you chose to import article authors as Custom fields, which account should have credit for having written the imported articles?', true, 'select', get_users_of_blog()),
		new CP_Import_Option('verbose', 0, 'Would you like additional output displayed when importing articles?', false, 'radio', array('1' => 'Yes', '0' => 'No')),
		new CP_Import_Option('date_fmt', 'Y-m-d H:i:s', 'How should dates be formatted in the database? (PHP date() syntax)'),
		new CP_Import_Option('cp4url', '"/media/storage/paper%s/news/%year%/%monthnum%/%day%/%category%/%postname%-%post_id%.shtml'),
		new CP_Import_Option('cp5url', '/%category%/%postname%-1.%post_id%"'),
		new CP_Import_Option('media_dir', WP_CONTENT_DIR."/cp-import/");
		new CP_Import_Option('media_dir_hr', basename(dirname(WP_CONTENT_DIR."/cp-import/"))."/" . basename(WP_CONTENT_DIR."/cp-import/")."/"),
		new CP_Import_Option('temp_dir', plugin_dir_path(__FILE__)."tmp/"),
		new CP_Import_Option('media_file', ""),
		new CP_Import_Option('archive_file', "")
	);

	private var $version = "2.0";

	public function CP_Import_Settings () {
		$this->options = $this->get();
	}

	public function upgrade() {
		// New options with this version
		add_option(CP_IMPORT_DB_OPTIONS, $this->default_options);
		add_option(CP_IMPORT_DB_VERSION, $this->version);

		// Upgrade from <= 1.5 to 2.0
		if (get_option('cp_import_from')) {
			// Simply remove unused options
			delete_option("cp_import_from");
			delete_option("cp_import_user");
			delete_option("cp_import_default_user");
			delete_option("cp_import_username_before");
			delete_option("cp_import_username_after");
			delete_option("cp_import_paper_id");
			delete_option("cp_import_verbose");
		}
	}

	public function get() {
		return get_option(CP_IMPORT_DB_OPTIONS);
	}

	public function get($option) {
		foreach ($this->options as $opt) {
			if ($opt->getName() == $option)
				return $opt;
		}

		return null;
	}

	public function set($option, $value) {
		foreach ($this->options as $opt) {
			if ($opt->getName() == $option)
				$opt->setValue($value);
		}

		$this->save();
	}

	public function save() {
		update_option(CP_IMPORT_DB_OPTIONS, $this->options);
	}

	public function ui_options() {
		CP_Import::logo();

	}

}

class CP_Import_Option {

	private var $name;
	private var $value;
	private var $description;
	private var $editable;
	private var $html_type;
	private var $possible_values;

	public function CP_Import_Option($n, $v, $d = "", $e = false, $html_type = "text", $possible_values = "") {
		$this->name = $n;
		$this->value = $v;
		$this->description = $d;
		$this->editable = $e;
	}

	public function setName($n) { $this->name = $n; }
	public function setValue($v) { $this->value = $v; }
	public function setDescription($d) { $this->description = $d; }
	public function setEditable($e) { $this->editable = $e; }
	public function setHTMLType($h) { $this->html_type = $h; }
	public function setPossibleValues($p) { $this->possible_values = $v; }

	public function isEditable() { return $this->editable; }
	public function getName() {return $this->name; }
	public function getValue() { return $this->value; }
	public function getDescription() { return $this->description; }
	public function getHTMLType() { return $this->html_type; }
	public function getPossibleValues() { return $this->possible_values; }

}
