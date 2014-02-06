package common_DB;
# Adrian Pasculescu pawsonlab.mshri.on.ca
# common functions for DB access etc.
# Date created: 2010 Apr 30
# Last change: 2012 Nov 26 

use strict;	# comment this line to speed up things
use DBI; 
use DBD::mysql;
use XML::XPath;


##################################################################
my $file = '.config.xml';
my $xp = XML::XPath->new(filename=>$file);

sub checkViewAccess(){
	my @xp_allowViewIPnodes=$xp->find('//allow_view_IPs')->get_nodelist;
	my $xp_allowViewIP=$xp_allowViewIPnodes[0]->string_value;
	my $remoteAddr=trim($ENV{REMOTE_ADDR});
	return $remoteAddr =~ /$xp_allowViewIP/;
}

sub checkWriteExecuteAccess(){
	my @xp_allowRXIPnodes=$xp->find('//allow_wx_IPs')->get_nodelist;
	my $xp_allowRXIP=$xp_allowRXIPnodes[0]->string_value;
	my $remoteAddr=trim($ENV{REMOTE_ADDR});
	return ($remoteAddr =~ /$xp_allowRXIP/);
}


sub connectToDB(){
	# same user used for insert/update
	
	my $dbh;

  my @xp_host=$xp->find('//db/host')->get_nodelist;
  my @xp_user;
  my @xp_pwd;
  if(checkWriteExecuteAccess()){
	  @xp_user=$xp->find('//db/user')->get_nodelist;
	  @xp_pwd =$xp->find('//db/user/@passwd')->get_nodelist;
	} else {
	  @xp_user=$xp->find('//db/user_public')->get_nodelist;
	  @xp_pwd =$xp->find('//db/user_public/@passwd')->get_nodelist;
	}
  my @xp_db  =$xp->find('//db/database')->get_nodelist;

  my $DB     =$xp_db[0]->string_value;
	my $DB_USER=$xp_user[0]->string_value;
	my $DB_PWD =$xp_pwd[0]->string_value;
	my $DB_HOST=$xp_host[0]->string_value;

  $dbh = DBI->connect( 'DBI:mysql:'.$DB.":".$DB_HOST,$DB_USER, $DB_PWD,
                         { RaiseError => 1 } );

	return $dbh;
}

sub getConfigValue($){
	(my $path)=@_;
  my @xp_nodes=$xp->find($path)->get_nodelist;
  return $xp_nodes[0]->string_value;
}


##################################################################

sub trim($){
	(my $s)=@_;
	$s =~ s/^\s+//;
	$s =~ s/\s+$//;
	return $s;
}

sub trimComments($){
	(my $s)=@_;
	$s =~ s/"/`/g;
	$s =~ s/'/`/g;
	return $s;
}


sub trimQuotes($){
	(my $s)=@_;
	$s =~ s/"//g;
	$s =~ s/'//g;
	return $s;
}


1;

