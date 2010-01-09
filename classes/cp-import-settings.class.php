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
define(CP_IMPORT_DB_VERSION, 'cp-import-version');

/**
 * Creates a new CP Import Settings object. This class contains all the functions for
 * manipulating the configureable option for CP Import
 *
 * @author John Luetke
 */
class CP_Import_Settings {

	// THIS version of CP Import
	var $version = "2.0";

	public function CP_Import_Settings () {
		$this->options = $this->get();
	}

	public function upgrade() {
		
		$options[] = new CP_Import_Option('from_version', 4, 'Which version of College Publisher are you importing from?', true, 'radio', array ('4' => 'CP 4')); //, '5' => 'CP 5'));
		$options[] = new CP_Import_Option('users', 'accounts', 'CP Import can either create a Wordpress User account for each author that it finds, or add that information to each post as a custom field.<br /><br />More options are below if you choose "Wordpress Account". If you choose "Custom Field", author data will be imported as-is from you Archive file.', true, 'radio', array('accounts' => 'Wodpress Account', 'custom_field' => 'Custom Field', 'none' => 'None'));
		$options[] = new CP_Import_Option('default_user', 1, 'If any kind of error occurs while attempting to import author data, or you chose to import article authors as Custom Fields, which account should have credit for having written the imported articles?', true, 'select', get_users_of_blog());
		$options[] = new CP_Import_Option('uname_prefix', '', 'If you choose to create Wordpress Accounts for each author, CP Import automatically names the account <em>firstname.lastname</em>. However, you can prefix this with whatever you want', true);
		$options[] = new CP_Import_Option('uname_suffix', '', 'You can also add a suffix to the generated username.', true);
		$options[] = new CP_Import_Option('verbose', 0, 'Would you like additional output displayed when importing articles?', false, 'radio', array('1' => 'Yes', '0' => 'No'));
		$options[] = new CP_Import_Option('date_fmt', 'Y-m-d H:i:s', 'How should dates be formatted in the database? (PHP date() syntax)');
		$options[] = new CP_Import_Option('cp4url', '"/media/storage/paper%s/news/%year%/%monthnum%/%day%/%category%/%postname%-%post_id%.shtml');
		$options[] = new CP_Import_Option('cp5url', '/%category%/%postname%-1.%post_id%"');
		$options[] = new CP_Import_Option('cp_permalinks', '0', 'CP Import can also set your Wordpress permalinks to mimic that of College Publisher. If you turn this on, please remeber to enter your College Publisher Paper ID below.', true, 'radio', array('1' => 'Yes', '0' => 'No'));
		$options[] = new CP_Import_Option('paper_id', '', 'Your College Publisher Paper ID (Required if you choose to mimic CP Permalinks above)', true);
		$options[] = new CP_Import_Option('redirect', '1', 'If you do not want to set your permalinks to mimic that of College Publisher, CP Import can attempt to redirect your old College Publisher links to your new Wordpress ones. (Special Thanks to <a href="http://danielbachhuber.com/">Daniel Bachhuber</a> of <a href="http://copress.org/">CoPress</a> for this)', true, 'radio', array( '1' => 'Enable', '0' => 'Disable'));
		$options[] = new CP_Import_Option('media_dir', WP_CONTENT_DIR."/cp-import/");
		$options[] = new CP_Import_Option('media_dir_hr', basename(dirname(WP_CONTENT_DIR."/cp-import/"))."/" . basename(WP_CONTENT_DIR."/cp-import/")."/");
		$options[] = new CP_Import_Option('temp_dir', plugin_dir_path(__FILE__)."tmp/");
		$options[] = new CP_Import_Option('media_file', "");
		$options[] = new CP_Import_Option('archive_file', "");

		// New options with this version
		add_option(CP_IMPORT_DB_OPTIONS, $options);
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

	public function purge() {
		delete_option(CP_IMPORT_DB_OPTIONS);
		delete_option(CP_IMPORT_DB_VERSION);
	}

	public function get($option = "") {
		if (empty($option)) {
			return get_option(CP_IMPORT_DB_OPTIONS);
		}
		else {
			foreach ($this->options as $opt) {
				if ($opt->getName() == $option)
					return $opt;
			}
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

	public function ui_error($msg) {

	}
	
	public function ui_process_post() {
		print_r($_POST);
		
	}

	public function ui_screen() {
		//echo "<pre>".print_r($this, true);
		echo CP_Import::ui_logo();

		if ($_POST)
			$this->ui_process_post();

		?>
		<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>?page=cp_import/settings'>
		<table>
			<?php
			foreach ($this->options as $option) { 
				if ($option->isEditable()) {
			?>
			<tr>
				<td valign='top' width='40%'>
					<?php echo $option->getDescription(); ?>
				</td>
				<td valign='top' width='40%'>
			<?php
				switch ($option->getHTMLType()) {
					// As of now, this assumes that $option->getPossibleValues will return
					// an array of Wordpress User objects
					case 'select':
						?>
						<select name='<?php echo $option->getName();?>'>
						<?php
						foreach ($option->getPossibleValues() as $value) {
							$extra = ($value->user_id == $option->getValue()) ? "selected='selected' " : "";
						?>
							<option value='<?php echo $value->user_id;?>' <?php echo $extra;?>><?php echo $value->display_name;?></option>
						<?php
						}
						?>
						</select>
						<?php
						break;
					case 'radio':
						foreach ($option->getPossibleValues() as $value=>$label) {
							$extra = ($value == $option->getValue()) ? "checked='checked' " : "";
						?>
						<input type='radio' name='<?php echo $option->getName();?>' value='<?php echo $value;?>' <?php echo $extra;?>/> <?php echo $label;?><br />
						<?php
						}				
						break;
					case 'text':
					default:
						?>
						<input type='text' name='<?php echo $option->getName();?>' value='<?php echo $option->getValue();?>' />
						<?php
				}
			?>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<?php
				} 
			}?>
		</table>
		<input type='submit' value='Save Settings'>
		</form>
		<?php
	}

}

class CP_Import_Option {

	var $name;
	var $value;
	var $description;
	var $editable;
	var $html_type;
	var $possible_values;

	public function CP_Import_Option($n, $v, $d = "", $e = false, $h = "text", $p = "") {
		$this->name = $n;
		$this->value = $v;
		$this->description = $d;
		$this->editable = $e;
		$this->html_type = $h;
		$this->possible_values = $p;
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
