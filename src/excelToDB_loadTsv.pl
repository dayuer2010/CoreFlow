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


# Load a table with data from a  tsv file

# Date created: 2010 aug 16
# Last change:  2010 aug 16
#		2010 aug 16 - use tab delimited content from Excel

$|=1;
print "Content-type: text/html;\n\n";

use strict;	# comment this line to speed up things
open(STDERR,">&STDOUT");
use CGI qw(-debug :standard);
if(!common_DB::checkViewAccess()){exit;}

my $truncateTable=common_DB::trim(param("truncateTable"));

my $contentTable =common_DB::trim(param('tableContent'));
if ($contentTable eq ""){
	print  " <font style='color:red'><b>ERROR!</b></font> Expecting non empty content of table!  <br>\n";
	exit;
}

my $firstRowId=common_DB::trim(param('firstRowId'));


my $loadTableName = common_DB::trim(param('loadTableName'));
if ($loadTableName eq ""){
	print  " <font style='color:red'><b>ERROR!</b></font> Expecting a Table name to load ! <br>\n";
	exit;
}

my @contentTableLines=split(/\n/,$contentTable);

use common_DB;
my $dbh=common_DB::connectToDB();


my $sth;	# !!!!!! very IMPORTANT TO BE A GLOBAL for prepareInsertContainer()
my $separator="\t";
#$separator=" ";
my $header=$contentTableLines[0];
my @columns=split($separator,$header);
my $insertNmbColumns=$#columns;

prepareInsert($insertNmbColumns);

print "<pre>\n";
for (my $i=$firstRowId; $i<=$#contentTableLines; $i++){
	my $line=$contentTableLines[$i];
	if (common_DB::trim($line) eq  ""){next;}

	$line =~ s/\x0a|\x0d|\x0c//g;
	
	my @valsTsv=split($separator,$line);
	my @vals; # this is the final set of values according to the desired columns in the desired order
	@vals=@valsTsv;

	# fill in with nuls if needed
	for (my $i=0; $i<=$insertNmbColumns; $i++){
		if (common_DB::trim($vals[$i]) eq ""){$vals[$i]=undef; }
	}
	if ($#vals<=0){
		print common_DB::trim($line)."\n";
		eval { $sth->execute(common_DB::trim($line)) ;};
	} else {
		print join("|",@vals)."\n";
		eval { $sth->execute(@vals) ;};
	}	
	if ($@) { 
		print  " <font style='color:red'><b>ERROR!</b></font> Couldn't execute query; nmb vals:".($#vals+1)."  error : $DBI::errstr\n  <br>\n".join("====_",@vals)."||||<br>
Nmb of values:".($#vals+1)."\n";
		exit;	
	};
 		
}
print "</pre>\n";
print '<span style="color:red">Finished!</span>';
###########################################################################################


sub prepareInsert($){
	(my $lastColId)=@_;
	my $holders="";
	for (my $i=0; $i<=$lastColId; $i++){
		if ($holders ne "") { $holders .= ",";}
		$holders .= "?";
	}

	if ($truncateTable eq "Yes"){
		my $sql = " truncate table $loadTableName  ";
		eval { $sth= $dbh->prepare($sql); };
		if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
		eval { $sth->execute() ;};
		if ($@) { 
			print  " <font style='color:red'><b>ERROR!</b></font> Couldn't execute query!<br>  error : $DBI::errstr\n  <br>\n";
		}
	}
	
	my $sql = " insert into $loadTableName  values ($holders) ";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
	print "Executing:\n".$sql."\n\n";
}



