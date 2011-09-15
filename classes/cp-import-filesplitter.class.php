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

class CP_Import_FileSplitter{

	var $_source = '';
	var $_lines = 0;
	var $_path = '';

	function configure ( $source = "", $path = "", $lines = "") {
		if ($source != "") $this->_source = $source;
		if ($path != "") $this->_path = $path;
		if ($lines != "") $this->_lines = $lines;
	}

	function run () {
		$i = 0;
		$j = 1;
		$files = array();

		$date = date("Ymd");
		$handle = @fopen ($this->_source, "r");
		$buffer = "";

		while (!feof ($handle)) {
			$buffer .= @fgets($handle);
			$i++;
			
			if ($i >= $this->_lines) {
				$fname = $this->_path . "part-".$date."-".$j.".txt";

				if (!$fhandle = @fopen($fname, 'w')) die("Cannot open file $fname");
				if (!@fwrite($fhandle, $buffer)) die("Cannot write to file $fname");
		
				$files[] = $fname;
			}

			fclose($fhandle);
			$j++;

			unset($buffer, $i);
		}

		fclose ($handle);

		return $files;
	}
}
?>
