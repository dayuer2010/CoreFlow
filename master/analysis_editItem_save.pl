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


# Updates/Inserts/Deletes a record from a table
# Date created: 2007 Feb 21
# Last change:  2007 Feb 22 - empty conditions on Update transformed in ORs with Null
# 		2007 Mar 20 - on finish go back to edit table and show record
# 		2008 jul 25 - allow ' for update/insert columns/fields
#				if there is a primary key then use those columns for where clause !
#		2008 Aug 26 - adding the set of timestamp to now() at insert
#		2008 Oct 31 - going back to the referrer

use strict;	# comment this line to speed up things

print "Content-type: text/html\n\n";

use common_DB;
my $dbh=common_DB::connectToDB();



use CGI qw(-debug :standard);


if(!common_DB::checkViewAccess()){exit;}

my $dbh=common_DB::connectToDB();

my $sql;

my @allParams=param(); #print join(':',@allParams);
my $cmd			=common_DB::trim(param("__command"));
my $autoIncColumn	=common_DB::trim(param("__autoIncrementColumn"));
my $tableName		=common_DB::trim(param("__tableName"));
my $changedColumns	=common_DB::trim(param("__changedColumns"));
my $timestampColumn	=common_DB::trim(param("__timestampColumn"));
my $timeCreatedColumn	=common_DB::trim(param("__timeCreatedColumn"));

my $refererUrl=$ENV{HTTP_REFERER};

my $recordId;


my $sql;
if (($cmd ne "delete") && ($cmd ne "insert") && ($cmd ne "update")){
	print "Undefined command: $cmd (only insert/delete/update accepted)!";
	exit;
}

# get all columns of the table and their types
$sql="show full columns from $tableName";
my %columnNameToQuote;
my %columnNameToKey;
my $sth;
my $primaryKeysExist=0;
eval { $sth= $dbh->prepare($sql); };
if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
eval { $sth->execute() ;};
if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
while (my $row_ref = $sth->fetchrow_hashref()) {
	my $colName=$row_ref->{Field};
	my $colType=$row_ref->{Type};
	my $colKey =$row_ref->{Key};
	if ($colKey =~ /PRI/){
		$columnNameToKey{$colName}=$colKey;
		$primaryKeysExist=1;
	}
	if ($colType =~ /varchar/){
		$columnNameToQuote{$colName}="`";
	} else {
		$columnNameToQuote{$colName}="'";
	}
}



if(!common_DB::checkWriteExecuteAccess()){
	print '<span >Note that you do not have privileges to change database content! Please contact authors if needed.</span><br>'."\n";
}

########### continue processing 
if ($cmd eq "delete"){
	$sql = ' delete from '.$tableName.' where 1=1
	';
	my $sqlWhere;
	my @valArray;
	foreach my $par (@allParams){
		if (!(common_DB::trim($par) =~ /^__/)){
			my $colName	=$par;
			my $colValue	=param($par);
				if ($colName eq $autoIncColumn){
						$sqlWhere .= "and `$colName`=? \n";
						push @valArray,$colValue;
				}	
		}
	}

	# first verify if more than on record will be deleted
	my $sqlCheck = 'select count(*) as Counts from '.$tableName.' where 1=1 ' .$sqlWhere;
	if(getHowMany($sqlCheck,\@valArray)>1){
		print '	<head></head><body><span style="color:red">More than 1 records will be affected! Aborting.</span>
			<!-- 
			changed Columns list: '.$changedColumns.'
			'.$sqlCheck.'
			
			-->
			</body>
			';
		exit;
	}
	$sql .= $sqlWhere;
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute(@valArray) ;};
	if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };

 ##  cascade delete in associate tables
 $sql="delete from MB_CUSTOM_ANALYSIS_RESULT where 1=1 ".$sqlWhere;
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare cascade query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute(@valArray) ;};
	if ($@) { print "Couldn't execute cascade query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };

}

if ($cmd eq "insert"){
	# verify if we get duplicates !
	my $sqlWhere;
	my @valArray;
	foreach my $par (@allParams){
		if (!(common_DB::trim($par) =~ /^__/)){
			my $colName	=$par;
			my $colValue	=param($par);
			if ($colValue eq ""){
				$sqlWhere .= "and ((`$colName`='') or (`$colName` is null))   \n";
			} else {
				$sqlWhere .= "and `$colName`=? \n";
				push @valArray,$colValue;
			}	
		}
	}
	my $sqlCheck = 'select count(*) as Counts from '.$tableName.' where 1=1 ' .$sqlWhere;
	if(getHowMany($sqlCheck,\@valArray)>=1){
		print '	<head></head><body><span style="color:red">duplicate records will result! Aborting.</span>
			<!-- '.$sqlCheck.'-->
			</body>
			';
		exit;
	}
	


	$sql = ' insert into '.$tableName.'
	';
	my $colList;
	my $valList;
	my @valArray;
	foreach my $par (@allParams){
		if (!(common_DB::trim($par) =~ /^__/)){
			my $colName	=common_DB::trim($par);
			if ($colName ne $autoIncColumn) {
				if ($colList ne "") {$colList .= "\n,";}
				$colList .="`$colName`";

					my $colValue	=param($par);
					if ($valList ne "") {$valList .= "\n,";}
					$valList .= "?";
					push @valArray,$colValue;
			}
		}
	}

  if($timestampColumn ne ""){
				if ($colList ne "") {$colList .= "\n,";}
				$colList .="`$timestampColumn`";
					if ($valList ne "") {$valList .= "\n,";}
					$valList .= "now()";
  }

  if($timeCreatedColumn ne ""){
				if ($colList ne "") {$colList .= "\n,";}
				$colList .="`$timeCreatedColumn`";
					if ($valList ne "") {$valList .= "\n,";}
					$valList .= "now()";
  }

  
	$sql .= "(\n $colList)\n values(\n $valList) \n";
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute(@valArray) ;};
	if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n (".join('<br>',@valArray).")</pre>"; exit; };
	$recordId=getLastDBinsertId();	
}


if ($cmd eq "update"){
	$sql = ' update '.$tableName.'
	';
	my $whereClause=" 1=1 ";
	my $setClause;
	my @allChangedColumns=split(/,/,$changedColumns);
	my %hashChangedColumns;
	for (my $j=0; $j<=$#allChangedColumns; $j++){
		$hashChangedColumns{common_DB::trim($allChangedColumns[$j])}=1;
	}
	my @valArray;
	#get the "set clause" first 
	foreach my $par (@allParams){
		if (!(common_DB::trim($par) =~ /^__/)){
			my $colName	=common_DB::trim($par);
			my $colValue	=param($par);
			if (exists $hashChangedColumns{$colName}){
				if ($setClause ne ""){ $setClause .= ", ";}
				$setClause .= " `$colName`=? \n";
				push @valArray,$colValue;
			}
		}
	}
  if($timestampColumn ne ""){
  	if ($setClause ne ""){ $setClause .= ", ";}
  	$setClause .= " `$timestampColumn`=now() \n";
  }
	#get the "where clause" now (use primary keys if we have)
	foreach my $par (@allParams){
		if (!(common_DB::trim($par) =~ /^__/)){
			my $colName	=common_DB::trim($par);
			my $colValue	=param($par);
			  if ($colName eq $autoIncColumn){
						$whereClause .= "and `$colName`=$colValue \n";
				}	
		}
	}


	# first verify if more than on record will be deleted
	my $sqlCheck = 'select count(*) as Counts from '.$tableName.' where '.$whereClause;
	if(getHowMany($sqlCheck)>1){
		print '	<head></head><body><span style="color:red">More than 1 records will be affected! Aborting ('.$tableName.' - '.$whereClause.').</span>
			<!--  
			changed Columns list: '.$changedColumns.'
			
			'.$sqlCheck.'-->
			</body>
			';
		exit;
	}


	$sql .= " set $setClause \n";
	$sql .= " where $whereClause \n";
	#print "cmd=$cmd <br><pre>".$sql."\n $changedColumns</pre>\n"; exit;
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute(@valArray) ;};
	if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
}



print '	<head>
	</head>
	<body>
	<!--
	Done!           <a href="'.$refererUrl.'">verify</a>
	<pre>
	'.$sql.'
	</pre>
	-->
	<script>
		location.href="'.$refererUrl.'&afterEditRecord=Yes";
	</script>
	</body>
	';
exit;
##################################################################




sub getLastDBinsertId(){
        my $sth;
        eval { $sth= $dbh->prepare('select LAST_INSERT_ID() as Last_Id'); };
        eval { $sth->execute() ;};
        if ($@) { print "Couldn't execute LAST_INSERT_ID() : $DBI::errstr\n"; exit; };
        my $row_ref =$sth->fetchrow_hashref();
        return $row_ref->{Last_Id};
}

sub getHowMany($$){
	(my $sql)=@_;
	my $sth;
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute() ;};
	if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	my $row_ref =$sth->fetchrow_hashref();
        return $row_ref->{Counts}/1;
}
