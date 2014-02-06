    <br><hr style="width:50%"><br>
  	<table align="center" class="app_footer">
  		<tr>
        <td>&copy; 2012-2014 SLRI Pawson Lab,</td>
        <td> &copy; 2012-2014 DTU/CSIG Linding Lab</td>
        <td style="color:#888888; font-size:80%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Best viewed in <img  alt="" style="vertical-align:middle" width="20" src="images/chrome.png">Chrome</td>
      </tr>  
    </table>
    <form name="formFocus" style="display:inline" action="" ></form>

    <script  type="text/javascript">

      <!-- will try to force focus on a different browser window -->
    	<?php if(trim($_REQUEST['command'])=="backFocus"){ print 'if(BrowserDetect.browser !="Chrome") {alert("Ready!")} else {document.formFocus.submit()}';} ?>

      <!-- will close the specified browser window -->
    	<?php if(trim($_REQUEST['command'])=="closeChild"){ print 'crtTarget=open("app_footer_closeWindow.php","'.trim($_REQUEST['targetName']).'")';} ?>

      window.focus();
    </script>
  </body>    
</html>
