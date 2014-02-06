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
		function rollOn(lyrName) {			
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
		    	
				lyr.style.position='absolute';
				lyr.style.left = (xCoord + 20 + offset_Left)+"px";
				lyr.style.top = (yCoord - 15 + offset_Top)+"px";
				lyr.style.visibility = 'visible';
				lyr.style.zIndex =999;
		}
		
		// function to make tool tip hidden
		// lyr is NOT an object
		function rollOut(lyrName) {
			if(br == "ns" && ver <= 4) { 		
				lyr = document.layers[lyrName];
		    		if (!lyr) {return;}
				lyr.visibility = 'hidden';
			} else {
				lyr = document.getElementById(lyrName);
		    if (!lyr) {return;}
				lyr.style.visibility='hidden';
			}
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
			// this catches NS6 & IE 5+
		        else if (document.getElementById){
		        xCoord = e.clientX;
		        yCoord = e.clientY;
			}
		}
		
		// start tracking mouse move events
		document.onmousemove = checkwhere;
		if(document.captureEvents) {document.captureEvents(Event.MOUSEMOVE);}
		
		
		/////////////////////end of tool tip code
		
		
		
			function forceSort(newCol){
				with (document.editForm){
					if (columnName.value != newCol){
						columnName.value=newCol;
						sortOrder.value="";
					}
					//alert(tableName.value);
					//alert(columnName.value);
					submit();
				}
			}
			


			
			markedIds=new Array();
			function markMultiple(o,newClassName){					
				// unmark previous if exists
				principalId=o.getAttribute("principalId"); // this works for both MSIE and Firefox
				alert(principalId);
				if ( markedIds[principalId]){
					o.className=markedIds[principalId];
					delete markedIds[principalId];
				} else {
					markedIds[principalId]=o.className;
					o.className=newClassName;
				}
			}

			// handling the marking/unmarking of a cell or a row
			crtMarkedOneObject=null;
			prevMarkedOneClassName="";
			function markOne(o,newClassName){
				// unmark previous if exists
				if (prevMarkedOneClassName != ""){
					if (crtMarkedOneObject.className == prevMarkedOneClassName ){
						return; // selected the same object more than once
					}
					crtMarkedOneObject.className=prevMarkedOneClassName;
				}

				// mark new one
				prevClassName=o.className;
				o.className=newClassName;

				// update global
				crtMarkedOneObject=o;
				prevMarkedOneClassName=prevClassName;
			}



		