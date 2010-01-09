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

/**
 * Creates a new CP_Import object. This class contains all the functions for
 * parsing and extracting data from a College Publisher archive file formatted 
 * by the accompanying Perl script.
 *
 * @author John Luetke
 */
class CP_Import {

	/**
	 * @var boolean DEBUG
	 */
	var $DEBUG = true;

	/*
	 * CP_Import
	 *
	 * Creates a new CP_Import object.
	 *
	 * @since 1.0
	 */
	function CP_Import () {
		global $wpdb;
		
		$this->step = (isset($_GET['step'])) ? $_GET['step'] : 1;

		$this->options = get_option('cp-import-options');

		if ($this->DEBUG)
			echo "<pre>".print_r($this, true)."</pre>";
	}

	/**
	 * save_options
	 *
	 * Saves $this->options to the database
	 *
	 * @since 2.0
	 */
	function save_options() {
		update_option("cp-import-options", $this->options);
	}

	/*
	 * ui_header
	 *
	 * Displays the header of the UI
	 *
	 * @since 1.0
	 */
	function ui_header() {
		echo "<div class='wrap'>\n";
		echo "<div style='padding: 20px 0px 0px 0px;'><img src='".get_bloginfo('home')."/wp-content/plugins/cp-import/img/cpimport.gif'</div>";
		echo "<p>";

		if (is_numeric($this->step))
			echo "<strong>";
		else
			echo "<a href=\"tools.php?page=cp-import/cp-import.php&amp;step=1\">";

		echo __('Import');
		
		if (is_numeric($this->step))
			echo "</strong>";
		else
			echo "</a>";

		echo "&nbsp;|&nbsp;";
		
                if ($this->step == "options")
			echo "<strong>";
		else 
			echo "<a href=\"tools.php?page=cp-import/cp-import.php&amp;step=options\">";
	        
		echo __('Options');
		
		if ($this->step == "options")
			echo "</strong>";
		else
			echo "</a>";
		
		echo "</p>";
							       
	}

	/*
	 * ui_footer
	 *
	 * Closes the HTML printed by ui_header()
	 *
	 * @since 1.0
	 */
	function ui_footer () {
		echo "</div>";
	}
	
	function ui_donate () {
		echo "<div style='float: left; width: 150px; text-align: center; margin-left: 10px; padding: 10px; background-color: #FFFAD4; border:1px solid #FF2700;'><h3>Please Donate</h3><form action='https://www.paypal.com/cgi-bin/webscr' method='post'><input type='hidden' name='cmd' value='_s-xclick'><input type='hidden' name='hosted_button_id' value='5789559'><input type='image' src='https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'><img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'></form><p>Thanks for using CP Import. I hope it saves you the headache experienced by me and others before me who have moved their newspapers from College Publisher to Wordpress.</p><p> Over 20 hours of coding, debugging, testing, and more debugging went into the creation of this plugin, and you'll be up and running in a fraction of that time. Please consider donating.</div>";
	}

	/*
	 * ui_options()
	 *
	 * Displays customizable settings for CP Import's behavior
	 *
	 * @since 1.5
	 */
	function ui_options () {
		$this->ui_header();
?>
		<div class='narrow' style='float: left;'>
			<h3><?php echo _('Options');?></h3>
			<form action="tools.php?page=cp-import/cp-import.php&amp;step=options&amp;saved=1" method="post">
			<?php
			if ($_POST) {

				if ($this->DEBUG) echo "<pre>".print_r($_POST, true)."</pre>";

				update_option("cp_import_from", $_POST['import_from']);
				update_option("cp_import_user", $_POST['create_users']);
				update_option("cp_import_default_user", $_POST['default_user']);
				update_option("cp_import_username_before", $_POST['username_before']);
				update_option("cp_import_username_after", $_POST['username_after']);
				update_option("cp_import_paper_id", $_POST['paper_id']);
				update_option("cp_import_verbose", $_POST['verbose']);

				// Refresh options
				$this->paper_id		= $_POST['paper_id'];
				$this->default_user	= $_POST['default_user'];
				$this->import_from 	= $_POST['import_from'];
				$this->user_type 	= $_POST['create_users'];
				$this->username_prefix	= $_POST['username_before'];
				$this->username_suffix	= $_POST['username_after'];
				$this->verbose		= $_POST['verbose'];

				if ($_POST['url_structure'] == 1 && $_POST['paper_id'] != "") { // CP URL's
					switch ($_POST['import_from']) {
						case 4:
						default:
							update_option("permalink_structure", $this->cp4link);
							break;
						case 5:
							update_option("permalink_structure", $this->cp5link);
							break;
					}
				}
			?>
			<div id="message" class="updated fade"> 
				<strong><p>Options Saved!</p></strong>
				<?php if ($_POST['url_structure'] == 1) { ?>
				<p><strong>Addtional Action Required:</strong> To activate your new Permalink style, you need to go to the <a href="options-permalink.php">Permalink Page</a> and click "Save Changes".</p>
				<?php } ?>
			</div>
			<?php
			
			}
			?>
			
			<table>
				<tr>
					<td valign="top"  width="20%">Import From:</td>
					<td valign="top"  width="20%"><input type="radio" name="import_from" value="4" checked="checked"/>&nbsp;CP 4<br/><input type="radio" name="import_from" value="5" disabled="disabled"/>&nbsp;CP 5</td>
					<td valign="top"  width="40%">Which version of College Publisher are you importing from?</td>
				</tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr>
					<td valign="top" >Import Authors as:</td>
					<td valign="top" >
						<input type="radio" name="create_users" value="accounts" group="create_users" <?php echo (($this->user_type == "accounts") ? "checked='checked' " : ""); ?>/>&nbsp;Wordpress Accounts<br />
						<input type="radio" name="create_users" value="fields" group="create_users" <?php echo (($this->user_type == "fields") ? "checked='checked' " : ""); ?>/>&nbsp;Custom Field<br />
						<input type="radio" name="create_users" value="none" group="create_users" <?php echo (($this->user_type == "none") ? "checked='checked' " : ""); ?>/>&nbsp;None
					</td>
					<td valign="top" >
						CP Import can either create a Wordpress User account for each author that it finds, or add that information to each post as a custom field.<br /><br />More options are below if you choose "Wordpress Account". If you choose "Custom Field", author data will be imported as-is from you Archive file.
					</td>
				</tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr>
					<td valign="top" >Default User Account:</td>
					<td valign="top" >
						<?php $this->ui_userlist(); ?>
					</td>
					<td valign="top" >
						If any kind of error occurs while attempting to import author data, or you chose to import article authors as Custom fields, which account should be used?
					</td>
				</tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr>
					<td valign="top" >Username Format:</td>
					<td valign="top" >
						<input type="text" name="username_before" size="15" value="<?php echo $this->username_prefix;?>"/>
						%username%
						<input type="text" name="username_after" size="15" value="<?php echo $this->username_suffix;?>"/>
					</td>
					<td valign="top" >
						If importing authors as Wordpress accounts, how should thier username be formatted? <strong>%username%</strong> is automatically created by CP Import as <pre>firstname.lastname</pre>
					</td>
				</tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr>
					<td valign="top" >CP URL Structure?</td>
					<td valign="top" >
						<input type="checkbox" name="url_structure" value="1" />&nbsp;Enabled&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CP Paper ID: <input type="text" size="5" maxlength="4" name="paper_id" value="<?php echo $this->paper_id;?>" />
					</td>
					<td valign="top" >
						Would you like CP Import to set your permalink structure to mimic that of College Publisher? (Experimental).<br /><br />You can come back to change this later without having to re-import your articles.
					</td>
				</tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr>
					<td align="top">Enable Verbose mode?</td>
					<td valign="top"><input type="radio" name="verbose" value="1" group="verbose" <?php echo (($this->verbose) ? "checked='checked' " : ""); ?> disabled="disabled"/>&nbsp;&nbsp;&nbsp;Yes<br /><input type="radio" name="verbose" value="0" group="verbose" <?php echo ((!$this->verbose) ? "checked='checked' " : ""); ?> />&nbsp;&nbsp;&nbsp;No</td>
					<td valign="top">Would you like additional output displayed when importing articles?</td>
				</tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr><td valign="top" >&nbsp;</td></tr>
				<tr>
					<td valign="top" >
						<input type="submit" value="Save Options" class="button" />
					</td>
				</tr>
			</table>
			</form>
		</div>
<?php
		$this->ui_donate();
		$this->ui_footer();

	}

	/**
	 * ui_userlist
	 *
	 * Echos a drop-down list of all the user accounts on this instance
	 *
	 * @return HTML select list
	 *
	 * @since 1.5
	 */
	function ui_userlist() {
		global $wpdb;
	
		$users = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users ORDER BY %s ASC", "display_name" ));

		$xhtml = "<select name='default_user'>";

		foreach ($users as $user) {
			
			$user = get_userdata( $user );
			$xhtml .= "<option value='".$user->ID."' ".(($this->default_user == $user->ID) ? "selected='selected'" : "")."/>".(($user->first_name) ? $user->display_name : $user->user_login);
		}

		$xhtml .= "</select>";

		echo $xhtml;
	}
	
	/*
	 * ui_step1
	 *
	 * Displays a greeting message explaining prerequisite actions to the user, and prompts the user to upload thier archive file
	 *
	 * @since 1.0
	 */
	function ui_step1 () {
		$this->ui_header();
		echo "<div class='narrow' style='float: left;'>";
		echo "<p>".__('Hey there, this plugin makes moving from <a href="http://collegepublisher.com/">College Publisher</a> (CP) to Wordpress a snap. Just follow the directions below and on the subsequent screens.')."</p>";
		echo "<p>".__('There are a few rules and guidelines, though. See the <a href="http://wordpress.org/tags/cp-import">documentation</a> for the most up-to-date information.')."</p>";
		echo "<p>".__('Here is what you will need to use this importer:')."</p>";
		echo "<ul>";
		echo "<li>".__('- Your archive file: Export_Story_Table_XXXX_XX-XX-XXXX.csv')."</li>";
		echo "<li>".__('- Your media file: Export_Story_Media_Table_XXX-XX-XX-XXXX.csv')."</li>";
		echo "<li>".__('- Your media folder (paperXXXX)')."</li>";
		echo "</ul>";
		
		echo "<p>".__('CP Import is not yet compatible with College Publisher 5. If you are coming from CP 5, please proceed at your own risk. If you can get me your export files, I will make this compatible with CP 5.')."</p>";
		
		echo "<h3>".__('Prerequisite Actions')."</h3>";
		echo "<p>".__('Be sure to check out the <a href="tools.php?page="cp-import/cp-import.php&amp;step=options">Options</a> page before importing to customize CP Import\'s behavior.')."</p>";
		echo "<p>".__('The following things must be done <b><i>BEFORE</i></b> you begin using this plugin:')."</p>";
		echo "<ol>";
		echo "<li>".__('Upload the contents of your media folder to:')." <b>".$this->options['media_dir_hr']."</b> ".__('Be warned: This will take a <i>long</i> time.')."</li>";
		echo "<li>".__('Due to default PHP settings, and the large size of College Publisher&apos;s export files, CP Import will automatically break your uploaded archive file into chunks of 1000 articles. This will prevent CP Import from running longer than PHP allows scripts to run and "timing out".')."</li>";
		echo "</ol>";
		echo "<h3>".__('Step 3: Specify your archive file')."</h3>";
		wp_import_upload_form("tools.php?page=cp-import/cp-import.php&amp;step=2");
		echo "<p><strong>".__('OR')."</strong></p>";
		echo "<form method='post' action='tools.php?page=cp-import/cp-import.php&amp;step=2'>";
		echo __('Enter the path of an archive file already on your webserver:');
		echo " <input type='text' name='archive_file_typed' size='25' value='".$this->options['archive_file']."'/>";
		echo "<p><input type='submit' value='     ".__('Next')."     ' /></form>";
		echo "</div>";
		$this->ui_donate();
		$this->ui_footer();
	}

	/*
	 * ui_step2
	 *
	 * Grabs the uploaded archive file from ui_step1, and prompts for the user to upload their media folder to $this->media_dir and thier media file using a form
	 *
	 * @since 1.0
	 */
	function ui_step2 () {
		$this->ui_header();
		echo "<div class='narrow' style='float: left;'>";
		
		$file = wp_import_handle_upload();

		if ( isset($file['file']) )
			$this->options['archive_file'] = $file['file'];
		else if ( isset($_POST['archive_file_typed']) && is_file($_POST['archive_file_typed']) )
			$this->options['archive_file'] = $_POST['archive_file_typed'];
		else
			die($file['error']);

		$this->save_options();

		echo "<p>".__('Your archive file has been successfully uploaded!')."</p>";

		echo "<h3>".__('Step 4: Upload Your Media File')."</h3>";

		echo "<p>".__('<b>REMEMBER</b> to upload the contents of your media <u>folder</u> (paperXXXX) to '.$this->media_dir.'. The importer will look for the files referenced in your media <u>file</u> in this location.');
		echo "<b> ".__('Do this before proceeding!')."</b></p>";
	
		wp_import_upload_form("tools.php?page=cp-import/cp-import.php&amp;step=3");
		echo "<p><strong>".__('OR')."</strong></p>";
		echo "<form method='post' action='tools.php?page=cp-import/cp-import.php&amp;step=3'>";
		echo __('Enter the path of a media file already on your webserver:');
		echo " <input type='text' name='media_file_typed' size='25' value='".$this->options['media_file']."'/>";
		echo "<p><input type='submit' value='     ".__('Next')."     ' /></form>";

		echo "</div>";
		$this->ui_donate();
		$this->ui_footer();
	}

	/*
	 * ui_step3
	 *
	 * Grabs the uploaded media file, and checks to make sure that all required files and folders are present.
	 *
	 * @since 1.0
	 */
	 function ui_step3 () {
		$this->ui_header();
		echo "<div class='narrow' style='float: left;'>";

		$file = wp_import_handle_upload();

		if (isset($file['file']) )
			$this->options['media_file'] = $file['file'];
		else if ( isset($_POST['media_file_typed']) && is_file($_POST['media_file_typed']) )
			$this->options['media_file'] = $_POST['media_file_typed'];
		else
			die($file['error']);

		$this->save_options(); 
			
		echo "<p>".__('Media file successfully uploaded!')."</p>";
					
		echo "<h3>".__('Step 6: Verify that everything is here')."</h3>";
					
		echo "<p>".__('Checking for required files and folders...')."</p>";
		echo "<ul><li>".__('<b>Archive file:</b> ');
		if (file_exists($this->options['archive_file']))
			echo __(' okay!')."<pre>          ".basename($this->options['archive_file'])."</pre></li>";
		else
			wp_die('Your archive file seems to have disappeared...<br/><br/>Make sure someone didn\'t accidentally delete it or that you didn\'t modify the URL that took you to this page.');
			
		echo "<li>".__('<b>Media file:</b> ');
		if (file_exists($this->options['media_file']))
			echo __(' okay!')."<pre>          ".basename($this->options['media_file'])."</pre></li>";
		else
			wp_die('Your media file seems to have disappeared...');

		echo "<li>".__('<b>Media folder:</b> ');
		
		if (	is_dir($this->options['media_dir']) &&
			is_dir($this->options['media_dir']."/stills") &&
			is_dir($this->options['media_dir']."/audio") &&
			is_dir($this->options['media_dir']."/video")
		)
			echo __(' okay!')."<pre>          ".$this->options['media_dir_hr']."</pre></li></ul>";
		else {
			wp_die('Your media folder was not uploaded correctly. '.
			'Remember that you needed to do this manually. '.
			'You need to upload the <b>contents</b> of the <b>paperXXXX</b> folder '.
			'that CP gave you to <b>'.$this->options['media_dir_hr'].'</b>. If done correctly, the '.
			'file structure should look similar to this:<br/><br/>'.$this->options['media_dir_hr'].'audio<br/>'.
			''.$this->options['media_dir_hr'].'stills<br/>'.$this->options['media_dir_hr'].'video<br/><br/>Once you\'ve '.
			'uploaded the media folder, refresh this page to try again.');
		}

		echo __('<p>Everything looks good! Now comes the time for the main event! Once you click the button '.
		'below, Wordpress is going to be working for a while. If you experience any errors about "timing-out" '.
		'or "too large", you\'ll need to break your archive file into smaller chunks. If that happens, you\'ll '.
		'still need to go through the motions of this importer with each of the smaller chunks, but you DO NOT '.
		'need to re-upload your media folder. If any other weird errors happen, '.
		'<a href="http://wordpress.org/tags/cp-import">see the documentation</a>. Good luck, and happy Wordpress\'ing!</p>');
			
			echo "<p><form action='tools.php?page=cp-import/cp-import&step=4&archive=".$this->archive_file."&media=".$this->media_file."' method='post'><input type='submit' class='button' value='".__('Import my College Publisher Archives!')."' /></form></p>";
			
		echo "</div>";
		$this->ui_donate();
		$this->ui_footer();
	}

	/*
	 * process_date
	 *
	 * Formats the date according to $this->date_format
	 *
	 * @param array $article array containing information about the article to be imported
	 *
	 * @return array array parameter with the 'post_date' key modified
	 *
	 * @since 1.0
	 */
	function process_date ( $article ) {
		$article['post_date'] = $this->format_date($article['post_date'], $this->date_format);

		return $article;
	}
	
	/*
	 * process_author
	 *
	 * Formats the author's name into firstname.lastname, and removes any apostrophes.
	 *
	 * @param array $article array containing information about the article to be imported
	 *
	 * @return array array parameter key 'post_author' modified.
	 *
	 * @since 1.0
	 */
	function process_author ( $article ) {

		$article['post_author_name'] = $article['post_author'];
	
		$first_name = substr($article['post_author'], 0, strpos($article['post_author'], " "));
		$last_name = substr($article['post_author'], strpos($article['post_author'], " ")+1, strlen($article['post_author']));
		$article['post_author'] = strtolower($first_name.".".$last_name);
	
		// Remove apostrophe's from author's name
		$article['post_author'] = $this->filter_content($article['post_author'],"'","");
		$article['post_author'] = $this->filter_content($article['post_author']," ","");

		return $article;
	}

	/*
	 * get_user_id
	 *
	 * The function adheres to the "cp_import_user" option
	 *
	 * If set to "accounts", it queries the Wordpress database to see if a user with the name of the article's author exists.
	 * If it does not exist, then create a new user with that name and a random password. If so, grab the existing user_id.
	 *
	 * If set to "fields", it will attach the author's name as specified in the archive file to the post as a custom field. The author
	 * will be set to the ID saved in "cp_import_default_user"
	 *
	 * If set to "none", this function will query the database to see if a user_id exists. If not, it will use the default id.
	 *
	 * In any case, the key 'post_author' will be replaced with the new Wordpress user ID.
	 *
	 * Additionally, this function will call $this->set_user_info, which modifies the username created with
	 * $this->process_author (firstname.lastname) to First Name, Last Name.
	 *
	 * @param array $article array containing information about the article to be imported
	 *
	 * @return array array parameter with 'post_author' set to a Wordpress user id.
	 *
	 * @since 1.0
	 */
	function get_user_id ( $article ) {

		$account = $this->username_prefix . $article['post_author'] . $this->username_suffix;

		$exists = username_exists($account);
		if ($this->verbose) echo "&nbsp;&nbsp;&nbsp;".__('Searching for account:') . " " . $account . "...";
		$name = $article['post_author'];


		switch ($this->user_type) {

			case "accounts":
			default:

				if ($exists) {
					if ($this->verbose) echo __('found!')."<br/>";
 				}
				else {
					$exists = wp_create_user ($account, md5(time()));
					if ($this->verbose) echo __('new acount created!')."<br />";
				}
				$article['post_author'] = $exists;
				$this->set_user_info($id, $name);

				break;

			case "fields":
				if ($this->default_user > 0) {
					$article['post_author'] = $this->default_user;
				}
				else {
					$article['post_author'] = 1;
				}
				if ($this->verbose) echo __('found!')."<br/>";
				break;

			case "none":
				if ($exists) {
					$article['post_author'] = $exists;
					if ($this->verbose) echo __('found!')."<br/>";
					$this->set_user_info($exists, $article['post_author_name']);
				}
				else {
					if ($this->default_user > 0) {
				        	$article['post_author'] = $this->default_user;
					}
					else {
					        $article['post_author'] = 1;
					}
				}
				break;
		}
	
		return $article;
	}

	/*
	 * set_user_info
	 *
	 * Formats the user's display_name into a readable format
	 *
	 * @param int $id the Wordpress user ID
	 * @param string $name the name generated by $this->process_author
	 *
	 * @since 1.0
	 */
	 function set_user_info ( $id, $name ) {
	 	if ($this->DEBUG)
			echo "<pre>set_user_info(".$id.", ".$name.")</pre>";

		$t['display_name'] = $name;
		$t['ID'] = $id;

		wp_update_user($t);
	}
	 
	/*
	 * get_category_id
	 *
	 * Queries the Wordpress datab ase to see if a category with the name of the one that this article came from exists. 
	 * If so, replace the human-readable name with the category id.
	 * If not, create a new category with that name and return the new id.
	 *
	 * @param array $article array containing information about the article to be imported
	 *
	 * @return array array paramter with 'post_category' modified
	 *
	 * @since 1.0
	 */
	function get_category_id ( $article ) {
		$cat_id = $article['post_category'];
		$new_id = array();
		
		foreach ($cat_id as $id) {
			if ($this->verbose) echo "&nbsp;&nbsp;&nbsp;".__('Searching for category "'.$id.'"... ');
			$new_id[] = wp_create_category($id);
			if ($this->verbose) echo __('done!')."<br />";
		}
	
		$article['post_category'] = $new_id;
		
		return $article;
	}
	
	/*
	 * filter_content
	 *
	 * Helper function, do not call directly. @see process_author
	 *
	 * @since 1.0
	 */
	function filter_content ( $subject, $str, $replacement ) {
		return str_replace($str, $replacement, $subject);
	}
	
	/*
	 * format_date
	 *
	 * Helper function. Do not call directly. @see process_date
	 *
	 * @since 1.0
	 */
	function format_date ($date, $dateString ) {
		$m = substr($date, 0,3);
		$d = (int) substr($date, 4,2);
		$y = (int) substr($date, 7,4);
	
		$hh = (int) substr($date, 12,2);
		$mm = (int) substr($date, 15,2);
		$ap = (int) substr($date, 17,2);

		if ($ap == "AM" && $hh == 12)
			$hh = 0;
		else if ($ap == "PM" && $hh == 12)
			$hh = 12;
		else if ($ap == "PM")
			$hh = $hh + 12;

		if ($m == "Jan") $m = 1;
		if ($m == "Feb") $m = 2;
		if ($m == "Mar") $m = 3;
		if ($m == "Apr") $m = 4;
		if ($m == "May") $m = 5;
		if ($m == "Jun") $m = 6;
		if ($m == "Jul") $m = 7;
		if ($m == "Aug") $m = 8;
		if ($m == "Sep") $m = 9;
		if ($m == "Oct") $m = 10;
		if ($m == "Nov") $m = 11;
		if ($m == "Dec") $m = 12;
	
		$m = (int) $m;
	
		$date = @mktime($hh,$mm,0,$m,$d,$y);
		$date = date( $dateString, $date);

		return $date;

	}
	
	/*
	 * get_date_elements
	 *
	 * @param Wordpress-formatted date
	 *
	 * @return array containing the year, month and day values from the parameter string.
	 *
	 * @since 1.5
	 */
	function get_date_elements( $date ) {
		if ($this->DEBUG)
			echo "<pre>get_gate_elements(".$date.")</pre><br />";
			
		$ret = array();
		array_push($ret, substr($date,8,2));
		array_push($ret, substr($date,5,2));
		array_push($ret, substr($date,0,4));

		if ($this->DEBUG)
			echo "<pre>get_date_elements returning:".print_r($ret, true)."</pre>";
	
		return $ret;
	}

	/**
	 * optimizeMediaFile
	 *
	 * Optimizes $this->media_file into a nested array by article_id.
	 *
	 * @return array
	 *
	 * @since 1.0
	 *
	 */
	function optimizeMediaFile() {

		$hndl = fopen($this->media_file, "r");
		$r = array();

		while (($i = fgetcsv($hndl, 1024)) !== FALSE) {
			
			$t['file'] = $i[1];
			$t['caption'] = $i[2];
			$t['credit'] = $i[3];

			$n = count($r[$i[0]]);

			$r[$i[0]][$n] = $t;

		}

		return $r;
	}

	/*
	 * get_mime
	 *
	 * Gets the mime type of the specified file by executing a shell command.
	 * THIS WILL ONLY WORK ON A UNIX / LINUX SERVER
	 *
	 * @param string $file file whose mime type is to be determined
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	function get_mime($file) {
		return trim(exec('file -b --mime '.escapeshellarg($file)));
	}

	/*
	 * go
	 *
	 * The main event! This displays the steps, and processes the articles.
	 */
	function go() {
		
		// determine the step we are one
		switch ( $this->step ) {
			case 'options':
				$this->ui_options();
				break;
			case 1:
			default:
				$this->ui_step1();
				break;
			case 2:
				$this->ui_step2();
				break;
			case 3:
				$this->ui_step3();
				break;
			case 4:	
				// Here is where the action REALLY happens
				//
				// Get a handle for the CSV files
				$archive_hndl = fopen($this->archive_file, "r");
				$media_hndl = $this->optimizeMediaFile();//($this->media_file);//fopen($this->media_file, "r");

				$this->ui_header();

				// counter for article's imported
				$count = 0;

				// Safety setting for different line endings.                                      
				$auto_detect_line_endings = ini_get('auto_detect_line_endings');
				ini_set('auto_detect_line_endings',TRUE);
				
				// Loop through each of the articles.
				while (($row = fgetcsv($archive_hndl, 16384)) !== FALSE) {
						
					$article['cp_id']			= $row[0];
					$article['post_title']		= $row[4];
					$article['post_sub_title']	= $row[5];
					$article['post_content']	= $row[7];
					$article['post_author']		= $row[8];
					$article['post_category']	= array($row[3]);
					$article['post_date']		= $row[2];
					$article['post_excerpt']	= $row[6];
						
					// default values
					$article['post_type']		= "post";
					$article['post_status']     = "publish";
					$article['comment_status']	= "closed";
			
					// transform data into Wordpress formats
					$article = $this->process_date(&$article);
					$article = $this->process_author(&$article);
			
					// If the ID is not numeric, skip it
					if (is_numeric($article['cp_id'])) {
			
						// begin output
						echo __('Importing article: ')."<i>".$article['post_title']."</i>...";
						if ($this->verbose) echo "<br />";
						// get the ID of this article's author
						$article = $this->get_user_id(&$article);
							
						// get / create the category that this article comes from
						$article = $this->get_category_id(&$article);

						// Append the [gallery] tag to the end of each post. If there is no 
						// media associated with the post, this will have no effect.
						$article['post_content'] .= "<p>[gallery]</p>";

						// insert the article into the Wordpress database, and get it's new ID
						$wp_id = wp_insert_post($article);
						//echo "<pre>".print_r($article,true)."</pre>";
						
						// set the new article's ID to that of CP
						$query = $this->wpdb->prepare("UPDATE ".$this->wpdb->posts." SET ID = %d WHERE ID = %d", $article['cp_id'], $wp_id);
						$this->wpdb->query($query);

						// Update category for new ID
						$query = $this->wpdb->prepare("UPDATE ".$this->wpdb->term_relationships." SET object_id = %d WHERE object_id = %d", $article['cp_id'], $wp_id);
						$this->wpdb->query($query);

						// Update Post GUID to match CP URL structure.
						// This has no effect if the user chose not to use CP URLS
						//
						// This block of code will also replace the value of the $wp_id with the CP ID.
						$post_title = $this->wpdb->get_var($this->wpdb->prepare("SELECT post_name FROM ".$this->wpdb->posts." WHERE ID = %d", $article['cp_id']));
						$post_title2 = explode("-", $post_title);
						$post_title = "";
						foreach ($post_title2 as $pt) {
							$post_title .= ucfirst($pt).".";
							if (strlen($post_title) > 80) {
								$post_title = substr($post_title, 0, 80);
								break;
							}	
						}

						if (substr($post_title, strlen($post_title)-1) == ".")
							$post_title = substr($post_title, 0, strlen($post_title)-1);

						switch ($this->import_from) {
							case 4:
							default:
								$url = get_option('home').$this->cp4link;
								break;
							case 5:
								$url = get_option('home').$this->cp5link;
								break;
						}

						$cats = get_the_category($article['cp_id']);
						$date = $this->get_date_elements($article['post_date']);
							
						// Replace permalink keywords
						$url = str_replace("%post_id%", $article['cp_id'], $url);
						$url = str_replace("%postname%", $post_title, $url);
						$url = str_replace("%category%", $cats[0]->cat_name, $url);
						$url = str_replace("%year%", $date[2], $url);
						$url = str_replace("%monthnum%", $date[1], $url);
						$url = str_replace("%day%", $date[0], $url);
							
						$query = $this->wpdb->prepare("UPDATE ".$this->wpdb->posts." SET guid = %s WHERE ID = %d", $url, $article['cp_id']);
						$this->wpdb->query($query);
						$wp_id = $article['cp_id'];

						unset($post_title);
						unset($post_title2);
						unset($url);
						unset($query);
						unset($cats);
						unset($date);

						// End GUID modifications

						// attach the paper's CP ID as a custom field
						add_post_meta($wp_id, 'CP ID', $article['cp_id']);
							
						// if this article had a subheadline, add it as a cumton field to the new post
						if ( isset($article['post_sub_title']) )
							add_post_meta($wp_id, 'subheadline', $article['post_sub_title']);
							
						// if we are to attach author name as custom fields, do so
						if ( $this->user_type == "fields")
							add_post_meta($wp_id, 'author', $article['post_author_name']);
							
						if ($this->verbose) echo __('&nbsp;&nbsp;&nbsp;Searching for media...');
						
						// if the media array has media for this post, find it
						if (isset($media_hndl[$article['cp_id']])) {
							
							if ($this->verbose) echo __('found!')."<br />";
							
							// there could be muliple pieces of media per article, so loop
							foreach ($media_hndl[$article['cp_id']] as $img) {
							
								$attach = array();
								$attach['post_content'] = $img['caption']."<br /><br />Credit: ".$img['credit'];
								$attach['post_title'] = "IMAGE: ".$article['post_title'];
								$attach['post_author'] = $img['credit'];
								$attach['comment_status'] = 'closed';
								$attach['post_date'] = $article['post_date'];
								$attach['post_date_gmt'] = $article['post_date'];
								
								$attach = $this->process_author(&$attach);
								$attach = $this->get_user_id(&$attach);
								
								if ($this->verbose) echo "&nbsp;&nbsp;&nbsp;".__('Importing media...');
								//echo "<pre>".print_r($img,true)."</pre>";
								//echo "<pre>".print_r($attach,true)."</pre>";
							
								// make sure that the file refereneced exists
								if (!file_exists(WP_CONTENT_DIR."/cp-import".$img['file']))
									echo __('File not found: ')."<b>".$this->media_dir_hr.$img['file']."</b><br />";
								else {
									// determine paths where this media will be saved at
									$rel_path = date("Y") . "/" . date("m") . "/" . basename ($img['file']);	
									$dest_path = get_option('upload_path') . "/" . $rel_path;
								
									// get the mime type. this is important 
									$attach['post_mime_type'] = $this->get_mime(WP_CONTENT_DIR."/cp-import".$img['file']);
									$attach['guid'] = get_option('siteurl') ."/". $dest_path;
										
									// copy the media from wp-content/cp-import to the Wordpress media repository
									@copy (	$this->media_dir.$img['file'],
										ABSPATH . $dest_path );
									// insert the media into the WP database
									$attach_id = wp_insert_attachment($attach, false, $wp_id);
									// generate basic metadata
									$attach_meta = wp_generate_attachment_metadata( $attach_id, ABSPATH . $dest_path );
									// update the generated meta data.
									wp_update_attachment_metadata( $attach_id, $attach_meta);
									// add another piece of metadata
									add_post_meta($attach_id, "_wp_attached_file", $rel_path, true);
									// we're done!
									if ($this->verbose) echo __('done!')."<br />";
								}
							} // end foreach
						} // end if media. */
						
						// congratualtions!
						echo "&nbsp;&nbsp;&nbsp;".__('done!')."</i><br/>";
						$count++;
					} // end non-numeric
				} // end while
				// Undo ini changes
				ini_set('auto_detect_line_endings',$auto_detect_line_endings);

					
				echo "<p>".$count.__(' articles were successfully imported!')."</p>";
				echo "<p>".__('Congratulations! Your CP archive has been successfully imported to Wordpress! Have fun!')."</p>";
				echo "<p><a href='tools.php?page=cp-import/cp-import.php'>".__('Import again')."</a></p>";
				$this->ui_footer();
				break;
				
		} //switch
	} // function
} // class

?>