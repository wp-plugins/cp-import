<?php
/*
Plugin Name: CP Import
Version: 1.0
Plugin URI: http://johnluetke.net/software/cp-import
Description: CP Import allows you to import your <b>articles, authors, categories, and media<b> from a College Publisher export file <br />[<a href="tools.php?page=cp-import/cp-import.php">Import</a> | <a href="http://johnluetke.net/software/cp-import">Help</a>]
Author: John Luetke
Author URI: http://johnluetke.net
*/

function cp_import_admin_menu() {
	require_once ABSPATH . '/wp-admin/admin-functions.php';
	add_management_page('CP Import', 'CP Import', 9, __FILE__, 'cp_import_init');
}


function cp_import_init() {
	$CPImporter = new CP_Import();
	
	$CPImporter->go();
}

add_action('admin_menu', 'cp_import_admin_menu');

/**
 * CP Import
 *
 * @package net.johnluetke.software.wordpress.cpimport
 *
 * @copyright 2009 John Luetke < john@johnluetke.net >
 *
 * @filesource
 */

require("excel-reader.php");

/**
 * Creates a new CP_Import object. This class contains all the functions for parsing and extracting data from a College Publisher archive file
 * in .xls format.
 *
 * @author John Luetke
 */
class CP_Import {

	/**
	 * @var array $articles
	 */
	var $articles;
	
	/**
	 * @var string $date_format
	 */
	var $date_format;
	
	/**
	 * @var string $archive_file
	 */
	var $archive_file;
	
	/**
	 * @var string $media_file
	 */
	var $media_file;
	
	/**
	 * @var string $media_dir
	 */
	var $media_dir;
	

	/*
	 * CP_Import
	 *
	 * Creates a new CP_Import object.
	 *
	 * @since 1.0
	 */
	function CP_Import () {
		$this->date_format = "Y-m-d H:i:s";
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
		echo "<h2>".__('CP Import')."</h2>";
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
		echo "<p>".__('There are a few rules and guidelines, though. See the <a href="http://johnluetke.net/software/cp-import">documentation</a> for the most up-to-date information.')."</p>";
		echo "<p>".__('Here is what you will need to use this importer:')."</p>";
		echo "<ul>";
		echo "<li>".__('- A text editor (Notepad, TextEdit, TextMate, etc.)')."</li>";
		echo "<li>".__('- Microsoft Excel or other spreadsheet program that can save in .xls format')."</li>";
		echo "<li>".__('- Your archive file: Export_Story_Table_XXXX_XX-XX-XXXX.csv')."</li>";
		echo "<li>".__('- Your media file: Export_Story_Media_Table_XXX-XX-XX-XXXX.csv')."</li>";
		echo "<li>".__('- Your media folder (paperXXXX)')."</li>";
		echo "</ul>";
		
		echo "<p>".__('Note that this version of CP Import was built using export files from College Publisher 4.0, and thus is untested with export files from CP 5. If you are coming from CP 5, please proceed at your own risk. If you can get me your export files, I will try to make this compatible with CP 5.')."</p>";
		
		echo "<h3>".__('Prerequisite Actions')."</h3>";
		echo "<p>".__('The following things must be done <b><i>BEFORE</i></b> you begin using this plugin:')."</p>";
		echo "<ol>";
		echo "<li>".__('1. Upload the contents of your media folder to <b>wp_content/cp-import/</b>. Be warned: This will take a <i>long</i> time.')."</li>";
		echo "<li>".__('2. Open your archive file in a text editor. Search for an area where a line break is, highlight only the whitespace around that line break, and copy it. (See screenshot-5.png)');
		echo "<li>".__('2-1. Perform a "Search and Replace". Paste the whitespace that you copied in the "Search for" field, and leave the "Replace" field blank.');
		
		echo "<li>".__('2-2. Save the file with a new name.');
		echo "<li>".__('3. Open your archive file with Excel, and save the file as <b>.xls</b> (NOT <b>.xslx</b>)')."</li>";
		echo "<li>".__('   <i>NOTE</i>: If Excel crashes while trying to open the file, you will have to break it into smaller pieces. Use your own discretion here.')."</li>";
		echo "<li>".__('3-1. Open your media file with Excel, and also save it in <b>.xls</b> format.')."</li>";
		echo "<h3>".__('Step 4: Upload your archive file')."</h3>";
		
		wp_import_upload_form("tools.php?page=cp-import/cp-import.php&amp;step=2");
		echo "</div>";
		$this->ui_donate();
		$this->ui_footer();
	}

	/*
	 * ui_step2
	 *
	 * Grabs the uploaded archive file from ui_step1, and prompts for the user to upload their media folder to wp-content/cp-import/ and thier media file using a form
	 *
	 * @since 1.0
	 */
	function ui_step2 () {
		$this->ui_header();
		echo "<div class='narrow' style='float: left;'>";
		
		$file = wp_import_handle_upload();

		if (isset($file['file'])) {
				
			echo "<p>".__('Your archive file has been successfully uploaded!')."</p>";
			echo "<p>".__('Now, we need to upload your media file.')."</p>";
			echo "<p>".__('<b>REMEMBER</b> to upload the contents of your media <u>folder</u> (paperXXXX) to wp-content/cp-import/. The importer will look for the files referenced in your media <u>file</u> in this location. <b>Do this before uploading your media <u>file</u> below!</b>')."</p>";
			
			echo "<h3>".__('Step 5: Upload Your Media File')."</h3>";
			
			wp_import_upload_form("tools.php?page=cp-import/cp-import.php&amp;step=3&archive=".$file['file']);
		}
		else {
			echo "<p>".__('It seems that you didn\'t upload your archive file. Was it too big? If so, remember that you need to split it up into smaller chunks')."</p>";
		}

		echo "</div>";
		$this->ui_donate();
		$this->ui_footer();
	}

	/*
	 * ui_step3
	 *
	 * Grabs te uploaded media file, and checks to make sure that all required files and folders are present.
	 *
	 * @since 1.0
	 */
	 function ui_step3 () {
		$this->ui_header();
		echo "<div class='narrow' style='float: left;'>";

		$file = wp_import_handle_upload();

		if (isset($file['file'])) {

			$this->archive_file = $_GET['archive'];
			$this->media_file = $file['file'];
			$this->media_dir = WP_CONTENT_DIR."/cp-import";
				
			echo "<p>".__('Media file successfully uploaded!')."</p>";
					
			echo "<h3>".__('Step 6: Verify that everything is here')."</h3>";
					
			echo "<p>".__('Checking for required files and folders...')."</p>";
			echo "<ul><li>".__('<b>Archive file:</b> ');
			if (file_exists($this->archive_file))
				echo __(' okay!')."</li>";
			else
				wp_die('Your archive file seems to have disappeared...<br/><br/>Make sure someone didn\'t accidentally delete it or that you didn\'t modify the URL that took you to this page.');
			
			echo "<li>".__('<b>Media file:</b> ');
			if (file_exists($this->media_file))
				echo __(' okay!')."</li>";
			else
				wp_die('Your media file seems to have disappeared...');

			echo "<li>".__('<b>Media folder:</b> ');
			if (	is_dir($this->media_dir) &&
				is_dir($this->media_dir."/stills") &&
				is_dir($this->media_dir."/audio") &&
				is_dir($this->media_dir."/video")
				)
				echo __(' okay!')."</li></ul>";
			else {
				wp_die('Your media folder was not uploaded correctly. '.
				'Remember that you needed to do this manually. '.
				'You need to upload the <b>contents</b> of the <b>paperXXXX</b> folder '.
				'that CP gave you to <b>wp-content/cp-import</b>. If done correctly, the '.
				'file structure should look similar to this:<br/><br/>'.$this->media_dir.'/audio<br/>'.
				''.$this->media_dir.'/stills<br/>'.$this->media_dir.'/video<br/><br/>Once you\'ve '.
				'uploaded the media folder, refresh this page to try again.');
			}

			echo __('<p>Everything looks good! Now comes the time for the main event! Once you click the button '.
			'below, Wordpress is going to be working for a while. If you experience any errors about "timing-out" '.
			'or "too large", you\'ll need to break your archive file into smaller chunks. If that happens, you\'ll '.
			'still need to go through the motions of this importer with each of the smaller chunks, but you DO NOT '.
			'need to re-upload your media folder. If any other weird errors happen, '.
			'<a href="http://johnluetke.net/software/cp-import">see the documentation</a>. Good luck, and happy Wordpress\'ing!</p>');
			
			echo "<p><form action='tools.php?page=cp-import/cp-import&step=4&archive=".$this->archive_file."&media=".$this->media_file."' method='post'><input type='submit' class='button' value='".__('Import my College Publisher Archives!')."' /></form></p>";
			
		}
		else {
			echo "<p>".__('Your media file wasn\'t uploaded...that\'s wierd...')."</p>";
		}
		
		echo "</div>";
		$this->ui_donate();
		$this->ui_footer();
	}

	/*
	 * newXLSReader
	 *
	 * Creates an Excel reader object.
	 *
	 * @since 1.0
	 */
	function newXLSReader () {
		$reader=new Spreadsheet_Excel_Reader();
		$reader->setUTFEncoder('iconv');
		$reader->setOutputEncoding('UTF-8');
		return $reader;
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
		$first_name = substr($article['post_author'], 0, strpos($article['post_author'], " "));
		$last_name = substr($article['post_author'], strpos($article['post_author'], " ")+1, strlen($article['post_author']));
		$article['post_author'] = strtolower($first_name.".".$last_name);
	
		// Remove apostrophe's from author's name
		$article['post_author'] = $this->filter_content($article['post_author'],"'","");

		return $article;
	}

	/*
	 * get_user_id
	 *
	 * Queries the Wordpress database to see if a user with the name of the article's author exists.
	 * If not, create a new user with that name and a random password. If so, grab the existing user_id.
	 *
	 * In either case, the key 'post_author' will be replaced with the Wordpress user ID.
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
		$exists = username_exists($article['post_author']);
		echo "&nbsp;&nbsp;&nbsp;".__('Searching for author "'.$article['post_author'].'"... ');
		$name = $article['post_author'];
	
		if ($exists) {
			$article['post_author'] = $exists;
			echo __('found!')."<br/>";
			$this->set_user_info($exists, $name);
			return $article;
		}
		else {
			$id = wp_create_user ($article['post_author'], md5(time()));
			$article['post_author'] = $id;
			echo __('new acount created!')."<br />";
			$this->set_user_info($id, $name);
			return $article;
		}
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
		$data = explode(".", $name);
		foreach ($data as $d)
			$t['display_name'] .= ucfirst($d)." ";
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
			echo "&nbsp;&nbsp;&nbsp;".__('Searching for category "'.$id.'"... ');
			$new_id[] = wp_create_category($id);
			echo __('done!')."<br />";
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
	 * formate_date
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
	 * optimizeXLS
	 *
	 * Optimizes and XLS Reader object by dropping unnessary columns based on the $type specified
	 *
	 * @param string $type enum { 'attachment' }
	 * @param XLS Reader Object $xls
	 *
	 * @return XLS Reader Object 
	 */
	function optimizeXLS( $type, $xls) {
		unset($xls->sst);
		unset($xls->data);
		unset($xls->_ole);
		unset($xls->formatRecords);
		unset($xls->boundsheets);

		switch ( $type ) {
			case "attachment":

				$xls->new = array();

				foreach ($xls->sheets as $s=>$data) {
					foreach ($data['cells'] as $row) {
										
						$t['file'] = $row[2];
						$t['caption'] = $row[3];
						$t['credit'] = $row[4];

						$xls->new[$row[1]][] = $t;
					}
				}

				$xls = $xls->new;

				break;
			default:
				break;
		}

		return $xls;
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
	 */
	function get_mime($file) {
		return trim(exec('file -b --mime '.escapeshellarg($file)));
	}

	/*
	 * go
	 *
	 * The main event! This displays the steps, and processes the articles.
	 */
	function go () {
		
		// determine the step we are one
		if (empty($_GET['step']))
			$this->step = 1;
		else
			$this->step = (int) $_GET['step'];

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
				// Create 2 XLS Readers, one for the article table and one for the media table
				$r = $this->newXLSReader();
				$r->read($_GET['archive']);
				
				$a = $this->newXLSReader();
				$a->read($_GET['media']);

				// optimize the media XLS
				$a = $this->optimizeXLS('attachment', $a);

				$this->ui_header();
				// counter for article's imported
				$count = 0;
				
				// we need to loop through the sheets contained in the article XLS file.
				//
				// it is preferred that each file only have one sheet. this has not been tested with multiple
				// sheet files
				foreach ($r->sheets as $k=>$data) {

					// loops through the rows, each of which is an individual article
					foreach($data['cells'] as $row) {
	
						// Sets up an article object based on Wordpress specifications
						$article = array();
						
						$article['cp_id']			= $row[1]; // only used for media association
						$article['post_title']		= $row[5];
						$article['post_sub_title']	= $row[6];
						$article['post_content']	= $row[8];
						$article['post_author']		= $row[9];
						$article['post_category']	= array($row[4]);
						$article['post_date']		= $row[3];
						$article['post_excerpt']	= $row[7];
						
						// default values
						$article['post_type']		= "post";
						$article['post_status']         = "publish";
						$article['comment_status']	= "closed";
			
						// transform data into Wordpress formats
						$article = $this->process_date(&$article);
						$article = $this->process_author(&$article);
			
						// begin output
						echo __('Importing article: <i>').$article['post_title'].__('...<br />');
						// get the ID of this article's author
						$article = $this->get_user_id(&$article);
						// get / create the category that this article comes from
						$article = $this->get_category_id(&$article);
						echo "&nbsp;&nbsp;&nbsp;".__('Searching for media attachments...');
						// search for media associated with this article. if found, simply append the [gallery]
						// tag to the article's content
						if (isset($a[$article['cp_id']])) {
							echo sizeof($a[$article['cp_id']]).__(' found!')."<br />";
							$article['post_content'] .= "<p>[gallery]</p>";
						}
						else
							echo __('none found')."<br/>";
						
						// insert the article into the Wordpress database, and get it's new ID
						$wp_id = wp_insert_post($article);
						//echo "<pre>".print_r($article,true)."</pre>";
						
						// if this article had a subheadline, add it as a cumton field to the new post
						if ( isset($article['post_sub_title']) )
							add_post_meta($wp_id, 'subheadline', $article['post_sub_title']);
						

						//echo "<pre>".print_r($a[$article['cp_id']],true)."</pre>";
						
						// if the media XLS has media for this post, find it
						if (isset($a[$article['cp_id']])) {
						
							// there could be muliple pieces of media per article, so loop
							foreach ($a[$article['cp_id']] as $img) {
						
								$attach = array();
								$attach['post_content'] = $img['caption']."<br /><br />Credit: ".$img['credit'];
								$attach['post_title'] = "IMAGE: ".$article['post_title'];
								$attach['post_author'] = $img['credit'];
								$attach['comment_status'] = 'closed';
								$attach['post_date'] = $article['post_date'];
								$attach['post_date_gmt'] = $article['post_date'];
								
								$attach = $this->process_author(&$attach);
								$attach = $this->get_user_id(&$attach);
							
								echo "&nbsp;&nbsp;&nbsp;".__('Importing media...');
								//echo "<pre>".print_r($img,true)."</pre>";
								//echo "<pre>".print_r($attach,true)."</pre>";
						
								// make sure that the file refereneced exists
								if (!file_exists(WP_CONTENT_DIR."/cp-import".$img['file']))
									echo __('File not found: ')."<b>wp-content/cp-import".$img['file']."</b><br />";
								else {
									// determine paths where this media will be saved at
									$rel_path = date("Y") . "/" . date("m") . "/" . basename ($img['file']);	
									$dest_path = get_option('upload_path') . "/" . $rel_path;
								
									// get the mime type. this is important 
									$attach['post_mime_type'] = $this->get_mime(WP_CONTENT_DIR."/cp-import".$img['file']);
									$attach['guid'] = get_option('siteurl') ."/". $dest_path;
									
									// copy the media from wp-content/cp-import to the Wordpress media repository
									@copy (	WP_CONTENT_DIR."/cp-import".$img['file'],
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
									echo __('done!')."<br />";
								}
							}
						}

						// congratualtions!
						echo "&nbsp;&nbsp;&nbsp;".__('done!')."</i><br/>";
						$count++;
			
					}
				}
				
				echo "<p>".$count.__(' articles were successfully imported!')."</p>";
				echo "<p>".__('Congratulations! Your CP archive has been successfully imported to Wordpress! Have fun!')."</p>";
				echo "<p><a href='tools.php?page=cp-import/cp-import.php'>".__('Import again')."</a></p>";
				$this->ui_footer();
				break;
				
		} //switch
	} // function
} // class

?>
