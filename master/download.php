<?php
/**
*
* Copyright (c) 2012-2013 Mount Sinai Hospital, Toronto, Ontario, 
* Copyright (c) 2012-2013 DTU/CSIG Linding Lab
*
* LICENSE:
*
* This is free software; you can redistribute it
* and/or modify it under the terms of the GNU General
* Public License as published by the Free Software Foundation;
* either version 3 of the License, or (at your option) any
* later version.
*
* This software is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
* General Public License for more details.
*
* You should have received a copy of the GNU General Public
* License along with the source code.  If not, see <http://www.gnu.org/licenses/>.
*
*
*/

?>
<?php include "app_header.php"; ?>
<script type="text/javascript">
	highLiteMenu('downloadMenu');
</script>	    	

<div align="center">
<BR><DIV style="font-size:12pt; font-family:Helvetica; font-weight:bold;">Before downloading, please read and agree to the following Terms and Conditions:</DIV><BR>

<DIV style="width:85%; border:2px solid gold; background: beige; padding:5px;">
<table style="font-size:12pt; font-family:Helvetica; padding:3px; text-align:justify;" cellpadding="0" cellspacing="0">

<tbody><tr><td style="text-align:left; padding-top:10px; font-weight:bold; color:navy;">CoreFlow Terms and Conditions</td>

			</tr><tr>
				<td style="font-weight:bold; padding-top:10px;">
					License
				</td>
			</tr>

			<tr>
				<td style="padding-top:10px;">
					<b>CoreFlow</b> is distributed under the GNU License, Version 3; you may not use this application except in compliance with the License. You may obtain a copy of the License at <a href="http://www.gnu.org/licenses">www.gnu.org/licenses</a>.
				</td>
			</tr>

			<tr>
				<td style="padding-top:10px; font-weight:bold;">
					Third Party Links
				</td>
			</tr>

			<tr>
				<td style="padding-top:10px;">
					This software package may contain third-party owned content or information, and may also include links or references to other web sites maintained by third parties over whom Mount Sinai Hospital has no control. Mount Sinai Hospital does not endorse, sponsor, recommend, warrant, guarantee or otherwise accept any responsibility for such third party content, information or web sites. Access to any third-party owned content or information or web site is at your own risk, and Mount Sinai Hospital is not responsible for the accuracy or reliability of any information, data, opinions, advice or statements made in such content or information or on such sites.
				</td>
			</tr>

			<tr>
				<td style="font-weight:bold; padding-top:10px;">
					Disclaimer of Warranty; Limitation of Liability
				</td>
			</tr>

			<tr>
				<td style="padding-top:10px;">
					MOUNT SINAI HOSPITAL DISCLAIMS ALL EXPRESS AND IMPLIED WARRANTIES WITH REGARD TO THIS SOFTWARE PACKAGE, THE INFORMATION, SERVICES AND MATERIALS DESCRIBED OR CONTAINED IN THIS SOFTWARE PACKAGE, INCLUDING WITHOUT LIMITATION ANY IMPLIED WARRANTIES OF MERCHANTIBILITY, FITNESS FOR A PARTICULAR PURPOSE OR NON-INFRINGEMENT.  YOUR ACCESS TO AND USE OF THIS SOFTWARE PACKAGE ARE AT YOUR OWN RISK. INFORMATION, SERVICES AND MATERIALS CONTAINED HEREIN MAY NOT BE ERROR-FREE. MOUNT SINAI HOSPITAL IS NOT LIABLE OR RESPONSIBLE FOR THE ACCURACY, CURRENCY, COMPLETENESS OR USEFULNESS OF ANY INFORMATION, SERVICES AND MATERIALS PROVIDED IN THIS SOFTWARE PACKAGE. IN ADDITION, MOUNT SINAI HOSPITAL SHALL ALSO NOT BE LIABLE FOR ANY DAMAGES, INCLUDING WITHOUT LIMITATION, DIRECT, INCIDENTAL, CONSEQUENTIAL, AND INDIRECT OR PUNITIVE DAMAGES, ARISING OUT OF ACCESS TO, USE OR INABILITY TO USE THIS SOFTWARE PACKAGE OR SERVICES DESCRIBED HEREIN, OR ANY ERRORS OR OMISSIONS IN THE CONTENT THEREOF. THIS INCLUDES DAMAGES TO, OR FOR ANY VIRUSES THAT MAY INFECT YOUR COMPUTER EQUIPMENT. WITHOUT LIMITING THE FOREGOING, EVERYTHING IN THIS SOFTWARE PACKAGE IS PROVIDED AS IS WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESS OR IMPLIED.
				</td>
			</tr>

			<tr>
				<td style="font-weight:bold; padding-top:10px;">
					Indemnification
				</td>
			</tr>

			<tr>
				<td style="padding-top:10px;">
					You agree to indemnify, defend and hold harmless Mount Sinai Hospital, its officers, directors, employees, agents, consultants, suppliers and third party partners from and against all losses, expenses, damages and costs, including reasonable attorneys' fees, resulting from any violation by you of these Terms and Conditions
				</td>
			</tr>

			<tr>
				<td style="font-weight:bold; padding-top:10px;">
					Applicable Laws
				</td>
			</tr>

			<tr>
				<td style="padding-top:10px;">
					These Terms and Conditions and the resolution of any dispute related to these Terms and Conditions shall be construed in accordance with the laws of the province of Ontario, Canada, without regard to its conflicts of laws principles. Any legal action or proceeding related to this Software shall be brought exclusively by the federal and provincial courts of the province of Ontario, Canada.
				</td>
			</tr>
		</tbody>
</table>
</DIV>

<BR>
<INPUT TYPE="button" style="" onclick="document.getElementById('download_instructions').style.display='inline';" value="I have read and accept the license agreement, show me the download instructions">

<br><br>
<table ID="download_instructions" align="center" style="display:none; margin-top:10px;">
<tr><td style="text-align:left; border: 2px solid gold; background: beige; padding: 5px;">

<DIV style="font-size:12pt; font-family:Helvetica; font-weight:bold; color:navy;">CoreFlow Installation Instructions:</DIV><BR>

Extract the following archive <a href="../tools/coreFlow_web_2013_01.tgz">coreFlow_web_2013_01.tgz</a> into your web (apache) server directory prepared for <?php print getAppName() ?>.
<br>You will use the following mysql dump to create the mysql database tables: <a href="../tools/mol_bio_dump.sql">dump file</a>.
<br>You will need to configure your <b>database, apache, git and other components</b>
 following the instructions from <b><a href="install_wiki_edit.php" target="CoreFlow Install wiki">CoreFlow install documentation</a></b>.
<br>The person installing <?php print getAppName() ?> should have prior experience with <b>Linux/Unix/Mac, apache web server, mysql, php, perl, R, xml, html, javascript, git (or svn)</b>.  
<br>Watch the youtube videos listed in the <b><?php print getAppName() ?> home page</b>
  to understand how <?php print getAppName() ?> is supposed to work. 

</td></tr></table>
</div>
<?php include "app_footer.php"; ?>
