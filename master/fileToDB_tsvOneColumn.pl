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

# Date created: 2013 Aug 21
# Last change:  2013 Aug 21
#		Columns 2..n will be concatenated as coulmn 3
#   Column 2 value will be 1..(n-1)

$|=1;
print "Content-type: text/html;\n\n";

use strict;	# comment this line to speed up things
use common_DB;
if(!common_DB::checkViewAccess()){exit;}

open(STDERR,">&STDOUT");
use CGI qw(-debug :standard);

my $tsvFilePath=common_DB::trim(param('tsvFilePath'));

my $tsvFileName	=common_DB::trim(param('tsvFileName'));

	#ignore file names that start with a dot
	my @fileElements=split("/",$tsvFilePath);
  my $shortFileName=$fileElements[$#fileElements];
  if(substr($shortFileName,0,1) eq '.'){ $tsvFilePath='';}

if (($tsvFileName eq "") & ($tsvFilePath eq "")){
	print  " <font style='color:red'><b>ERROR!</b></font> Expecting a TSV file!  <br>\n";
	exit;
}

my $firstRowId=common_DB::trim(param('firstRowId'));

my $truncateFlag=common_DB::trim(param('previousContentManagement'));

my $valuesToSkip=common_DB::trim(param('valuesToSkip'));

my $loadTableName = common_DB::trim(param('loadTableName'));
if (($loadTableName eq "") ){
	print  " <font style='color:red'><b>ERROR!</b></font> Expecting a Table name to load ! <br>\n";
	exit;
}

  
my $size;
my $buff;
my $contentTable;
if($tsvFilePath eq ""){
  while (my $bytes_read=read($tsvFileName,$buff,2096)){
	$size += $bytes_read;
	$contentTable .=$buff;
  }
}else{
  $contentTable=`cat '$tsvFilePath' `;
}  
my @contentTableLines=split(/\n/,$contentTable);


if($#contentTableLines==0){ # we have just one row (we should use the CR row separator)
	@contentTableLines=split(/\015/,$contentTable);
}	

my $dbh=common_DB::connectToDB();



my $sth;	# !!!!!! very IMPORTANT TO BE A GLOBAL for prepareInsertContainer()

my $header=$contentTableLines[0];
my @columns=split(/\t/,$header);
my $insertNmbColumns=$#columns;

my @desiredColumnIds;


print "<pre>\n";
print "The number of columns:".$insertNmbColumns."<br>\n"."<br>\n";
if($truncateFlag ne "insert"){
	# truncate the table first
	my $sql = "truncate table $loadTableName ";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
	eval { $sth->execute() ;};
	if ($@) { 
		print  " <font style='color:red'><b>ERROR!</b></font> Couldn't execute query '$sql' ; error : $DBI::errstr\n  <br>\n";
		exit;	
	};	
}	
prepareInsert();
for(my $k=1; $k<=$insertNmbColumns;$k++){ # for each column except the first
 for (my $i=$firstRowId; $i<=$#contentTableLines; $i++){
	my $line=$contentTableLines[$i];
	if (common_DB::trim($line) eq  ""){next;}
	
	my @valsTsv=split(/\t/,$line);
	my @vals; # this is the final set of values according to the desired columns in the desired order
	@vals=($valsTsv[0],$k,$valsTsv[$k]);

	# skip nuls if needed
  if(($valuesToSkip eq 'empty_only')    && (common_DB::trim($valsTsv[$k]) eq '')) {next;} 
  if(($valuesToSkip eq 'zero_or_empty') && ((common_DB::trim($valsTsv[$k])/1) == 0 )) {next;} 
	
	for (my $j=0; $j<=2; $j++){
		if (common_DB::trim($vals[$j]) eq ""){$vals[$j]=undef; }
	}

	if(($k*$i)>200){print '.'; if(($i % 100)==0){print '<br>\n';} } else { print join("\t",@vals)."\n";}
	eval { $sth->execute(@vals) ;};

	if ($@) { 
		print  " <font style='color:red'><b>ERROR!</b></font> Couldn't execute query; nmb vals:$#vals  error : $DBI::errstr\n  <br>\n".join("====_",@vals)."||||<br>\n";
		exit;	
	};
 		
 }
} 
print "</pre>\n";
print '<span style="color:red">Finished!</span>';
###########################################################################################



sub prepareInsert($$){
	my $sql = " insert into $loadTableName  values (?,?,?) ";  ### always 3 columns
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
	print "Executing:\n".$sql."\n\n";
}

