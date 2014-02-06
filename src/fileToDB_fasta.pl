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

$|=1;
print "Content-type: text/html;\n\n";

use strict;	# comment this line to speed up things
use common_DB;
if(!common_DB::checkViewAccess()){exit;}

open(STDERR,">&STDOUT");
use CGI qw(-debug :standard);

my $fileName	=common_DB::trim(param('fileName'));
if ($fileName eq ""){
	print  " <font style='color:red'><b>ERROR!</b></font> Expecting a FASTA file!  <br>\n";
	exit;
}


my $truncateFlag=common_DB::trim(param('previousContentManagement'));
my $firstRowId=common_DB::trim(param('firstRowId'))/1;


my $loadTableName = common_DB::trim(param('loadTableName'));
if ($loadTableName eq ""){
	print  " <font style='color:red'><b>ERROR!</b></font> Expecting a Table name to load ! <br>\n";
	exit;
}

my $size;
my $buff;
my $contentTable;
while (my $bytes_read=read($fileName,$buff,2096)){
	$size += $bytes_read;
	$contentTable .=$buff;
}
my @contentTableLines=split(/\n/,$contentTable);


my $dbh=common_DB::connectToDB();



my $sth;	# !!!!!! very IMPORTANT TO BE A GLOBAL for prepareInsertContainer()

### obtain the number of columns from the description of the table
# separate the database from table name
(my $dbName, my $tableName)=split('\.',$loadTableName);
  my $sql = "SELECT COUNT(*) as Nmb_Cols FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '$dbName' AND table_name='$tableName'";
  eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
	eval { $sth->execute() ;};
	if ($@) { 
		print  " <font style='color:red'><b>ERROR!</b></font> Couldn't execute query '$sql' ; error : $DBI::errstr\n  <br>\n";
		exit;	
	};
	my $row_ref = $sth->fetchrow_hashref();	
  my $insertNmbColumns=$row_ref->{Nmb_Cols};


#######  parse the fasta file and make one sequence only 
my @allLines=split('\n',$contentTable);
my @tsvContent;
my $crtContent="";
my $crtHeader="";
foreach my $line(@allLines){
	if($line =~ /^>/) {
		if($crtContent ne ""){ push @tsvContent,$crtHeader."\t".$crtContent}
		# replace tabs with spaces
    $line =~ s/\t/ /g;		
		$crtHeader=$line; $crtContent="";
	}else{
	  #get rid of * and \n \r 
	  $line =~ s/\*//g;
	  $line =~ s/\n//g;
	  $line =~ s/\r//g;
		$crtContent.=$line;
	}
}
if($crtContent ne "") { push @tsvContent,$crtHeader."\t".$crtContent}

#######
print "<pre>\n";
print "The number of target table columns:".$insertNmbColumns."<br>\n";
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
prepareInsert($insertNmbColumns);
my $i=0;
foreach my $line(@tsvContent){
	$i++;
	if (common_DB::trim($line) eq  ""){next;}
	
	my @valsTsv=split(/\t/,$line);
	my @vals; # this is the final set of values according to the desired columns in the desired order
		@vals=@valsTsv;

	# fill in with nuls if needed
	for (my $j=0; $j<$insertNmbColumns; $j++){
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



sub prepareInsert($){
	(my $lastColId)=@_;
	my $holders="";
	for (my $i=0; $i<$lastColId; $i++){
		if ($holders ne "") { $holders .= ",";}
		$holders .= "?";
	}
	
	my $sql = " insert into $loadTableName  values ($holders) ";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "<font style='color:red'><b>ERROR!</b></font> Couldn't prepare query '$sql': $DBI::errstr\n"; exit; };
	print "Executing:\n".$sql."\n\n";
}

