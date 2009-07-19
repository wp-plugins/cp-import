#!/bin/perl
use strict;

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
# $URL$
# $Revision$
# $Date$
# $Author$
#

#
# The following are simple "flags" that you can set to tweak the behavior of 
# CP Import when preparing your archive file for processing by Wordpress.
#
# 1 = Yes, 0 = No.
# 

#
# Would you like this script to operate in read-only mode? This will result in
# nothing being saved to your computer.
#
my $readOnly = 0;

#
# Would you like additional information about the script's internal workings
# displayed?
#
# WARNING! When turned on, this results in over 2.5 million lines of text
# being output for roughly 11,000 articles.
#
my $debug = 0;

#
# Would you like the script to stop executing when it encounters an error?
#
my $dieOnError = 0;


#
# Please do not change anything below this point.
#
my $line = "";
my $numArticles = 0;
my $numErrors = 0;
my $inArticle = 0;
my $firstLine = 0;
my $lastLine = 0;
my $errorOccured = 0;
my $bad = "";

my $ID;
my $Priority;
my $Date;
my $Category;
my $Headline;
my $SubHeadline;
my $Summary;
my $Content;
my $Author;


print "\n";
print "\n";
print "#\n";
print "# CP Import Archive Preparation Script\n";
print "# Version 1.1\n";
print "#\n";
print "# Copyright 2009 John Luetke.\n";
print "#\n";
print "\n";
print "\n";

if (!$ARGV[0] || (!$ARGV[1] && !$readOnly)) {
	usage();
}
else {
	process();
}

sub usage () {

	print "USAGE: perl $0 <Archive File> <New File>\n";
	print "\n";
	print "WHERE:\n";
	print "     Archive File = CP Export file to read from\n";
	print "     New File  = file to save new CSV as\n";
	print "\n";
	print "\n";
	exit 0;
}

sub process () {

	#
	# Temporarily open the file to add a bogus last line.
	#
	open (CP_ARCHIVE, ">>$ARGV[0]");
	print CP_ARCHIVE "\n\"0000000\",\"";
	close (CP_ARCHIVE);

	open (CP_ARCHIVE, "$ARGV[0]") or
		die ("Unable to open $ARGV[0] for reading.\n");

	if (!$readOnly) {
		open (NEW_CP_ARCHIVE, ">$ARGV[1]") or
			die ("Unable to open $ARGV[1] for writing.\n");
	}

	while ( $line = <CP_ARCHIVE> ) {

		chomp($line);

		#
		# Remove funky newlines (^M in VIM)
		#
		$line =~ s///g;
	
		#
		# If the line is blank, skip it completely
		#
		if ( length($line) != 0) { 
	
			if ($debug) { print "  DEBUG:  \$line = $line\n"; }
	
			#
			# Remove <br> tags. Manual linebreaks are bad.
			#
			#$line =~ s/<br>/ /g
	
			if ($debug) { print "  DEBUG:  \$line = $line\n"; }
	
			if ( $line =~ /^"([0-9]{7})",/ ) {
	
				if ($debug) { print "  DEBUG:  \$line is a new article\n\n"; }
	
				#
				# "" might occur within the headline or subheadline.
				#
				$line =~ s/"""([A-Za-z0-9\s,\.':\?]*)"""/"&quot;$1&quot;"/g;
				$line =~ s/\s""([A-Za-z0-9\s,\.':\?]*)""\s/ &quot;$1&quot; /g;
				$line =~ s/"""([A-Za-z0-9\s,\.':\?]*)""\s/"&quot;$1&quot; /g;
				$line =~ s/""([A-Za-z0-9\s,\.:'\?]*)"""\s/&quot;$1&quot;" /g;
				$line =~ s/""([A-Za-z0-9\s,\.':\?]*)""","/&quot;$1&quot;","/g;
				$line =~ s/","""([A-Za-z0-9\s,\.':\?]*)""","/","&quot;$1&quot;","/g;
		
				# This line marks the start of a new article.
				# If we are currently in an article, finish processing the current
				# article
				if ($inArticle) {
	
					if ($debug) { print "  DEBUG:  End of Article reached\n\n" }
	
					#
					# Now that all the information has been extracted, we can
					# filter it and add it to the new file.
					#
					$Summary =~ s/""/&quot;/g;
					$Content =~ s/""/&quot;/g;
	
					$Summary =~ s/'/&apos;/g;
					$Content =~ s/'/&apos;/g;
	
					$Summary =~ s/,/&#44;/g;
					$Content =~ s/,/&#44;/g;
		
					$numArticles++;
	
					if (!$readOnly && !$errorOccured) {
	
						if ($debug) {
							print "  DEBUG:  \$ID			= $ID\n";
							print "  DEBUG:  \$Priority		= $Priority\n";
							print "  DEBUG:  \$Date			= $Date\n";
							print "  DEBUG:  \$Category		= $Category\n";
							print "  DEBUG:  \$Headline		= $Headline\n";
							print "  DEBUG:  \$SubHeadline	= $SubHeadline\n";
							print "  DEBUG:  \$Summary		= $Summary\n";
							print "  DEBUG:  \$Author		= $Author\n";
	
						}
	
						my $data = "\"$ID\",\"$Priority\",\"$Date\",\"$Category\",\"$Headline\",\"$SubHeadline\",\"$Summary\",\"$Content\",\"$Author\"";
	
						if ($debug) { print "  DEBUG:  Writing >>>$data<<< to file.\n\n" }
	
						print NEW_CP_ARCHIVE "\"$ID\",";
						print NEW_CP_ARCHIVE "\"$Priority\",";
						print NEW_CP_ARCHIVE "\"$Date\",";
						print NEW_CP_ARCHIVE "\"$Category\",";
						print NEW_CP_ARCHIVE "\"$Headline\",";
						print NEW_CP_ARCHIVE "\"$SubHeadline\",";
						print NEW_CP_ARCHIVE "\"$Summary\",";
						print NEW_CP_ARCHIVE "\"$Content\",";
						print NEW_CP_ARCHIVE "\"$Author\"\n" or die $!;
		
						print " Done!\n";
					} # End !$readOnly
	
					#
					# Reset all variables
					#
					$inArticle = 0;
					$firstLine = 0;
					$lastLine = 0;
					$errorOccured = 0;
	
				} # End Saving previous article.
	
				if ($debug) { print "  DEBUG:  Turning \$inArticle on.\n\n"; }
	
				$inArticle = 1;
				$firstLine = 1;
	
				#
				# Set Article variables for this iteration
				#
				#           (   ID   )   (PRIORITY)  
				$line =~ /^"([0-9]{7})","([0-9]{1,2})","/;
	
				if ($debug) {
					print "  DEBUG:  $1\n";
					print "  DEBUG:  $2\n";
				}

				#
				# An ID of 0000000 means EOF, so exit.
				#
				if ($1 == "0000000") {
					goto end;
				}

				#
				# These two values will be numeric, so a simple == "" check can be used.
				#
				if ($1 == "" || $2 == "") {
					print "+------\n";
					print "| ERROR: Unable to detect ID or Priority for ${numArticles}th article.\n";
					print "|\n";
					print "| line = $line\n";
					print "|\n";
					print "| Please correct this anomaly in your CP Archive file or contact\n";
					print "| john\@johnluetke.net to update this script.\n";			
					print "+------\n";
					if ($dieOnError) { exit 1; }
					$numErrors++;
					$errorOccured = 1;
					$numArticles--;
				}
	
				#                         (                              DATE                            )   (    CATEGORY   )   
				$line =~ /^"($1)","($2)","([A-Za-z]{3}\s[\s0-9]{2}\s[0-9]{4}\s[0-9]{2}:[0-9]{2}[AM|PM]{2})","([A-Za-z0-9\s']*)","/;
				if ($debug) {
					print "  DEBUG:  $3\n";
					print "  DEBUG:  $4\n";
				}
	
				if (length($3) == 0 || length($4) == 0) {
					print "+------\n";
					print "| ERROR: Unable to detect Date or Category for article #$1.\n";
					print "|\n";
					print "| line = $line\n";
					print "|\n";
					print "| Please correct this anomaly in your CP Archive file or contact\n";
					print "| john\@johnluetke.net to update this script.\n";	
					print "+------\n";
					if ($dieOnError) { exit 1; }
					$numErrors++;
					$errorOccured = 1;
					$numArticles--;
					$bad = $bad . $1  . ",";
				}
	
				#                                       (               HEADLINE                 )    
				$line =~ /^"($1)","($2)","($3)","($4)","([A-Za-z0-9\s\?\!\.,:;'-<>&\#\$~\(\)_@*]*)","/;
	
				if ($debug) {
					print "  DEBUG:  $5\n";
				}
	
				if (length($5) == 0) {
					print "+------\n";
					print "| ERROR: Unable to detect Headline for article #$1.\n";
					print "|\n";
					print "| line = $line\n";
					print "|\n";
					print "| Please correct this anomaly in your CP Archive file or contact\n";
					print "| john\@johnluetke.net to update this script.\n";	
					print "+------\n";
					if ($dieOnError) { exit 1; }
					$numErrors++;
					$errorOccured = 1;
					$numArticles--;
					$bad = $bad . $1 . ",";
				}
	
				#                                              (                SUBHEADLINE              )
				$line =~ /^"($1)","($2)","($3)","($4)","($5)","([A-Za-z0-9\s\?\!\.,:;\'-<>&\#\$~\(\)_@*]*)","/;
	
				if ($debug) {
					print "  DEBUG:  $6\n";
				}
	
				#
				# Subheadlines might be blank, and it's too difficult to check whether or
				# not an empty string ("") is expected when it normally means that something 
				# failed to extract.
				#
	
				#
				# It's never a good idea to use temporary variables
				#
				$ID			= $1;
				$Priority 	= $2;
				$Date		= $3;
				$Category	= $4;
				$Headline	= $5;
				$SubHeadline= $6;
		
				print "Processing Article \#$ID: \"$Headline\"..."; 
		
				#
				# Escape special regex characters.
				# This is just for the following few lines. Once everything before
				# the article summary has been remove from the line, we can undo
				# it, or else all special
				# characters will have
				# a backslash in front of them when we write to the new file.
				#
				$Headline	=~ s/([\?\.\!\(\)])/\\$1/g;
				$SubHeadline=~ s/([\?\.\!\(\)])/\\$1/g;

				#
				# Get rid of the information that we have already extracted
				#
				$line =~ s/"$ID","$Priority","$Date","$Category",//g;
				$line =~ s/"$Headline","$SubHeadline",//g;
		
				#
				# Grab the Summary and the beginning of the Content from the rest
				# of the line.
				#
				$line =~ /"(.*)","(.*)$/;
	
				#
				# Again, get info out of temp vars
				#
				$Summary 	= $1;
				$Content	= $2;
		
				#
				# Now, we need to undo the escapes we did earlier.
				#
				$Headline =~ s/\\//g;
				$SubHeadline =~ s/\\//g;
		
				$line = $Content;
				$Content = "";
	
			} # End Line matches "#######", pattern
		
			if ($inArticle) {
	
				if ($debug) { print "  DEBUG:  \$inArticle = true\n\n"; }
	
				#
				# Is this is the last line of an article?
				#
				if ( $line =~ /","([\s-,&A-Za-z]*)"$/ ) {
	
					if ($debug) { print "  DEBUG:  Detected last line of article\n\n"; }
			
					$Author = $1;
	
					$line =~ s/","$Author"$//g;
	
					$lastLine = 1;
				}
	
				#
				# If this line is not wrapped in <p> tags, do so.
				# We DO NOT want to do this if:
				# 	* this is the first or last line of the article. (Information
				# other than content).
				#	* this is a blank line
				#
				if ( $line =~ /^<p>.*<\/p>$/ ||
						length($line) == 0) {
					# Do Nothing
				}
				else {
		
					if ($debug) { print "  DEBUG:  Wrapping in <p> tags\n\n"; }
		
					$line =~ s/(.*)/<p>$1<\/p>/g;
				}
		
				if ($debug) { print "  DEBUG:  Concat'ing \"$line\" to \$Content\n\n"; }
		
				$Content = $Content . $line;
		
				if ($debug) { print "  DEBUG:  \$Content = \"$Content\"\n\n"; }
		
			} # end $inArticle
	
		} #end $line not blank
	
		if ($debug) { print "  DEBUG:  --- END OF WHILE LOOP --- \n\n"; }
	}

	end:
	
	print "\n\n+------\n";
	print "| FINISHED!\n";
	print "|\n";
	print "| Number of Articles processed: $numArticles\n";
	print "|\n";
	print "| Number of Errors: $numErrors ($bad)\n";
	print "+-------\n";

	close (CP_ARCHIVE);
	close (NEW_CP_ARCHIVE);

} # End sub process
