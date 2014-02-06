#!/usr/bin/env perl

#**
#
# Copyright (c) 2012-2013 Mount Sinai Hospital, Toronto, Ontario, 
# Copyright (c) 2012-2013 DTU/CSIG Linding Lab
#
# LICENSE:
#
# This is free software; you can redistribute it
# and/or modify it under the terms of the GNU General
# Public License as published by the Free Software Foundation;
# either version 3 of the License, or (at your option) any
# later version.
#
# This software is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
# General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with the source code.  If not, see <http://www.gnu.org/licenses/>.
#
#
#**


# uploads icon files and updates MB_ICON table
# author: Adrian Pasculescu  pawsonlab.mshri.on.ca 
# Date created: 2010 Mar 24
# Last change: 2012 Nov 26    

use strict;	# comment this line to speed up things
print "Content-type: text/html\n\n";

use common_DB;
if(!common_DB::checkViewAccess()){exit;}

my $dbh;


use CGI qw(-debug :standard);
$dbh=common_DB::connectToDB();
my $sth;

my $localDestination=common_DB::getConfigValue('//icons_location');
if (substr($localDestination,length($localDestination)-1) ne "/" ) {$localDestination.="/";} # usual error (forget to end with a /)
my $webPath='./images/Icons/';

my $tableName=param("tableName");
if ($tableName eq "") {$tableName="MB_ICON"}

my $iconFileOriginal=param("iconFile");
my $iconFile=common_DB::trimQuotes(common_DB::trim($iconFileOriginal));
my $userComments=common_DB::trimComments(param("userComments"));
if (common_DB::trim($iconFile) ne ""){
	# read the new icon and insert it in the MB_ICON table or the MB_ANALYSIS_AUTHOR
	my $iconFileLocal=$localDestination.$iconFile;
	if (-e $iconFileLocal){
		print '<span style="color:red">File: <b>'.$iconFileLocal.'</b> already stored in folder</span>';
	} else {
		my $size;
		my $buff;
		my $contentIcon;
		while (my $bytes_read=read($iconFileOriginal,$buff,2096)){
			$size += $bytes_read;
			$contentIcon .=$buff;
		}
		if($tableName eq "MB_ICON"){
			my $sql="select File_Name from MB_ICON where File_Name='$iconFile'";
			eval { $sth= $dbh->prepare($sql); };
			if ($@) { print "Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
			eval { $sth->execute() ;};
			if ($@) { print "Couldn't execute query '$sql': $DBI::errstr\n"; exit; };
			if ($sth->fetchrow_array()){
				print '<span style="color:red">File: <b>'.$iconFile.'</b> already stored in Table</span>';
			} else {
				# store in both local folder and database table
				if (open(ICON,">",$iconFileLocal)){
					print ICON $contentIcon;
					close(ICON);
					my $sql="insert into MB_ICON(File_Name,Web_Path,Storage_Path,`Comments`) values('$iconFile','$webPath','$localDestination','$userComments')";
					eval { $sth= $dbh->prepare($sql); };
					if ($@) { print "Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
					eval { $sth->execute() ;};
					if ($@) { print "Couldn't execute query '$sql': $DBI::errstr\n"; exit; };
				} else {
					print '<span style="color:red">Could not save: <b>'.$iconFile,'</b> size:'.$size.'<br> into: <b>'.$iconFileLocal.'</b> !
					<br> Maybe forgot to set write permissions!</span>';
				}
	
			}
		} else {

			my $sql="select Picture_Path from MB_ANALYSIS_AUTHOR where Picture_Path='$iconFile'";
			eval { $sth= $dbh->prepare($sql); };
			if ($@) { print "Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
			eval { $sth->execute() ;};
			if ($@) { print "Couldn't execute query '$sql': $DBI::errstr\n"; exit; };
			if ($sth->fetchrow_array()){
				print '<span style="color:red">File: <b>'.$iconFile.'</b> already stored in Table</span>';
			} else {
				# store in both local folder and database table
				if (open(PICTURE,">",$iconFileLocal)){
					print PICTURE $contentIcon;
					close(PICTURE);
					my $sql="insert into MB_ANALYSIS_AUTHOR(Analysis_Author,Picture_Path) values('$userComments','$iconFile')";
					eval { $sth= $dbh->prepare($sql); };
					if ($@) { print "Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
					eval { $sth->execute() ;};
					if ($@) { print "Couldn't execute query '$sql': $DBI::errstr\n"; exit; };
				} else {
					print '<span style="color:red">Could not save: <b>'.$iconFile,'</b> size:'.$size.'<br> into: <b>'.$iconFileLocal.'</b> !
					<br> Maybe forgot to set write permissions!</span>';
				}
      }
		}
	}
} else {
	# probably a delete action
	my $iconAction=common_DB::trim(param("action"));
	my $iconId    =common_DB::trim(param("Icon_Id"));
  if($iconAction eq 'delete'){
		if($tableName eq "MB_ICON"){
			my $sql="select File_Name from MB_ICON where Icon_Id='$iconId'";
			eval { $sth= $dbh->prepare($sql); };
			if ($@) { print "Content-type: text/html\n\n"."Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
			eval { $sth->execute() ;};
			if ($@) { print "Content-type: text/html\n\n"."Couldn't execute query '$sql': $DBI::errstr\n"; exit; };
			if (my @row_array=$sth->fetchrow_array()){
				my $iconFile=$row_array[0];
				my $iconFileLocal=$localDestination.$iconFile;
				`rm $iconFileLocal`;
				my $sql="delete from MB_ICON where Icon_Id=$iconId";
				eval { $sth= $dbh->prepare($sql); };
				if ($@) { print "Content-type: text/html\n\n"."Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
				eval { $sth->execute() ;};
				if ($@) { print "Content-type: text/html\n\n"."Couldn't execute query '$sql': $DBI::errstr\n"; exit; };			
			}
		} else {

			my $sql="select Picture_Path from MB_ANALYSIS_AUTHOR where Analysis_Author='$iconId'";
			eval { $sth= $dbh->prepare($sql); };
			if ($@) { print "Content-type: text/html\n\n"."Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
			eval { $sth->execute() ;};
			if ($@) { print "Content-type: text/html\n\n"."Couldn't execute query '$sql': $DBI::errstr\n"; exit; };
			if (my @row_array=$sth->fetchrow_array()){
				my $iconFile=$row_array[0];
				my $iconFileLocal=$localDestination.$iconFile;
				`rm $iconFileLocal`;
				my $sql="delete from MB_ANALYSIS_AUTHOR where Analysis_Author='$iconId'";
				eval { $sth= $dbh->prepare($sql); };
				if ($@) { print "Content-type: text/html\n\n"."Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
				eval { $sth->execute() ;};
				if ($@) { print "Content-type: text/html\n\n"."Couldn't execute query '$sql': $DBI::errstr\n"; exit; };			
			}
		}
  }
}	



my $sql="select Icon_Id, File_Name, Web_Path,`Comments` from MB_ICON";
eval { $sth= $dbh->prepare($sql); };
if ($@) { print "Content-type: text/html\n\n"."Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
eval { $sth->execute() ;};
if ($@) { print "Content-type: text/html\n\n"."Couldn't execute query '$sql': $DBI::errstr\n"; exit; };


my $htmlContent='<tr><th>Icon_Id</th> <th>File Name</th> <th>Web_Path</th> <th>Comments</th><th></th></tr>';
while (my @row_array = $sth->fetchrow_array()) {
	my $rowContent .= '<td>'.$row_array[0].'</td>'
	                 .'<td>'.$row_array[1].'</td>'
	                 .'<td><img alt="" src="'.$row_array[2].$row_array[1].'" width="20"></td>'
	                 .'<td><span style="color:#BBBBBB">'.$row_array[3].'</span></td>'
	                 .'<td><a href="?tableName=MB_ICON&amp;action=delete&amp;Icon_Id='.$row_array[0].'" onclick="return confirm(\'Delete icon '.$row_array[0].' and its file?\')"><img alt="" src="images/trash.png" title="delete this icon and its file"></a></td>';
	$htmlContent .= '<tr>'.$rowContent.'</tr>'."\n";
}

####### same for the Author
my $sql="select Analysis_Author, Picture_Path from MB_ANALYSIS_AUTHOR";
eval { $sth= $dbh->prepare($sql); };
if ($@) { print "Content-type: text/html\n\n"."Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
eval { $sth->execute() ;};
if ($@) { print "Content-type: text/html\n\n"."Couldn't execute query '$sql': $DBI::errstr\n"; exit; };


my $htmlContentAuthor='<tr><th>Author</th> <th>Picture</th> <th></th></tr>';
while (my @row_array = $sth->fetchrow_array()) {
	my $rowContent .= '<td>'.$row_array[0].'</td>'
	                 .'<td><img alt="" width="20" src="images/Icons/'.$row_array[1].'"></td>'
	                 .'<td><a href="?tableName=MB_ANALYSIS_AUTHOR&amp;action=delete&amp;Icon_Id='.$row_array[0].'" onclick="return confirm(\'Delete author '.$row_array[0].' and its picture?\')"><img alt="" src="images/trash.png" title="delete this author and picture"></a></td>';
	$htmlContentAuthor .= '<tr>'.$rowContent.'</tr>'."\n";
}


$htmlContent = `php app_header.php`.'
<script  type="text/javascript">
	highLiteMenu("manageIconsMenu");
</script>	    	

<table align="center">
  <tr>
   <td>
    <br>
    <div align="center"><span style="text-align:center; font-size:120%; font-weight:bold">Manage</span><form action="" style="display:inline"><select name="tableName" onchange="submit()"><option value="MB_ICON" '.(($tableName eq "MB_ICON")?'selected':'').'>Task Icons</option><option value="MB_ANALYSIS_AUTHOR"'.(($tableName eq "MB_ANALYSIS_AUTHOR")?'selected':'').'>Author Pictures</option></select></form></div>
    <br>One <a href="http://www.iconfinder.net/search/?q=important" target="_blank">source of icons</a> 
    or <a href="http://www.iconfinder.com/search/?q=iconset%3AUltimateGnome" target="_blank">other source</a>.
    <br><form ENCTYPE="multipart/form-data" method="post" action="">
        <input type="hidden" name="action" value="insert">
        <input type="hidden" name="tableName" value="'.$tableName.'">
        <b>New</b>'.(($tableName eq "MB_ICON")?' Icon description/name':'Author full name').':<input size="50" name="userComments">
        <br><b>New</b>'.(($tableName eq "MB_ICON")?' icon':' picture').' file:<input type="file" name="iconFile" size="40">
        <input type="submit" value="Upload ...."></form>
        <br>
    <br>
   </td> 
  </tr>
  <tr>
    <td>
      <span style="color:blue">Already stored:</span><br>   
      <table style="border:1">'.(($tableName eq "MB_ICON")?$htmlContent:$htmlContentAuthor).'</table>
    </td>  
  </tr>  
    
</table>    
'.`php app_footer.php`;

print $htmlContent;
exit;

