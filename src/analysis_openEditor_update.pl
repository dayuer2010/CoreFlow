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


# Updates the custom analysis table only the DB_script or R_script and the timestamp
# Date created: 2013 March 17
# Last change:  2013 March 17 - 
# 		 

use strict;	# comment this line to speed up things

print "Content-type: text/html\n\n";

use common_DB;
my $dbh=common_DB::connectToDB();



use CGI qw(-debug :standard);


if(!common_DB::checkViewAccess()){exit;}

my $dbh=common_DB::connectToDB();

my $sql;
my $sth;

my @allParams=param(); #print join(':',@allParams);
my $changedValue	=common_DB::trim(param("__changedValue"));
my $autoIncColumn	=common_DB::trim(param("__autoIncrementColumn"));
my $autoIncValue	=common_DB::trim(param("__autoIncrementValue"));
my $tableName		=common_DB::trim(param("__tableName"));
my $changedColumn	=common_DB::trim(param("__changedColumn"));
my $timestampColumn	=common_DB::trim(param("__timestampColumn"));


my $sql;


if(!common_DB::checkWriteExecuteAccess()){
	print '<span >Note that you do not have privileges to change database content! Please contact authors if needed.</span><br>'."\n";
}


{  # this is only for the update 
	$sql = ' update '.$tableName.'
	';
	my $whereClause=" 1=1 ";
	my $setClause;
	$setClause .= " `$changedColumn`=? \n";
	
  if($timestampColumn ne ""){
  	if ($setClause ne ""){ $setClause .= ", ";}
  	$setClause .= " `$timestampColumn`=now() \n";
  }
  $whereClause .= "and `$autoIncColumn`=$autoIncValue \n";

	# first verify if more than on record will be deleted
	my $sqlCheck = 'select count(*) as Counts from '.$tableName.' where '.$whereClause;
	if(getHowMany($sqlCheck)>1){
		print '	<head></head><body><span style="color:red">More than 1 records will be affected! Aborting ('.$tableName.' - '.$whereClause.').</span>
			<!--  
			changed Columns list: '.$changedColumn.'
			
			'.$sqlCheck.'-->
			</body>
			';
		exit;
	}


	$sql .= " set $setClause \n";
	$sql .= " where $whereClause \n";
	#print "<br><pre>".$sql."\n $changedColumn</pre>\n"; exit;
	eval { $sth= $dbh->prepare($sql); };
	if ($@) { print "Couldn't prepare query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
	eval { $sth->execute($changedValue) ;};
	if ($@) { print "Couldn't execute query <pre>'$sql': $DBI::errstr\n</pre>"; exit; };
}



print '	<head>
	</head>
	<body>
	<!--
	Done!
	<pre>
	'.$sql.'
	</pre>
	-->
	<script>
		location.href="analysis_openEditor.php?parentName='.$changedColumn.'&parentPrimaryKey='.$autoIncValue.'";
	</script>
	</body>
	';
exit;
##################################################################




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
