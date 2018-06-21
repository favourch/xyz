<?php  

if (!isset($_SESSION)) {
  session_start();
}

$MM_authorizedUsers = "11";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}
mysql_select_db($database_tams, $tams);
$query_prog = 'SELECT * FROM programme ORDER BY progid ASC' ;
$rsProg = mysql_query($query_prog, $tams) or die(mysql_error());
$row_query_prog = mysql_fetch_assoc($rsProg);
$totalRows_query_prog = mysql_num_rows($rsProg);
 
function getProgChoice($pid, $choice, $tams){
    
    if($choice == 1){
        $query_prog_count = sprintf('SELECT count(progid1) as pg'
                                    . ' FROM prospective'
                                    . ' WHERE progid1 = %s', $pid);
    }elseif($choice == 2){
        $query_prog_count = sprintf('SELECT count(progid2) as pg'
                                . ' FROM prospective'
                                . ' WHERE progid2 = %s', $pid);
    }
    $rsProg = mysql_query($query_prog_count, $tams) or die(mysql_error());
    $row_prog_count = mysql_fetch_assoc($rsProg);
    
    return $row_prog_count['pg'];
}

function adm_status($pid, $tams){
    $query_adm_count = sprintf("SELECT count(adminstatus) as adm"
                                    . " FROM prospective"
                                    . " WHERE adminstatus ='Y'"
                                    . " AND (progid2 = %s"
                                    . " OR progid1 = %s)", $pid, $pid);
    $rsAdm = mysql_query($query_adm_count, $tams) or die(mysql_error());
    $row_adm_count = mysql_fetch_assoc($rsAdm);
    
    return $row_adm_count['adm'];
}



require_once('../path.php'); 


if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php  ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" /> 
</head>

<body data-layout-sidebar="fixed" data-layout-topbar="fixed">
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Prospective Registration Summary Page <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td>
            <div id="CollapsiblePanel1" class="CollapsiblePanel" >
                <div class="CollapsiblePanelTab" tabindex="0">Prospective Registration By Programme</div>
                <div class="CollapsiblePanelContent">
                    <fieldset>
                        <legend>Prospective Registration By Programme </legend>
                        <p>&nbsp;</p>
                        <table width="632" border="0" align="center" class="table table-bordered table-condensed table-striped">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Programme</th>
                                    <th>1st</th>
                                    <th>2nd</th>
                                    <th>Admitted</th>
                                    <th>Acceptance</th>
                                    <th>Sch.Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($totalRows_query_prog > 0){
                                $total_stdnt = 0;
                                $i=1;
                                do {
                                    
                                    ?>
                                    <tr>
                                        <td><?php echo $i++;?></td>
                                        <td><?php echo $row_query_prog['progname']?></td>
                                        <td><a href="#"><?php echo getProgChoice($row_query_prog['progid'],1, $tams)?></a></td>
                                        <td><a href="#"><?php echo getProgChoice($row_query_prog['progid'],2, $tams)?></a></td> 
                                        <td><a href="#"><?php echo adm_status($row_query_prog['progid'], $tams)?></a></td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                <?php }while($row_query_prog = mysql_fetch_assoc($rsProg));?>
                                <tr>
                                    <th colspan="7">Total </th>
                                    
                                </tr>
                                <?php }else{?>
                                <tr>
                                    <td colspan="3" style="color: red">Sorry No Record Available </td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                        <p>&nbsp;</p>
                    </fieldset>
                </div>
            </div>
            <p>&nbsp;</p>
            </td>
        </tr>
    </table>
    <script type="text/javascript">
        var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
    </script>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd -->
</html>

