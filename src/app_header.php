<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
  	
  	<link rel="icon" href="images/coreFlow_favico.ico" />

    <title><?php include_once('common_connect.php'); print $menuTitle; print getAppName() ?></title>
    <meta name="description" content="Flexible Data mining and data analysis framework for proteomics and genomics">
    <meta name="keywords" content="Flexible Data mining data analysis framework and playground R biopython perl proteomics and genomics">
    <meta name="AUTHORS" content="Adrian Pasculescu, Karen Colwill">
		<LINK REL="stylesheet" HREF="css/generic.css" type="text/css">


 
    <style type="text/css">
		  .app_header	    {font-size:11px; font-family:Arial} 
		  td.app_header   {text-align:center;
		  		            -webkit-border-radius: 10px;
	                    -khtml-border-radius: 10px;	
	                    -moz-border-radius: 10px;
	                     border-radius: 10px;
	                     border: 1px solid #BBBBBB;
	                     padding: 3px;
	                     background: #EEEEEE
		  	               }

		  td.app_header_white   {text-align:center;
		  		            -webkit-border-radius: 10px;
	                    -khtml-border-radius: 10px;	
	                    -moz-border-radius: 10px;
	                     border-radius: 10px;
	                     border: 1px solid #BBBBBB;
	                     padding: 3px;
	                     background: #FFFFFF
		  	               }


		  td.app_header_grey   {text-align:center;
		  		            -webkit-border-radius: 10px;
	                    -khtml-border-radius: 10px;	
	                    -moz-border-radius: 10px;
	                     border-radius: 10px;
	                     border: 1px solid #BBBBBB;
	                     padding: 3px;
	                     background: #F0F0F0
		  	               }


		  td.app_header_selected   {text-align:center;
		  		            -webkit-border-radius: 10px;
	                    -khtml-border-radius: 10px;	
	                    -moz-border-radius: 10px;
	                     border-radius: 10px;
	                     border: 1px solid #BBBBBB;
	                     padding: 3px;
	                     background: #FEFB8C
		  	               }


		  a.app_header    {text-decoration: none}
		  img.app_header  {width: 30px; border:0px}	
		  img.app_header_home  {width: 50px; border:0px}	

		  .app_footer	    {font-size:11px; font-family:Arial} 


			td.time {vertical-align:bottom; color:darkgrey; font-size:80%}
			td.youtube {vertical-align:bottom;}


			table.loadFile {
			          -webkit-border-radius: 5px;
		            -khtml-border-radius: 5px;	
		            -moz-border-radius: 5px;
		             border-radius: 5px;
			           }


		    div.mother {text-align:center;
		  		            -webkit-border-radius: 10px;
	                    -khtml-border-radius: 10px;	
	                    -moz-border-radius: 10px;
	                     border-radius: 10px;
	                     border: 1px solid #BBBBBB;
	                     padding: 3px;
		                }
		 		    img {vertical-align:middle}
		    * {-webkit-print-color-adjust:exact;}
               

    </style>

    <script  type="text/javascript">

				var BrowserDetect = {
					init: function () {
						this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
						this.version = this.searchVersion(navigator.userAgent)
							|| this.searchVersion(navigator.appVersion)
							|| "an unknown version";
						this.OS = this.searchString(this.dataOS) || "an unknown OS";
					},
					searchString: function (data) {
						for (var i=0;i<data.length;i++)	{
							var dataString = data[i].string;
							var dataProp = data[i].prop;
							this.versionSearchString = data[i].versionSearch || data[i].identity;
							if (dataString) {
								if (dataString.indexOf(data[i].subString) != -1)
									return data[i].identity;
							}
							else if (dataProp)
								return data[i].identity;
						}
					},
					searchVersion: function (dataString) {
						var index = dataString.indexOf(this.versionSearchString);
						if (index == -1) return;
						return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
					},
					dataBrowser: [
						{
							string: navigator.userAgent,
							subString: "Chrome",
							identity: "Chrome"
						},
						{ 	string: navigator.userAgent,
							subString: "OmniWeb",
							versionSearch: "OmniWeb/",
							identity: "OmniWeb"
						},
						{
							string: navigator.vendor,
							subString: "Apple",
							identity: "Safari",
							versionSearch: "Version"
						},
						{
							prop: window.opera,
							identity: "Opera",
							versionSearch: "Version"
						},
						{
							string: navigator.vendor,
							subString: "iCab",
							identity: "iCab"
						},
						{
							string: navigator.vendor,
							subString: "KDE",
							identity: "Konqueror"
						},
						{
							string: navigator.userAgent,
							subString: "Firefox",
							identity: "Firefox"
						},
						{
							string: navigator.vendor,
							subString: "Camino",
							identity: "Camino"
						},
						{		// for newer Netscapes (6+)
							string: navigator.userAgent,
							subString: "Netscape",
							identity: "Netscape"
						},
						{
							string: navigator.userAgent,
							subString: "MSIE",
							identity: "Explorer",
							versionSearch: "MSIE"
						},
						{
							string: navigator.userAgent,
							subString: "Gecko",
							identity: "Mozilla",
							versionSearch: "rv"
						},
						{ 		// for older Netscapes (4-)
							string: navigator.userAgent,
							subString: "Mozilla",
							identity: "Netscape",
							versionSearch: "Mozilla"
						}
					],
					dataOS : [
						{
							string: navigator.platform,
							subString: "Win",
							identity: "Windows"
						},
						{
							string: navigator.platform,
							subString: "Mac",
							identity: "Mac"
						},
						{
							   string: navigator.userAgent,
							   subString: "iPhone",
							   identity: "iPhone/iPod"
					    },
						{
							string: navigator.platform,
							subString: "Linux",
							identity: "Linux"
						}
					]
				
				};
				BrowserDetect.init();
 			</script>	
    
  </head>
  <body onLoad="focus()">

    <script  type="text/javascript">
	  	function highLiteMenu(menuId){
	  		crtMenu=document.getElementById(menuId);
	  		crtMenu.className='app_header_selected';
	  	}
    </script>	    
 
		<table style="border:0px; topmargin:0px" align="center">
		   <tr>

			    <td id="homeMenu" class="app_header"  title="Back to home page">
		        <a class="app_header" href="index.php">
		        	<br>
		        	<img alt=""  class="app_header_home" src="images/core_flow_4.png">		       
		         <br><span style="color:black; font-weight:bold; font-size:120%"><?php print getAppName() ?></span>
		         <br>
		         <br>
		         </a>
			    </td>

			    <td> &nbsp;&nbsp;&nbsp;&nbsp;<!-- spacer -->
			    </td>

			    <td id="flowChartMenu" class="app_header" style="background: #FFFFFF"  title="Manage Data Analysis flows; Collapse/expand owners/projects/threads; Examine attached files.">
		        <a class="app_header" href="analysis.php" target="FlowCharts <?php print str_replace('CoreFlow','',getAppName()); ?>" >
		        	<img alt=""  class="app_header" src="images/line_chart-edit.png">
		          <br>Analysis
		        </a>
			    </td>

			    <td> &nbsp;&nbsp;&nbsp;&nbsp;<!-- spacer -->
			    </td>

			    <td id="viewTablesMenu" class="app_header"   title="View Tables stored in the database(s), their structure and some of their data rows.">
			    	<a class="app_header" href="browseDB.php">
			    		<img alt=""  class="app_header" src="images/tables-relation.png">
			    	  <br>Browse DB
			    	</a>
			    </td>
		
		      <td id="editableTableMenu" class="app_header" title="Update a small database table using copy/paste from Excel">
			    	<a class="app_header" href="excelToDB.php">
			    		<img alt=""  class="app_header" src="images/table-edit.png" >
			    	  <br>Excel to DB
			    	</a>	
		      </td>	
		
		      <td id="loadDatabaseMenu" class="app_header"  title="Load a DB table with data from various file formats.">
			    	<a class="app_header" href="fileToDB.php">
			    		<img alt=""  class="app_header" src="images/db_comit.png">
			    	  <br>file to DB
			    	</a>	
		      </td>	
		      
		      <td id="sqlEditorMenu" class="app_header" title="Open a SQL colored syntax editor in a `playground` area. Edit and run a SQL query to create or query joined tables.">
		        <a class="app_header" href="dbQuery.php" >
		        	<img alt=""  class="app_header" src="images/sql_join_inner.png">
		          <br>DB query
		        </a>
		      </td>
		      	
		  		<td id="manageIconsMenu" class="app_header" title="Manage analysis Authors or Icons that are attached to the expandable Flowcharts or embedded in the wiki descriptions. They help identifying different steps in flowcharts.">  
			      <a class="app_header" href="icons.pl">
			      	<img alt=""  class="app_header" src="images/palette.png">
			        <br>Icons
			      </a>
			    </td>

			    <td> &nbsp;&nbsp;&nbsp;&nbsp;<!-- spacer -->
			    </td>


		
			    <td id="readmeMenu" class="app_header_grey">
			    	<a class="app_header" href="readme.php">
			    		<img alt=""  class="app_header" src="images/notes.png" title="Notes and caveats">
			    	  <br>Readme
			    	</a>
			    </td>	
		  		
			    <td id="downloadMenu" class="app_header_grey">
			    	<a class="app_header" href="download_public.php">
			    		<img alt=""  class="app_header" src="images/archive.png" title="Download the sources for installation">
			    	  <br>Downloads
			    	</a>
			    </td>	

			    <td id="contactMenu" class="app_header_grey">
			    	<a class="app_header" href="contact.php">
			    		<img alt=""  class="app_header" src="images/contacts.png" title="Contact authors for questions, suggestions ...">
			    	  <br>Contact
			    	</a>
			    </td>	

		  </tr>   
		</table>

