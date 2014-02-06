<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" version="4.0" encoding="iso-8859-1" indent="yes" />
<!--xsl:include href="table_common.xsl" /-->
<!-- 
  author: Adrian Pasculescu
  generates an HTML  based on a "standrd" XML content from table MB_CUSTOM_ANALYSIS

<analysis path="MB_CUSTOM_ANALYSIS">
<owner name="Rune Linding">
<project name="Munoz - Stem Cells">
  <thread name="All data Filtered for CV]">
    <step name="All data filtered CV less 50%" threadId="1" mainId="25">All data filtered CV less 50%</step>
      <attachment ../><attachment ../>
    <step name="K-means filtered CV less 50%" threadId="2" mainId="26">K-means filtered CV less 50%</step>
    <step name="K-means filtered CV less 50% and Multi Sequences" threadId="3" mainId="27">K-means filtered CV less 50% and Multi Sequences</step>
  </thread>
</project>
</owner>

</analysis>

  last change: 	2008-05-21
  		2007-05-21 - 
    	2012-09-20	
-->
 
<xsl:template match="analysis">

  <html>
    <head>
    	<link rel="icon" href="images/coreFlow_chart.ico" />
      <meta http-equiv="content-type" content="text/html;charset=iso-8859-1"/>
        <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE"/>
        <script type="text/javascript" src="wikiwym-read-only/lib/GoogleCodeWikiParser.js"></script>
	<title>FlowCharts <xsl:value-of select="@organization"/></title>
	<style type="text/css">
		* {-webkit-print-color-adjust:exact;} /** this is fory pritty print with Chrome **/
		img {vertical-align: middle}
		
		body {
			font-family : arial;
			font-size : 80%;
			}
			
		ul .tree{
			margin-left:0px;
			padding-left:0px;
			display:block;
		}
		
		 li .tree{
			list-style-type:none;
			vertical-align:middle;
		}

		ul.owner_minus {display:none}
		ul.owner_plus {display:block}

		ul.project_minus {display:none}
		ul.project_plus {display:block}

		ul.thread_minus {display:none}
		ul.thread_plus {display:block}
		
		span.owner {color:#FF0000; font-weight:bold }
		span.project {color:#0000FF; font-weight:bold }
		span.thread {color:#008800; font-weight:bold }
		span.stepId { font-weight:bold }
		span.stepId { font-size : 80%; }


    span.mainIdHide { font-size:70%; padding:3px; color: white; }
    span.mainIdShow { font-size:70%; padding:3px; color: #777777; }

		div.tooltip 	{font-family : Verdana; 
					font-size : 13px; 
					padding: 3px 3px 3px 3px;
					color : #0000FF; 
					position : absolute; 
					visibility : hidden; 
					background : #FFFFCC; 
					border-style : solid; 
					border-color : #000000; 
					border-width : 1px;
					z-index: 999}

		.round_corners {-webkit-border-radius: 10px;
			              -khtml-border-radius: 10px; 
			              -moz-border-radius: 10px; 
			              border-radius: 10px; 
			              padding:3px ;
			              display: inline-block; }
		
		.lessImportant {font-size:80%;
			             background-color:#DDDDDD; }
	
	  img.img_hide   {visibility:hidden}
	  img.img_reveal {visibility:visible}
				
	</style>
	<script type="text/javascript"><![CDATA[

	  /***** the capturing of the expanded elements in a select structure called 'aSelectControl' *****/	
	  function removeOption(optValue){
		var elSel=document.getElementById("aSelectControl");
		var i;
  		for (i = elSel.length - 1; i>=0; i--) {
    			if (elSel.options[i].value == optValue) {
      				elSel.remove(i);
    			}
  		}
	  }

	  function appendOption(optValue,optText){
		var elSel=document.getElementById("aSelectControl");

		var elOptNew = document.createElement("option");
		elOptNew.value = optValue ;
		elOptNew.text  = optText ;
		elOptNew.selected=true; 
		try {
			elSel.add(elOptNew, null); // standards compliant; does not work in IE
		}
  		catch(ex) {
    			elSel.add(elOptNew); // IE only
  		}
	  }

	  function refreshView(mainId){
		 with(document.customAnalysisForm){
			// action="#"+mainId; // jump to this step
			action="#";
	  	expand_all_flag.value="";
			submit();
		 }
	  }

	  function revealExpanded(){
		var elSel=document.getElementById("aSelectControl");
  		for (i = elSel.length - 1; i>=0; i--) {
    			var optValue= elSel.options[i].value;
    			var optText = elSel.options[i].text;
	  		targetObj	= document.getElementById(optValue);
	  		targetObj.className=optText;
			/* must add a new expaned element*/
			appendOption(optValue);
			
  		}
	  }
	  
	  
	  function expandCollapseAll(obj){
	  	if(obj.src.indexOf('expand')>=0){
	  		obj.src="images/collapse_2.png";
	  		document.customAnalysisForm.expand_all_flag.value="Yes";
	  	  document.customAnalysisForm.submit();
	  	}else{
	  		obj.src="images/expand_2.png";
	  		// copy selectOriginal to select
	  		location.href='analysis.php';
	  	}
	  }
	  
	  
	  /**** end of expand/colapse after refresh *********/


	  var popup;
	  function showHide(obj){
	  	crtId=obj.id;
	  	
	  	if (obj.src.indexOf('owner_plus_small.png') >=0){
	  		obj.src 	= 'images/owner_minus_small.png';
	  		targetObj	= document.getElementById('owner_'+crtId);
	  		targetObj.className='owner_plus';
			/* must add a new expaned element*/
			appendOption('owner_'+crtId,'owner_plus');
	  		return;
	  	}
	  	if (obj.src.indexOf('owner_minus_small.png') >=0){
	  		obj.src 	= 'images/owner_plus_small.png';
	  		targetObj	= document.getElementById('owner_'+crtId);
	  		targetObj.className='owner_minus';
			/* must remove colapsed element*/
			removeOption('owner_'+crtId);
	  		return;
	  	}
	  	
	  	if (obj.src.indexOf('project_plus_small.png') >=0){
	  		obj.src 	= 'images/project_minus_small.png';
	  		targetObj	= document.getElementById('project_'+crtId);
	  		targetObj.className='project_plus';
			/* must add a new expaned element*/
			appendOption('project_'+crtId,'project_plus');
	  		return;
	  	}
	  	if (obj.src.indexOf('project_minus_small.png') >=0){
	  		obj.src 	= 'images/project_plus_small.png';
	  		targetObj	= document.getElementById('project_'+crtId);
	  		targetObj.className='project_minus';
			/* must remove colapsed element*/
			removeOption('project_'+crtId);
	  		return;
	  	}
	  	

	  	if (obj.src.indexOf('thread_plus_small.png') >=0){
	  		obj.src 	= 'images/thread_minus_small.png';
	  		targetObj	= document.getElementById('thread_'+crtId);
	  		targetObj.className='thread_plus';
			/* must add a new expaned element*/
			appendOption('thread_'+crtId,'thread_plus');
	  		return;
	  	}
	  	if (obj.src.indexOf('thread_minus_small.png') >=0){
	  		obj.src 	= 'images/thread_plus_small.png';
	  		targetObj	= document.getElementById('thread_'+crtId);
	  		targetObj.className='thread_minus';
			/* must remove colapsed element*/
			removeOption('thread_'+crtId);
	  		return;
	  	}

	  	
	  }
	  function editStep(analysisStepId){
	  	
	  }
    ///////////////////////////////////////// Wiki parser
    gcwp = new GoogleCodeWikiParser();
    
		/////////////////////////////////////////Tool tip code
		       
		// initialize some global vars
		var xCoord, yCoord, br, ver;
		
		
		// very basic check for browser type and version
		if (navigator.appName == "Netscape") {
			br="ns";
		}
		ver = navigator.appVersion.substring(0,1);
		
		
		// function to make tool tip visible 
		// lyr is NOT an object
		function rollOn(lyrName,mainId) {
			    if(document.documentElement.scrollTop){
			        offset_Left=document.documentElement.scrollLeft;
			        offset_Top =document.documentElement.scrollTop;
			    }
			    else{
			        offset_Left=document.body.scrollLeft;
			        offset_Top =document.body.scrollTop;
			    }
			    
		    	lyr = document.getElementById(lyrName);
		    	if (!lyr) {return;}
		    	if(lyr.innerHTML==""){
		    		// try to convert from Wiki
		    		lyr_wiki=document.getElementById(lyrName+'_wiki');
		    		if(lyr_wiki){
		    			content_wiki=lyr_wiki.innerHTML;
		    			// get rid of the step and add a crlf
		    			content_wiki=content_wiki.replace(/<step_description>/,'');
		    			content_wiki=content_wiki.replace(/<\/step_description>/,'');
		    			content_nice=gcwp.parse(content_wiki);
		    			my_innerHTML ='<img style="cursor:pointer" src="images/close_object.png" width="12" border="0" title="close" onclick="'+lyr.getAttribute('action')+'">';
		    			my_innerHTML = my_innerHTML +'<img src="images/null.gif" width="5"><img style="cursor:pointer" src="images/wiki_notes.png" width="16" border="0" title="edit description in wiki format" onclick="openEditor('+"'Description',"+mainId+",'w"+mainId+"'"+')"><br>';
		    			lyr.innerHTML = my_innerHTML + content_nice;
		    		}
		    	}
		    	
					lyr.style.position='absolute';
					lyr.style.left = (xCoord + 20 + offset_Left)+"px";
					lyr.style.top = (yCoord - 15 + offset_Top)+"px";
					lyr.style.visibility = 'visible';
					lyr.style.zIndex =999;
		}
		
		// function to make tool tip hidden
		// lyr is NOT an object
		function rollOut(lyrName) {
				lyr = document.getElementById(lyrName);
		    if (!lyr) {return;}
				lyr.style.visibility='hidden';
		}
		
		// this code just tracks the mouse coordinates
		// need these so we know where to pop up the tool tips
		function checkwhere(e) {
			// if netscape 4.x
			if (document.layers){
		        xCoord = e.x;
		        yCoord = e.y;
			}
			// if IE 
			        else if (document.all){
			        xCoord = event.clientX;
			        yCoord = event.clientY;
			}
			// this catches NS6 and IE 5+
		        else if (document.getElementById){
		        xCoord = e.clientX;
		        yCoord = e.clientY;
			}
		}
		
		// start tracking mouse move events
		document.onmousemove = checkwhere;
		if(document.captureEvents) {document.captureEvents(Event.MOUSEMOVE);}
		
		/////////////////////end of tool tip code


    function showHideMainId(obj){
    	// obj is the ligth switch image
    	my_source=new String(obj.src);
    	if(my_source.indexOf('switch_off.png')>=0){
    		obj.src="images/switch_on.png";
		    var all_spans = document.getElementsByTagName("span");
		    for (var i = 0; i < all_spans.length; i++) {
		        if(all_spans[i].className=='mainIdHide') { all_spans[i].className='mainIdShow' }
		    }
    		alert(s)
    	} else {
    		obj.src="images/switch_off.png";
		    var all_spans = document.getElementsByTagName("span");
		    for (var i = 0; i < all_spans.length; i++) {
		        if(all_spans[i].className=='mainIdShow') { all_spans[i].className='mainIdHide' }
		    }
    	}
    }
    


			function openEditor(objName,primaryKey,myTarget){
				document.formOpenEditor.parentId.value=''; // don't need to return from editor back here 
				document.formOpenEditor.parentName.value=objName;
				document.formOpenEditor.parentContent.value=''; // don't need send a content to the editor
				document.formOpenEditor.parentPrimaryKey.value=primaryKey;
				document.formOpenEditor.target=myTarget;
				document.formOpenEditor.submit();
			}


	  ]]>
	</script>
    </head>

  <body style="margin:2px" onLoad="focus()">
	<h2>FlowCharts <xsl:value-of select="@organization"/></h2>
	<a href="index.php" target="CoreFlow" title="open main CoreFlow page in a separate tab if not already present (Google Chrome only)"><img alt="" style="vertical-align:bottom"  src="images/home2.png" height="23px"/></a> 
	<img alt=""  src="images/null.gif"/><a href="analysis_wiki.php" target="analysis_wiki"> <img alt=""  style="vertical-align:bottom" src="images/wiki_notes.png"  height="23" title="Add your own Notes and Comments. Improves CoreFlow efectiveness."/></a>
	<img alt=""  src="images/null.gif"/><img alt=""  style="cursor:pointer; vertical-align:bottom" onclick="showHideMainId(this)" src="images/switch_off.png"  height="23" title=" Show/Hide the main Id (DB id) of each task"/>
	<img alt=""  src="images/null.gif"/><img alt=""  style="cursor:pointer; vertical-align:bottom" onclick="expandCollapseAll(this)" src="{//expandedSet/@img_src}"  height="20" title=" Expand all levels / collapse to minimum"/>
  <img alt=""  src="images/null.gif"/><img alt=""  class="img_reveal" id="search_owner_img_all" style="cursor:pointer;vertical-align:bottom" src="images/search_icon.png" onclick="setSearch('Project_Owner','%','','library')" border="0" height="23" title="search keywords in SQL and processing scripts (all owners). Thre are hidden search icons at the right end of each Owner, Project or Thread name."/>

  <img alt=""  src="images/null.gif" width="100" height="20"/>
	<img alt=""  src="images/null.gif"/><img alt=""  style="cursor:pointer; vertical-align:bottom" onclick="refreshView()" src="images/refresh.png"  height="20" title=" Refresh view (after task insert/update/delete)"/>


	<xsl:if test="//keywords">
		<img alt=""  src="images/null.gif"/>
		<div class="round_corners" style="border:1px solid #BBBBBB">
		Search keywords:<span style="background-color:#DDDDDD"><xsl:value-of select="//keywords"/></span>
		 Results:<span style="background-color:#FFFF00"><xsl:value-of select="count(//found)"/></span>
		</div> 	
	</xsl:if>
	<form name="customAnalysisForm" method="post" style="display:inline" >
		<input type="hidden" name="expand_all_flag" value=""/>
		<select style="display:none; visibility:hidden"  multiple="multiple" name="aSelectControl[]" id="aSelectControl">
	      	  <xsl:apply-templates select="//expandedSet/expand"/>
		</select>
		<select style="display:none; visibility:hidden"  multiple="multiple" name="aSelectOriginalControl[]" id="aSelectOriginalControl">
	      	  <xsl:apply-templates select="//expandedSetOriginal/expand"/>
		</select>
		<select style="display:none; visibility:hidden"  multiple="multiple" name="aSearchResults[]" id="aSearchResults">
	      	  <xsl:apply-templates select="//foundSet/found"/>
		</select>
		<textarea style="display:none; visibility:hidden"  name="keywords"><xsl:apply-templates select="//keywords"/></textarea>
		<!--input name="anchor" type="hidden" value=""/-->
	</form>
	<table cellpadding="0" cellspacing="0" width="100%">
	  <tr>
	    <td valign="top">
  		<ul class="tree">
	      	  <xsl:apply-templates select="owner"/>
		</ul>
	    </td>
	  </tr>
	</table>
	<script type="text/javascript"><![CDATA[
		//revealExpanded();

		function setProvenance(scopeValue,scopeName,scopeMainId){
			with(document.provenanceForm){
				provenance_scope.value =scopeValue;
				scope_name.value   =scopeName;
				Custom_Analysis_Id.value=scopeMainId;
				submit();
			}
		}


		function setDBRelationship(scopeValue,scopeName,scopeMainId){
			with(document.dbRelationshipForm){
				provenance_scope.value =scopeValue;
				scope_name.value   =scopeName;
				Custom_Analysis_Id.value=scopeMainId;
				submit();
			}
		}


		function setSearch(scopeValue,scopeName,scopeMainId,initialKeyword){
			with(document.searchForm){
				search_scope.value =scopeValue;
				scope_name.value   =scopeName;
				scope_main_id.value=scopeMainId;
				search_img_obj=document.getElementById("search_scope_img");
				
				if(initialKeyword !=''){
					if(keywords.innerHTML =='%%'){
					  keywords.innerHTML='%'+initialKeyword+'%';
					}
				}

				if(scopeValue=='Project_Owner'){
					 search_img_obj.src='images/owner_plus_small.png';
					 search_img_obj.title='Search only in this `Owner`';
				}
				if(scopeValue=='Project'){
					search_img_obj.src='images/project_plus_small.png';
					search_img_obj.title='Search only in this `Project`';
				}
				if(scopeValue=='Thread_Name'){
					 search_img_obj.src='images/thread_plus_small.png';
					 search_img_obj.title='Search only in this `Thread`';
				}
				rollOn('tip_H_search','');
			}
		}


		function setRename(scopeValue,scopeName,scopeMainId){
			with(document.renameForm){
				search_scope.value =scopeValue;
				scope_name.value   =scopeName;
				scope_main_id.value=scopeMainId;
				search_img_obj=document.getElementById("rename_scope_img");
				

				if(scopeValue=='Project_Owner'){
					 search_img_obj.src='images/owner_plus_small.png';
					 search_img_obj.title='Rename only this `Owner`';
				}
				if(scopeValue=='Project'){
					search_img_obj.src='images/project_plus_small.png';
					search_img_obj.title='Rename only this `Project`';
				}
				if(scopeValue=='Thread_Name'){
					 search_img_obj.src='images/thread_plus_small.png';
					 search_img_obj.title='Rename only this `Thread`';
				}
				rollOn('tip_H_rename','');
			}
		}


   popW=Array();
   function openItem(mainId,stepId){
   	  if(popW[mainId]) popW[mainId].close();
   	  popW[mainId]=window.open('analysis_editItem.php?mainIdName=Custom_Analysis_Id&mainId='+mainId+'&Thread_Step_Number='+stepId,mainId);
   }
		
		function cloneExpandedNodes(){
      var node=document.getElementById("aSelectControl").cloneNode(true);
      node.id="aSelectControl_2";
      my_target=document.getElementById("tip_H_search");
      my_target.appendChild(node);

      var node3=document.getElementById("aSelectControl").cloneNode(true);
      node3.id="aSelectControl_3";
      my_target=document.getElementById("tip_H_rename");
      my_target.appendChild(node3);

      return(true);
		}
		
		// alert('Ready');

	]]></script>

    <form style="display:inline" name="provenanceForm" action="analysis_obtainProvenance.php" method="post" target="_blank">
	    	<input type="hidden" name="provenance_scope" value=""/> <!-- what is the scope of search: Owner , Project, Thread --> 
	    	<input type="hidden" name="scope_name"   value=""/> <!-- this is the name of the Owner or Project or Thread and it's (first Custom_Analysis_Id) --> 
	    	<input type="hidden" name="Custom_Analysis_Id" value=""/> <!-- this is the Custom_Analysis_Id --> 
    </form> 	


    <form style="display:inline" name="dbRelationshipForm" action="analysis_obtainProvenance_DB.php" method="post" target="_blank">
	    	<input type="hidden" name="provenance_scope" value=""/> <!-- what is the scope of search: Owner , Project, Thread --> 
	    	<input type="hidden" name="scope_name"   value=""/> <!-- this is the name of the Owner or Project or Thread and it's (first Custom_Analysis_Id) --> 
	    	<input type="hidden" name="Custom_Analysis_Id" value=""/> <!-- this is the Custom_Analysis_Id --> 
    </form> 	



    <form name="searchForm" action="analysis_search.php" method="post">
	    <div name="tip_H_search" id="tip_H_search" class="tooltip round_corners" >
	    	<img alt=""  style="cursor:pointer" src="images/close_object.png" width="12" border="0" title="close search" onclick="rollOut('tip_H_search')"/>search keywords (use <b>%</b> as wildcard):
	    	<br/>
	    	<textarea name="keywords" style="color:black" cols="40" title="use % as wildcharacter"><xsl:choose><xsl:when test="//keywords"><xsl:value-of select="//keywords"/></xsl:when><xsl:otherwise>%%</xsl:otherwise></xsl:choose></textarea>  
	    	<br/>
	    	<span>restrict to:</span>
	    	<img alt=""  id="search_scope_img" src="images/null.gif" height="20" title="Search only in this" />
	    	<input style="font-size:80%; background-color:#FFFFCC"                                name="search_scope"  type="hidden" value=""/> <!-- what is the scope of search: Owner , Project, Thread --> 
	    	<input style="font-size:80%; background-color:#FFFFFF" title="Search restricted to:"  name="scope_name"    size="18" value=""/> <!-- this is the name of the Owner or Project or Thread and it's (first Custom_Analysis_Id) --> 
	    	<input type="hidden" style="font-size:80%; background-color:#FFFFCC" title="first main id" name="scope_main_id" size="3" value=""/> <!-- this is the name of the Owner or Project or Thread and it's (first Custom_Analysis_Id) --> 
	    	<input type="submit" onclick="return cloneExpandedNodes()" value="search"/>
        <!-- will clone here the aSelectControl  object (a select for with expanded nodes of the flowchart)-->
       
	    </div>
    </form>


    <form name="renameForm" action="analysis_rename.php" method="post">
	    <div name="tip_H_rename" id="tip_H_rename" class="tooltip round_corners" >
	    	<img alt=""  style="cursor:pointer" src="images/close_object.png" width="12" border="0" title="close rename" onclick="rollOut('tip_H_rename')"/>
        <br/><br/> rename current 
	    	<img alt=""  id="rename_scope_img" src="images/null.gif" height="20" title="Rename this level:" />
	    	<input style="font-size:80%; background-color:#FFFFCC"                                name="search_scope"  type="hidden" value=""/> <!-- what is the scope of rename: Owner , Project, Thread --> 
	    	to: 
	    	<br/><textarea style="font-size:80%; background-color:#FFFFFF" title="Rename to this nue value:"  name="scope_name"    rows="1" cols="50" ></textarea> <!-- this is the name of the Owner or Project or Thread and it's (first Custom_Analysis_Id) --> 
	    	<input type="hidden" style="font-size:80%; background-color:#FFFFCC" title="first main id" name="scope_main_id" size="3" value=""/> <!-- this is the name of the Owner or Project or Thread and it's (first Custom_Analysis_Id) --> 
	    	<br/><input type="submit" onclick="return cloneExpandedNodes()" value="rename"/>
        <!-- will clone here the aSelectControl  object (a select for with expanded nodes of the flowchart)-->
       <br/><br/>
	    </div>
    </form>



		<form style="display:inline" name="formOpenEditor" action="analysis_openEditor.php" method="post" target="">
			<input type="hidden" name="parentPrimaryKey"/>
			<input type="hidden" name="parentId"/>
			<input type="hidden" name="parentName"/>
			<input type="hidden" name="parentContent"/>
		</form>


	<script type="text/javascript"><![CDATA[
		 //alert('Ready');
     window.focus();
	]]></script>


  </body>
</html>
</xsl:template>

<xsl:template match="expand">
  <option value="{@name}" selected="selected"><xsl:value-of select="@name"/></option>
</xsl:template>


<xsl:template match="owner">
    <xsl:variable name="ownerClass"><xsl:choose><xsl:when test="//expandedSet/expand/@name=concat('owner_',@name,'_',@mainId)">owner_plus</xsl:when><xsl:otherwise>owner_minus</xsl:otherwise></xsl:choose></xsl:variable>
    <xsl:variable name="ownerImage"><xsl:choose><xsl:when test="//expandedSet/expand/@name=concat('owner_',@name,'_',@mainId)">images/owner_minus_small.png</xsl:when><xsl:otherwise>images/owner_plus_small.png</xsl:otherwise></xsl:choose></xsl:variable>
    <li class="tree">
      <img alt=""  style="cursor:pointer; vertical-align:middle" height="20" src="{$ownerImage}" title="owner expand/collapse" id="{@name}_{@mainId}" onclick="showHide(this)"/>
        <span onmouseover="document.getElementById('search_owner_img_{@mainId}').className='img_reveal';document.getElementById('rename_owner_img_{@mainId}').className='img_reveal'" onmouseout="document.getElementById('search_owner_img_{@mainId}').className='img_hide';document.getElementById('rename_owner_img_{@mainId}').className='img_hide'">
          <span style="cursor:pointer;" class="owner" onclick="showHide(document.getElementById('{@name}_{@mainId}'))" ><xsl:value-of select="@name"/> 
          </span>
            <img alt=""  class="img_hide" id="search_owner_img_{@mainId}" style="cursor:pointer" src="images/search_icon.png" onclick="setSearch('Project_Owner','{@name}',{@mainId},'')" border="0" height="16" title="search keywords in analysis belonging to this owner"/>
            <img alt=""  class="img_hide" id="rename_owner_img_{@mainId}" style="cursor:pointer" src="images/rename_edit.png" onclick="setRename('Project_Owner','{@name}',{@mainId},'')" border="0" height="16" title="rename owner (affects all child projects, threads and tasks)"/>
        </span>
		<ul class="{$ownerClass}" id="{concat('owner_',@name,'_',@mainId)}">
			<xsl:apply-templates select="descendant::project"/>
		</ul>
    </li>
</xsl:template>

<xsl:template match="project" >
    <xsl:variable name="projectClass"><xsl:choose><xsl:when test="//expandedSet/expand/@name=concat('project_',@name,'_',@mainId)">project_plus</xsl:when><xsl:otherwise>project_minus</xsl:otherwise></xsl:choose></xsl:variable>
    <xsl:variable name="projectImage"><xsl:choose><xsl:when test="//expandedSet/expand/@name=concat('project_',@name,'_',@mainId)">images/project_minus_small.png</xsl:when><xsl:otherwise>images/project_plus_small.png</xsl:otherwise></xsl:choose></xsl:variable>
    <li class="tree">
      <img alt=""  style="cursor:pointer; vertical-align:middle; padding-right:5px" height="20" src="{$projectImage}" title="project expand/collapse" id="{@name}_{@mainId}" onclick="showHide(this)"/>
        <span onmouseover="document.getElementById('search_project_img_{@mainId}').className='img_reveal';document.getElementById('rename_project_img_{@mainId}').className='img_reveal'" onmouseout="document.getElementById('search_project_img_{@mainId}').className='img_hide';document.getElementById('rename_project_img_{@mainId}').className='img_hide'">
          <span style="cursor:pointer;" class="project" onclick="showHide(document.getElementById('{@name}_{@mainId}'))" ><xsl:value-of select="@name"/>
          </span>
            <img alt=""  class="img_hide" id="search_project_img_{@mainId}" style="cursor:pointer" src="images/search_icon.png" onclick="setSearch('Project','{@name}',{@mainId},'')" border="0" height="16" title="search keywords in analysis belonging to this project"/>
            <img alt=""  class="img_hide" id="rename_project_img_{@mainId}" style="cursor:pointer" src="images/rename_edit.png" onclick="setRename('Project','{@name}',{@mainId},'')" border="0" height="16" title="rename project (affects all child threads and tasks)"/>
        </span>
		<ul class="{$projectClass}" id="{concat('project_',@name,'_',@mainId)}">
			<xsl:apply-templates select="descendant::thread"/>
		</ul>
    </li>
</xsl:template>

<xsl:template match="thread" >
    <xsl:variable name="threadClass"><xsl:choose><xsl:when test="//expandedSet/expand/@name=concat('thread_',@name,'_',@mainId)">thread_plus</xsl:when><xsl:otherwise>thread_minus</xsl:otherwise></xsl:choose></xsl:variable>
    <xsl:variable name="threadImage"><xsl:choose><xsl:when test="//expandedSet/expand/@name=concat('thread_',@name,'_',@mainId)">images/thread_minus_small.png</xsl:when><xsl:otherwise>images/thread_plus_small.png</xsl:otherwise></xsl:choose></xsl:variable>
    <li class="tree">
      <img alt=""  style="cursor:pointer; vertical-align:middle; transform:rotate(270deg); padding-right:5px" height="20" src="{$threadImage}" title="thread expand/collapse" id="{@name}_{@mainId}" onclick="showHide(this)"/>
        <span onmouseover="document.getElementById('search_thread_img_{@mainId}').className='img_reveal'; document.getElementById('rename_thread_img_{@mainId}').className='img_reveal'; document.getElementById('provenance_thread_img_{@mainId}').className='img_reveal'; document.getElementById('DBextract_thread_img_{@mainId}').className='img_reveal';" 
               onmouseout="document.getElementById('search_thread_img_{@mainId}').className='img_hide'; document.getElementById('rename_thread_img_{@mainId}').className='img_hide'; document.getElementById('provenance_thread_img_{@mainId}').className='img_hide'; document.getElementById('DBextract_thread_img_{@mainId}').className='img_hide';">
          <span style="cursor:pointer;" class="thread" onclick="showHide(document.getElementById('{@name}_{@mainId}'))" ><xsl:value-of select="@name"/>
          </span>
            <img alt=""  class="img_hide" id="search_thread_img_{@mainId}" style="cursor:pointer" src="images/search_icon.png" onclick="setSearch('Thread_Name','{@name}',{@mainId},'')" border="0" height="16" title="search keywords in analysis belonging to this thread"/>
            <img alt=""  class="img_hide" id="rename_thread_img_{@mainId}" style="cursor:pointer" src="images/rename_edit.png" onclick="setRename('Thread_Name','{@name}',{@mainId},'')" border="0" height="16" title="rename thread (affects all child tasks)"/>
					<!-- only for thread obtain the data provenance -->
            <img alt=""   class="img_hide"  id="provenance_thread_img_{@mainId}" style="cursor:pointer" src="images/link.png"        onclick="    setProvenance('Thread_Name','{@name}',{@mainId})" border="0" height="16" title="obtain graph of `data exchange` between tasks in thread (data `provenance`)"/>
            
        </span>


		<ul class="{$threadClass}" id="{concat('thread_',@name,'_',@mainId)}">
			<xsl:apply-templates select="descendant::step"/>
		</ul>
    </li>
</xsl:template>

<xsl:template match="step" >
    <xsl:variable name="searchResultStyle"><xsl:if test="//found/@mainId=@mainId">background-color:#FFFF00</xsl:if></xsl:variable>
    <li class="tree">
      <img alt=""  style="cursor:pointer" height="14" src="images/analysisStep_small_{@stepImportance}.png" title="click to REFRESH - step:{@threadId}, importance:{@stepImportance}, dbId:{@mainId}" onclick="refreshView({@mainId})"/>
          <a name="{@mainId}" onclick="rollOn('tip_H_{@mainId}',{@mainId})" style="cursor:pointer" title="click to see Wiki Summary"><span class="stepId"><xsl:value-of select="@threadId"/></span> <span class="mainIdHide"> <xsl:value-of select="@mainId"/> </span></a> 
         <span class="step">
          <xsl:if test="@iconFile"><img alt=""  src="{@iconFile}" width="20" border="0" title="{@iconComments}"/></xsl:if>

          <xsl:if test="descendant::attachment"> <div style="display:inline; border:1px solid #BBBBBB; padding:3px; background-color:#EEEEEE" title="attachments"><xsl:apply-templates select="descendant::attachment"/></div> </xsl:if>

<!--       <a  target="{@mainId}" title="click to edit and run this analysis step" href="analysis_editItem.php?mainIdName=Custom_Analysis_Id&amp;mainId={@mainId}&amp;Thread_Step_Number={@threadId}"> -->
           <a  title="click to edit and run this analysis step" href="javascript:openItem({@mainId},{@threadId})">
             <span style="{$searchResultStyle}"><xsl:value-of select="@name"/></span>
           </a>
         </span>

        <span onmouseover="document.getElementById('db_sql_step_img_{@mainId}').className='img_reveal'; document.getElementById('r_script_step_img_{@mainId}').className='img_reveal'; document.getElementById('provenance_step_img_{@mainId}').className='img_reveal';  document.getElementById('DBextract_step_img_{@mainId}').className='img_reveal'" 
               onmouseout="document.getElementById('db_sql_step_img_{@mainId}').className='img_hide'; document.getElementById('r_script_step_img_{@mainId}').className='img_hide'; document.getElementById('provenance_step_img_{@mainId}').className='img_hide'; document.getElementById('DBextract_step_img_{@mainId}').className='img_hide'">
          <span style="cursor:pointer;" class="thread" onclick="showHide(document.getElementById('{@name}_{@mainId}'))" ><img width="5" height="13"  src="images/null.gif"/>
          </span>
            <img alt=""   class="img_hide"  id="db_sql_step_img_{@mainId}"   style="cursor:pointer" src="images/sql_join_inner.png" onclick="openEditor('Db_script',{@mainId},'s{@mainId}')" border="0" height="16" title="edit and test the data pre-processing script (database sql) for this step/task"/>
            <img alt=""   class="img_hide"  id="r_script_step_img_{@mainId}" style="cursor:pointer" src="images/R_logo.gif"         onclick="openEditor('R_script' ,{@mainId},'.{@mainId}')" border="0" height="16" title="edit and test the informatics/statistics script (R, perl, python...) for this step/task"/>

          <img width="15" height="13" src="images/null.gif" /><!-- this is a spacer -->
					<!-- only for this step obtain the data provenance -->
					  <xsl:if test="contains(@emptyRscript,'Not Empty')">
              <img alt=""   class="img_hide"  id="provenance_step_img_{@mainId}" style="cursor:pointer" src="images/link.png"        onclick="    setProvenance('Thread_Step_Number','{@threadId}',{@mainId})" border="0" height="16" title="obtain graph of `data exchange` between other tasks/steps and this one (data `provenance`)"/>
            </xsl:if>  
            <xsl:if test="contains(@emptyDBscript,'Not Empty')">
              <img alt=""    style="cursor:pointer" src="images/DB_relation.png" onclick="setDBRelationship('Thread_Step_Number','{@threadId}',{@mainId})" border="0" height="16" title="obtain graph of relationship between DB tables used in thread (data `extraction`)"/>
            </xsl:if>
        </span>


	  <div name="tip_H_{@mainId}_wiki" id="tip_H_{@mainId}_wiki" class="tooltip round_corners">

<xsl:copy-of select="./step_description"/>
	  </div>
	  <div name="tip_H_{@mainId}" id="tip_H_{@mainId}" action="rollOut('tip_H_{@mainId}')" class="tooltip round_corners"></div>
    </li>

</xsl:template>


<xsl:template match="attachment" >
  <xsl:variable name="attachmentIcon"><xsl:choose>
  <xsl:when test="contains(@resultFileType,'.xls')">xls_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.ppt')">pptx_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.doc')">docx_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.png')">img_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.jpg')">img_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.gif')">img_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.pdf')">pdf_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.htm')">html_icon.png</xsl:when>
  <xsl:when test="contains(@resultFileType,'.svg')">svg_logo.jpg</xsl:when>
  <xsl:otherwise>binary.png</xsl:otherwise></xsl:choose></xsl:variable>
	<a href="analysis_getAttachement.pl?Custom_Analysis_Result_Id={@mainResultId}" target="_blank"><img alt=""  src="images/{$attachmentIcon}" border="0" height="16" title="get attached ({@resultFileType})"/></a>	
</xsl:template>


</xsl:stylesheet>
