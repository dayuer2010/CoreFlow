<?php include_once('common_connect.php'); checkViewAccess(); ?>
<?php $menuTitle='' ;include "app_header.php"; ?>

<script  type="text/javascript">
	 highLiteMenu('homeMenu');
	 window.name="CoreFlow";
</script>	    	


<br><br>
<div align="center" style="align:center; font-family:Arial; font-size:80%">
	<h3>A computational platform for integration, analysis and modeling of complex biological data</h3>


<table>

<tr>
	<td class="youtube">
		<a href="https://www.youtube.com/watch?v=VaWs1kaHE6E" title="CoreFlow2013 - Navigation in Core Flow, Management of FlowCharts, Analysis task components: wiki description, data pre-processing in SQL, statistical analysis in R. Saving and viewing script versions using `git` or `svn`. Running scripts, attaching results. Relationship between tasks(data provenance) in graphical format.  " target="_blank">
		<!--a href="../CF_user_inreface_v1.mov" title="Download the movie in QuickTime format (58 Mb)" -->
	    <img alt="" style="vertical-align:bottom" src="images/youtube.png">
	    <?php print getAppName() ?> -user interface overview  
	  </a>
	</td>
	<td class="time"> 8 min.</td>
	<td class="time"> 
		<a href="../CF_user_inreface_v1.mov" title="Download the movie in QuickTime format (58 Mb)">
			<img alt="" src="images/movie.png" width="20" style="vertical-align:bottom">
	  </a>
	</td>
</tr>


		
<tr>
	<td class="youtube">
		<a href="https://www.youtube.com/watch?v=oOzHnh-HRXM" title="CoreFlow2013 - Cloning an analysis task, Editing the description in wiki format, Extracting data needed froom the database using the SQL script; Running SQL to verify data. Running R or python or perl scripts. Examination of results. Pdf graphs: R to P convertion rate variation by sample." target="_blank">
		<!-- a href="../R_to_P_conversion_part_1.mov" title="Download the movie in QuickTime format (57 Mb)" -->
	    <img alt="" style="vertical-align:bottom" src="images/youtube.png">
	    Using <?php print getAppName() ?> - SILAC Arg to Pro data analysis - Part 1 
	  </a>
	</td>
	<td class="time">9 min.</td>
	<td class="time"> 
		<a href="..R_to_P_conversion_part_1.mov" title="Download the movie in QuickTime format (57 Mb)">
			<img alt="" src="images/movie.png" width="20" style="vertical-align:bottom">
		</a>
  </td>
</tr>		  



<tr>
	<td class="youtube">
		<a href="http://www.youtube.com/watch?v=ujoZe2zlOTY&amp;feature=youtu.be" title="CoreFlow2013 - Cloning an analysis task, changing attributes: icons, authors, project, thread; Focus on the R script; Copy/paste to/from a local R or RStudio instalation; Explaining API calls and testing API calls in a browser; Verifying in R the structure of data must match the one generated in SQL; Attaching results or any files to a task. Show estimation of R to P conversion rate for different experiments." target="_blank">
		<!-- a href="../R_to_P_conversion_part_2.mov" title="Download the movie in QuickTime format (87 Mb)" -->
	    <img alt="" style="vertical-align:bottom" src="images/youtube.png">
	    Using <?php print getAppName() ?> - SILAC Arg to Pro data analysis - Part 2 
	  </a>
	</td>
	<td class="time">12 min.</td>
	<td class="time"> 
		<a href="../R_to_P_conversion_part_2.mov" title="Download the movie in QuickTime format (87 Mb)">
			<img alt="" src="images/movie.png" width="20" style="vertical-align:bottom">
		</a>
  </td>
</tr>		  

<!--
<tr>
	<td class="youtube"><a href="http://www.youtube.com/watch?v=oOzHnh-HRXM&feature=youtu.be" target="_blank">
	  <img style="vertical-align:bottom" src="images/youtube.png">Using <?php print getAppName() ?> - SILAC labeling incorporation efficiency - Part 1 </a>
	</td>
	<td class="time">12 min.</td>
</tr>		 
<tr>
	<td class="youtube"><a href="http://www.youtube.com/watch?v=oOzHnh-HRXM&feature=youtu.be" target="_blank">
	  <img style="vertical-align:bottom" src="images/youtube.png">Using <?php print getAppName() ?> - SILAC labeling incorporation efficiency - Part 2 </a> 
	</td>
	<td class="time">13 min</td>
</tr>
-->		 
<!-- a href="http://www.youtube.com/watch?v=-LLYipVPBhE&feature=youtu.be" target="_blank"><img src="images/youtube.png">Installing and enhancing <?php print getAppName() ?> </a-->
</table>

</div>
<?php include "app_footer.php"; ?>
