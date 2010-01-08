<?php

#
# CP Import Archive Preparation Script
#
# This script takes your CP-provided "poorly" formatted CSV archive file 
# changes it into a properly-formatted CSV file for use with the CP Import
# Wordpress plugin.
#
# @package net.johnluetke.software.wordpress.cpimport
#
# @copyright 2009 John Luetke
#
# $URL: http://svn.wp-plugins.org/cp-import/trunk/cp-import-prepare.pl $
# $Revision: 139243 $
# $Date: 2009-07-25 12:59:06 -0500 (Sat, 25 Jul 2009) $
# $Author: johnl1479 $
#

class CP_Import_Prepare {

	#
	# The following are simple "flags" that you can set to tweak the behavior of 
	# CP Import when preparing your archive file for processing by Wordpress.
	#

	var $readonly = false;
	var $debug = false;
	var $dieOnError = true;

	var $start;
	var $end;
	
	var $line = "";
	var $numArticles = 0;
	var $numErrors = 0;       
	var $inArticle = false;
	var $firstLine = false;
	var $lastLine = false;
	var $errorOccured = false;
	var $bad = "";

	var $ID;
	var $Priority;
	var $Date;
	var $Category;
	var $Headline;       
	var $SubHeadline;	
	var $Summary;
	var $Content;
	var $Author;

	var $input;
	var $output;

	function detection_error ($what) {
		$this->end = time();
		
		if (!$this->debug) echo "<pre>";

		echo "+------<br/>";
		echo "| ERROR: Unable to detect $what for {$this->numArticles}th article.<br/>";
		echo "|<br/>";
		echo "| line = $this->line<br/>";
		echo "|<br/>";
		echo "| Please correct this anomaly in your CP Archive file or contact<br/>";
		echo "| john@johnluetke.net to update this script.<br/>";			
		echo "+------<br/>";
		
		if ($this->dieOnError) { echo $this->end - $this->start . " seconds."; die(); }
		
		$this->numErrors++;
		$this->errorOccured = true;
		$this->numArticles--;

		if (!$this->debug) echo "</pre>";
	}

	function debug($msg) {
		if ($this->debug)
			echo "  <b>DEBUG:</b>  ".$msg."<br/><br/>";
	}

	function process ($in, $out) {
		$this->start = time();
		$this->debug = isset($_GET['debug']);
		if ($this->debug) { echo "<pre>"; }

		if (!file_exists($in))
			die ($in . " does not exist.");

		#
		# Temporarily open the file to add a bogus last line.
		#
		$t = fopen ($in, "a");
		fwrite($t, "\n\"0000000\",\"0\",\"");
		fclose ($t);

		if (!($this->input = fopen($in, "r")))			
			die ("Could not open $in for reading.");
		if (!($this->output = fopen($out, "w")))
		    die ("Could not open $out for writing.");
		
		while ( $this->line = fgets($this->input) ) {

			$this->debug("Loop start");
			
			$this->line = trim($this->line);
						
			// Remove any newlines fro the current line
			$this->line = preg_replace("//", "", $this->line);
			$this->line = preg_replace("/\n/", "", $this->line);

			// Temporary array for holding regex matches.
			$matches = array();

			$this->debug("\$line = $this->line");

			// If the beginning of this line begins with a double quote and the a number, we can
			// assume that it indicates a new article.
			if ( preg_match("/^\"([0-9]+)\",\"/", $this->line) ) {

				$this->debug("\$line is a new article");
				
				// Replace "" with &quot;
				$this->line = preg_replace("/\s\"\"([A-Za-z0-9\s,\.':\?]*)\"\"\s/", " &quot;$1&quot; ", $this->line);
				$this->line = preg_replace("/\"\"\"([A-Za-z0-9\s,\.':\?]*)\"\"\s/", "\" &quot;$1&quot; ", $this->line);
				$this->line = preg_replace("/\"\"([A-Za-z0-9\s,\.:'\?]*)\"\"\"\s/", " &quot;$1&quot; ", $this->line);
				$this->line = preg_replace("/\"\"([A-Za-z0-9\s,\.':\?]*)\"\"\",\"/"," &quot;$1&quot; \",\"", $this->line);
				$this->line = preg_replace("/\",\"\"\"([A-Za-z0-9\s,\.':\?]*)\"\",\"/","\",\" &quot;$1&quot; \",\"", $this->line);

				// Since we have encoutered a new article, we must save out the current one before
				// we begin to process
				if ($this->inArticle) {
					// We've reached the end of the current article

					$this->debug("End of Article reached");

					$this->Summary = preg_replace( "/\"\"/", "&quot;", $this->Summary);
					$this->Content = preg_replace( "/\"\"/", "&quot;", $this->Content);
	
					$this->Summary = preg_replace( "/'/", "&apos;", $this->Summary);
					$this->Content = preg_replace( "/'/", "&apos;", $this->Content);
	
					$this->Summary = preg_replace( "/,/", "&#44;", $this->Summary);
					$this->Content = preg_replace( "/,/", "&#44;", $this->Content);

					$this->debug("\$ID = $this->ID");
					$this->debug("\$Priority = $this->Priority");
					$this->debug("\$Date = $this->Date");
					$this->debug("\$Category = $this->Category");
					$this->debug("\$Headline = $this->Headline");
					$this->debug("\$SubHeadline = $this->SubHeadline");
					$this->debug("\$Summary	= $this->Summary");
					$this->debug("\$Author = $this->Author");

					if (!$this->readonly && !$this->errorOccurred) {
						
						if (!is_writable($out)) { die(" <b>ERROR</b>:  File $out is not writable."); }

						if ($this->debug) { echo "  <b>DEBUG</b>:  Writing article to file...<br/><br/>"; }

						$this->data = "\"$this->ID\",\"$this->Priority\",\"$this->Date\",\"$this->Category\",\"$this->Headline\",\"$this->SubHeadline\",\"$this->Summary\",\"$this->Content\",\"$this->Author\"";

						if(!fwrite($this->output, $data)) {
							$cmd = "echo ".escapeshellarg($this->data)." >> ".$out;
							//echo $cmd;
							exec($cmd, $idontexist, $status);
							if ($status != 0)
								die(" <b>ERROR</b>:  Could not write to file $out.");
						}
					}

					$this->firstLine = false;
					$this->lastLine = false;
					$this->errorOccured = false;
					$this->inArticle = false;
					echo " done!<br/>\n";
				}

				$this->debug("Turning \$inArticle on.");
				$this->numArticles++;

				$this->inArticle = true;
				$this->firstLine = true;

				preg_match ("/^\"([0-9]{1,11})\",\"([0-9]{1,2})\",/", $this->line, $matches);
				$this->ID = $matches[1];
				$this->Priority = $matches[2];
					
				$this->debug($this->ID);
				$this->debug($this->Priority);

				if ($this->ID == "0000000") break;

				if ($this->ID == "" || $this->Priority == "") $this->detection_error("ID or Priority");

				preg_match ("/^\"(".$this->ID.")\",\"(".$this->Priority.")\",\"([A-Za-z]{3}\s[\s0-9]{2}\s[0-9]{4}\s[0-9]{2}:[0-9]{2}[AM|PM]{2})\",\"([A-Za-z0-9\s']*)\",\"/", $this->line, $matches);
				$this->Date = $matches[3];
				$this->Category = $matches[4];

				$this->debug($this->Date);
				$this->debug($this->Category);

				if ($this->Date == "" || $this->Category == "")  $this->detection_error("Date or Category");

				preg_match ("/^\"(".$this->ID.")\",\"(".$this->Priority.")\",\"(".$this->Date.")\",\"(".$this->Category.")\",\"([A-Za-z0-9 \s\?\!\.,:;\'-<>&\#\$~\(\)_@*%=]*)\",\"([A-Za-z0-9\s\?\!\.,:;\'-<>&\#\$~\(\)_@*\"%=]*)\",/", $this->line, $matches);
				$this->Headline = $matches[5];
				$this->SubHeadline = $matches[6];

				$this->debug($this->Headline);
				$this->debug($this->SubHeadline);

				if ($this->Headline == "")  $this->detection_error("Headline or SubHeadline");

				// Escape special Regex characters
				$this->Headline = preg_replace("/([\?\.\!\(\)\/])/", "\\\\$1", $this->Headline);
				$this->SubHeadline = preg_replace("/([\?\.\!\(\)\/])/", "\\\\$1", $this->SubHeadline);

				// Grab the Summary and Content from the rest of the line
				preg_match ("/^\"(".$this->ID.")\",\"(".$this->Priority.")\",\"(".$this->Date.")\",\"(".$this->Category.")\",\"(".$this->Headline.")\",\"(".$this->SubHeadline.")\",\"(.*)\",\"(.*)$/", $this->line, $matches);

				$this->Summary = $matches[7];
				$this->Content = $matches[8];

				// Undo the escaping done earlier
				$this->Headline = preg_replace("/\\\\/", "", $this->Headline);
				$this->SubHeadline = preg_replace("/\\\\/", "", $this->SubHeadline);

				// I've observed that some headlines have linebreaks in them, so get rid of those.
				$this->Headline = preg_replace("/<br>/", "", $this->Headline);

				echo "Processing Article #".$this->numArticles." (CP Article #".$this->ID."): \"$this->Headline\"...";
			}

			// The current line does not start with a number.
			if ($this->inArticle && !$this->firstLine) {

				$this->debug("\$inArticle = true");

				// is this the last line of the article?
				if ( preg_match ("/\",\"([\s-,&A-Za-z]*)\"$/", $this->line, $matches) ) {
					$this->Author = $matches[1];
					
					$this->line = preg_replace("/\",\"".$this->Author."\"$/", "", $this->line);

					$this->lastLine = true;

				}

				if ( preg_match("/<p>.*<\/p>$/", $this->line ) || strlen($this->line) == 0 ) {
					// Do Nothing
				}
				else {
					$this->line = preg_replace ( "/(.*)/", "<p>$1<\/p>", $this->line);
				}

				$this->Content .= $this->line;

			}

			$this->firstLine = false;
		}

		$this->end = time();

		echo "Finished! ".$this->numArticles." articles were properly formatted into a CSV file for use with CP Import.<br/>";
		echo "It took ".(($this->end - $this->start)/60)." minutes to complete.<br/>";

		echo "</pre>";
	}
}

function stripquotes($value) {
		
	$value = trim($value);

	$f = substr($value, 0, 1);
	$l = substr($value, -1, 1);

	if ($f == $l && $f == '"') {
		$value = substr($value, 1, -1);
	}

	return $value;
}


?>
