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
	var $DEBUG = false;
	var $settings;
	const form_action = "admin.php?page=cp-import/import";

	/*
	 * CP_Import
	 *
	 * Creates a new CP_Import object.
	 *
	 * @since 1.0
	 */
	function CP_Import () {
		$this->step = $_REQUEST['step'];
	}

	function load_settings($cpis) {
		$this->settings = $cpis;
	}

	static function ui_logo() {
		return "<div style='padding: 20px 0px;'><img src='".get_bloginfo('home')."/wp-content/plugins/cp-import/img/cpimport.gif' /></div>";
	}

	function ui_welcome_screen() {		

		echo CP_Import::ui_logo();
		?>
		<div class='narrow' style='float: left;'>
			<p>Hey there, this plugin makes moving from <a href='http://collegepublisher.com/'>College Publisher</a> (CP) to Wordpress a snap. Just follow the directions below and on the subsequent screens.</p>
			<p>There are a few rules and guidelines, though. See the <a href='http://wordpress.org/tags/cp-import'>documentation</a> for the most up-to-date information.</p>
			<p>Here is what you will need to use this importer:</p>
			<ul>
				<li>- Your archive file: Export_Story_Table_XXXX_XX-XX-XXXX.csv</li>
				<li>- Your media file: Export_Story_Media_Table_XXX-XX-XX-XXXX.csv</li>
				<li>- Your media folder (paperXXXX)</li>
			</ul>
		
			<p>CP Import is not yet compatible with College Publisher 5. If you are coming from CP 5, please proceed at your own risk. If you can get me your export files, I will make this compatible with CP 5.</p>
		
			<h3>Prerequisite Actions</h3>
			<p>Be sure to check out the <a href='admin.php?page=cp-import/settings'>Settings</a> page before importing to customize CP Import&apos;s behavior.</p>
			<p>The following things must be done <b><i>BEFORE</i></b> you begin using this plugin:</p>
			<ol>
				<li>Upload the contents of your media folder to: <b><?php echo $this->settings->get('media_dir_hr')->getValue(); ?></b> Be warned: This will take a <i>long</i> time.</li>
				<li>Due to default PHP settings, and the large size of College Publisher&apos;s export files, CP Import will automatically break your uploaded archive file into chunks of <a href='admin.php?page=cp-import/settings'><?php echo $this->settings->get('split_threshold')->getValue(); ?> articles</a>. This will prevent CP Import from running longer than PHP allows scripts to run and timing out</li>
			</ol>
		</div>
		<?php

		CP_Import::ui_donate();
	}	

	static function ui_donate () {
		echo "<div style='float: left; width: 150px; text-align: center; margin-left: 10px; padding: 10px; background-color: #FFFAD4; border:1px solid #FF2700;'><h3>Please Donate</h3><form action='https://www.paypal.com/cgi-bin/webscr' method='post'><input type='hidden' name='cmd' value='_s-xclick'><input type='hidden' name='hosted_button_id' value='5789559'><input type='image' src='https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif' border='0' name='submit' alt='PayPal - The safer, easier way to pay online!'><img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'></form><p>Thanks for using CP Import. I hope it saves you the headache experienced by me and others before me who have moved their newspapers from College Publisher to Wordpress.</p><p> Over 20 hours of coding, debugging, testing, and more debugging went into the creation of this plugin, and you'll be up and running in a fraction of that time. Please consider donating.</div>";
	}

	/*
	 * ui_step1
	 *
	 * Displays a greeting message explaining prerequisite actions to the user, and prompts the user to upload thier archive file
	 *
	 * @since 1.0
	 */
	function ui_import () {
		echo CP_Import::ui_logo();
		echo "<div class='narrow' style='float: left'>";

		switch($this->step) {
			default:
			case 1:
		?>
		<p><strong>BEFORE PROCEEDING: </strong>Please ensure that you have completed the necessary <a href="admin.php?page=cp-import">prework</a> before continuing.</p>
		<h3>Step 1: Specify your archive file</h3>
		<?php wp_import_upload_form(CP_Import::form_action."&amp;step=2"); ?>
		<p><strong>OR</strong></p>
		<form method='post' action='<?php echo CP_Import::form_action;?>&amp;step=2'>
		Enter the path of an archive file already on your webserver:
		<input type='text' name='archive_file_typed' size='25' value='<?php echo $this->settings->get('archive_file')->getValue(); ?>'/>
		<p><input type='submit' value='     Next     ' /></form>
		
		<?php
				break;
			case 2:
				$file = wp_import_handle_upload();
				if ( isset($file['file']) )
					$this->settings->set('archive_file', $file['file']);
				else if ( isset($_REQUEST['archive_file_typed']) && is_file($_REQUEST['archive_file_typed']) )
					$this->settings->set('archive_file', $_REQUEST['archive_file_typed']);
				else
					wp_die($file['error']);

		?>
		<div class='updated'>
			<p>Archive file uploaded! (<?php echo $this->settings->value('archive_file');?>)</p>
		</div>
		<?php

				$this->settings->save();
		?>
		<h3>Step 2: Upload Your Media File</h3>
		<p><strong>REMEMBER</strong> to upload the contents of your media <u>folder</u> (paperXXXX) to <strong><?php echo $this->settings->value('media_dir');?></strong>. The importer will look for the files referenced in your media <u>file</u> in this location. <strong>Do this before proceeding!</strong></p>
		<?php if (true) { //$this->settings->get('from_version')->getValue() == "5") { ?>


		
		<?php } else { ?>
		wp_import_upload_form(CP_Import::form_action."&amp;step=3"); ?>
		<p><strong>OR</strong></p>
		<form method='post' action='<?php echo CP_Import::form_action;?>&amp;step=3'>
		Enter the path of a media file already on your webserver:
		<input type='text' name='media_file_typed' size='25' value='<?php echo $this->settings->value('media_file');?>'/>
		<p><input type='submit' value='     Next     ' /></form>

		<?php
			}
				break;
			case 3:
				$file = wp_import_handle_upload();
				if ( isset($file['file']) )
					$this->settings->set('media_file', $file['file']);
				else if ( isset($_REQUEST['media_file_typed']) && is_file($_REQUEST['media_file_typed']) )
					$this->settings->set('media_file', $_REQUEST['media_file_typed']);
				else
					wp_die($file['error']);

		?>
		<div class='updated'>
			<p>Media file uploaded! (<?php echo $this->settings->value('media_file');?>)</p>
		</div>
		<?php

				$this->settings->save();
		?>		
		<h3>Step 3: Verify that everything is here</h3>
		<p>Checking for required files and folders...</p>
		<?php $this->ensure_exists(); ?>

		<p>Everything looks good!</p>

		<p>Once you click the button below, your archive file will automatically be parsed into a properly-formatted CSV file, such as having linebreaks encoding into HTML, special characters encoded into HTML, and the like. During testing, this process only took about 3 minutes for over 11,000 articles. Go grab a drink while your waiting.</p>

		<p>You MIGHT encounter a script time-out after clicking the button. If this occurs, you will need to change PHP's 'max_execution_time' setting.</p>

		<p><form action='<?php echo CP_Import::form_action;?>&amp;step=4' method='post'><input type='submit' class='button' value='Properly format my College Publisher Archive!' /></form></p>
		<?php		
				break;
			case 4:
				$prep = new CP_Import_Prepare();
				$outfile = CP_IMPORT_DIR."/tmp/cp-archive-formatted.csv";
				
		?>
		<div id="output" style="height: 300px; width: 100%; overflow: auto;">
			<?php //$prep->process($this->settings->value('archive_file'), $outfile); ?>
		</div>
		
		<p>Formatting complete! It took <?php echo $prep->get_run_time();?> minutes.</p>
		<?php
				$this->settings->set('archive_file', $outfile);
		?>
		<p>CP Import is now going to split you archive file into chunks of <?php echo $this->settings->value('split_threshold');?> articles.</p>

		<p><form action='<?php echo CP_Import::form_action;?>&amp;step=5' method='post'><input type='submit' class='button' value='Split my College Publisher Archive!' /></form></p>
		<?php
				break;
			case 5:
				$split = new CP_Import_FileSplitter();
				$split->configure(
					$this->settings->value('media_file'),
					CP_IMPORT_DIR."/tmp/",
					$this->settings->value('split_threshold')
				);

				$files = $split->run();

				print_r($files);

				break;
		}
		
		echo "</div>";

		CP_Import::ui_donate();
	}


	private function ensure_exists() {

		$archive = $this->settings->value('archive_file');
		$media   = $this->settings->value('media_file');
		$media_dir = $this->settings->value('media_dir');

		?>
		<ul>
			<li><b>Archive file:</b>&nbsp;
		<?php
		if (!file_exists($archive)) {
			wp_die('Your archive file seems to have disappeared...<br/><br/>Make sure someone didn\'t accidentally delete it.');
		}
		else {	
		?>
			okay! <pre><?php echo $archive;?></pre>
		<?php
		}
		?>
			</li>
			<li><b>Media file:</b>&nbsp;
		<?php
		if (!file_exists($media)) {
			wp_die('Your media file seems to have disappeared...<br/><br/>Make sure someone didn\'t accidentally delete it.');
		}
		else {	
		?>
			okay! <pre><?php echo $media;?></pre>
		<?php
		}
		?>
			</li>
			<li><b>Media folder:</b>&nbsp;
		<?php
		if (	!is_dir($media_dir) &&
			!is_dir($media_dir."/stills") &&
			!is_dir($media_dir."/audio") &&
			!is_dir($media_dir."/video")
		) {
			wp_die('Your media folder was not uploaded correctly. '.
			'Remember that you needed to do this manually. '.
			'You need to upload the <b>contents</b> of the <b>paperXXXX</b> folder '.
			'that CP gave you to <b>'.$media_dir.'</b>. If done correctly, the '.
			'file structure should look similar to this:<br/><br/>'.$media_dir.'audio<br/>'.
			''.$media_dir.'stills<br/>'.$media_dir.'video<br/><br/>Once you\'ve '.
			'uploaded the media folder, refresh this page to try again.');
		}
		else {
		?>
			okay!
		<?php	
		}

		return true;
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
						echo "&nbsp;&nbsp;&nbsp;done!</i><br/>";
						$count++;
					} // end non-numeric
				} // end while
				// Undo ini changes
				ini_set('auto_detect_line_endings',$auto_detect_line_endings);

					
				echo "<p>".$count.__(' articles were successfully imported!')."</p>";
				echo "<p>Congratulations! Your CP archive has been successfully imported to Wordpress! Have fun!</p>";
				echo "<p><a href='admin.php?page=cp-import/cp-import.php'>Import again</a></p>";
				$this->ui_footer();
				break;
				
		} //switch
	} // function
} // class

?>
