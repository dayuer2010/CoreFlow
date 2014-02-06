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

# Date created: 2008 may 1
# Last change:  2008 may 1
#		2009 apr 16 - use List Of Coluns if not empty
#   2013 july 07 - use CR as row separator if needed

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

my $columnList=common_DB::trim(param('columnList'));

my $truncateFlag=common_DB::trim(param('previousContentManagement'));

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

# read the desired columns if needed
my @desiredColumns; # it has also the desired order
my @desiredColumnIds;
if($columnList ne ""){
  if($columnList =~ /\015/){
    @desiredColumns=split(/\015/,$columnList);
  }else{
    if($columnList =~ /\012/){
      @desiredColumns=split(/\012/,$columnList);
    }else{
      if($columnList ne ""){
        # there is one column only
        push (@desiredColumns,$columnList);
      }else{
        print  " <font style='color:red'><b>ERROR!</b></font> Couldn't find desired column separator:[crlf]  among: ".join("====",@desiredColumns)."\n<pre>====$columnList====</pre>  <br>\n";
        exit;
      }
    }
  }
  $insertNmbColumns=$#desiredColumns;
}
for(my $id=0; $id<=$#desiredColumns; $id++){
  my $desiredColumn=common_DB::trim($desiredColumns[$id]);
  my $found="no";
  for(my $i=0; $i<=$#columns; $i++){
    my $column=common_DB::trim($columns[$i]);
    if(($column eq $desiredColumn)&&($desiredColumn ne "")){
      $found="yes";
      push(@desiredColumnIds,$i);
    }
  }
  if ($found eq "no"){
    print  " <font style='color:red'><b>ERROR!</b></font> Couldn't find desired column:`$desiredColumn`  among: ".join("====",@desiredColumns)."\n  <br>\n";
    exit;
  }
}

print "<pre>\n";
print "The number of desired columns:".$insertNmbColumns."<br>\n".join(",",@desiredColumnIds)."<br>\n";
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
prepareInsert($insertNmbColumns,\@desiredColumns);
for (my $i=$firstRowId; $i<=$#contentTableLines; $i++){
	my $line=$contentTableLines[$i];
	if (common_DB::trim($line) eq  ""){next;}
	
	my @valsTsv=split(/\t/,$line);
	my @vals; # this is the final set of values according to the desired columns in the desired order
	if($columnList ne ""){
		for(my $id=0; $id<=$insertNmbColumns; $id++){
			push(@vals,$valsTsv[$desiredColumnIds[$id]]);
			#print "$id:".$desiredColumnIds[$id].":".$valsTsv[$desiredColumnIds[$id]]."\n";
		}
		#print "After push:".$#vals."\n";
	} else {
		@vals=@valsTsv;
	}

	# fill in with nuls if needed
	for (my $j=0; $j<=$insertNmbColumns; $j++){
		if (common_DB::trim($vals[$j]) eq ""){$vals[$j]=undef; }
	}
	if ($#vals<=0){
		print common_DB::trim($line)."\n";
		eval { $sth->execute(common_DB::trim($line)) ;};
	} else {
		if($i>200){print '.'; if(($i % 100)==0){print '<br>\n';} } else { print join("\t",@vals)."\n";}
		eval { $sth->execute(@vals) ;};
	}	
	if ($@) { 
		print  " <font style='color:red'><b>ERROR!</b></font> Couldn't execute query; nmb vals:$#vals  error : $DBI::errstr\n  <br>\n".join("====_",@vals)."||||<br>\n";
		exit;	
	};
 		
}
print "</pre>\n";
print '<span style="color:red">Finished!</span>';
###########################################################################################



sub prepareInsert($$){
	(my $lastColId, my $desiredColumnsPtr)=@_;
	my @desiredColumns=@$desiredColumnsPtr;
	my $holders="";
	for (my $i=0; $i<=$lastColId; $i++){
		if ($holders ne "") { $holders .= ",";}
		$holders .= "?";
	}
	
	my $colNamesList;
	if ($#desiredColumns>=0){
		$colNamesList="(`".join("`,`",@desiredColumns)."`)";
	} else {
		$colNamesList="";
	}
	my $sql = " insert into $loadTableName $colNamesList values ($holders) ";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
	print "Executing:\n".$sql."\n\n";
}

