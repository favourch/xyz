<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$MM_authorizedUsers = "1,20";
$MM_donotCheckaccess = "false";

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
        if (($strUsers == "") && false) {
            $isValid = true;
        }
    }
    return $isValid;
}

$MM_restrictGoTo = "../index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
    $MM_qsChar = "?";
    $MM_referrer = $_SERVER['PHP_SELF'];
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);
    exit;
}



$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    
    $insertSQL = sprintf("INSERT INTO `session` ( sesname, tnumin, tnumax) "
            . "VALUES ( %s, %s, %s)", 
            GetSQLValueString($_POST['sesname'], "text"), 
            GetSQLValueString($_POST['tnumin'], "int"), 
            GetSQLValueString($_POST['tnumax'], "int"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
    
    if ($result1){
        
        $insertGoTo = ( isset($_GET['success']) ) ? $insertGoTo : $insertGoTo . "?success";
        
    }else{
        
        $insertGoTo = ( isset($_GET['error']) ) ? $insertGoTo : $insertGoTo . "?error";
    }
    
    $insertGoTo = "index.php";
    
    if (isset($_SERVER['QUERY_STRING'])) {
        
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

$updateSQL1 = '';
$updateSQL2 = '';

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'ses') {
        
        $updateSQL1 = sprintf("UPDATE session SET status = %s",
                              GetSQLValueString('FALSE', "text"));
        $updateSQL2 = sprintf("UPDATE session "
                            . "SET status = %s WHERE sesid = %s", 
                            GetSQLValueString($_GET['status'], "text"), 
                            GetSQLValueString($_GET['id'], "text"));
        
                   
    }
    elseif ($_GET['action'] == 'reg') {
        $updateSQL1 = sprintf("UPDATE session SET registration = %s", GetSQLValueString("FALSE", "text"));
        $updateSQL2 = sprintf("UPDATE session "
                            . "SET registration = %s WHERE sesid = %s", 
                            GetSQLValueString($_GET['status'], "text"), 
                            GetSQLValueString($_GET['id'], "text"));

        
    }
    elseif($_GET['action'] == 'adm') {
        $updateSQL1 = sprintf("UPDATE session SET admission = %s", GetSQLValueString("FALSE", "text"));
        $updateSQL2 = sprintf("UPDATE session "
                            . "SET admission = %s WHERE sesid = %s", 
                            GetSQLValueString($_GET['status'], "text"), 
                            GetSQLValueString($_GET['id'], "text"));
    }
    elseif($_GET['action'] == 'eps'){
        $updateSQL1 = sprintf("UPDATE session SET epass = %s", GetSQLValueString("FALSE", "text"));
        $updateSQL2 = sprintf("UPDATE session "
                            . "SET epass = %s WHERE sesid = %s", 
                            GetSQLValueString($_GET['status'], "text"), 
                            GetSQLValueString($_GET['id'], "text"));
    }
    elseif($_GET['action'] == 'sem'){
        $updateSQL1 = sprintf("UPDATE session SET semester = %s", GetSQLValueString("second", "text"));
        $updateSQL2 = sprintf("UPDATE session "
                            . "SET semester = %s WHERE sesid = %s", 
                            GetSQLValueString($_GET['status'], "text"), 
                            GetSQLValueString($_GET['id'], "text"));
        
    }else{
        $insertGoTo = 'index.php';
        //header(sprintf("Location: %s", $insertGoTo));
    }
    
   
    
    
        mysql_select_db($database_tams, $tams);
        $Result1 = mysql_query($updateSQL1, $tams) or die(mysql_error());
        $Result2 = mysql_query($updateSQL2, $tams) or die(mysql_error()); 
        
         //die(var_dump($Result2 ));
         //die(var_dump($Result1 ));
         
        
    $insertGoTo = "index.php";
    
    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
//        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}



mysql_select_db($database_tams, $tams);
$query_rsses = "SELECT * FROM session ORDER BY sesid DESC";
$rsses = mysql_query($query_rsses, $tams) or die(mysql_error());
$row_rsses = mysql_fetch_assoc($rsses);
$total_rowses = mysql_num_rows($rsses);




if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";



$page_title = "Tasued";
?>
<!doctype html>
<html>
<?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
<?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
    <?php include INCPATH."/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
        <?php include INCPATH."/page_header.php" ?>
                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="college.php">College</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>
                    <br/>
                    <div class="span6">
<?php statusMsg(); ?>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-calendar"></i>
                                        Academic Session Management
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div id="accordion2" class="accordion">
                                        <div class="accordion-group">
                                            <div class="accordion-heading">
                                                <a href="#collapseOne" data-parent="#accordion2" data-toggle="collapse" class="accordion-toggle collapsed">
                                                    <i class="icon-plus"></i> Create New Session
                                                </a>
                                            </div>
                                            <div class="accordion-body collapse" id="collapseOne" style="height: 0px;">
                                                <div class="accordion-inner">

                                                    <form class="form-horizontal form-bordered form-validate" action="<?php echo $editFormAction; ?>s" method="post">
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Session Name </label>
                                                            <div class="controls">
                                                                <input name="sesname" type="text"  class="input-xlarge" required=""/>
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Mininum Unit</label>
                                                            <div class="controls">
                                                                <input name="tnumin" type="number" class="input-medium"  min="0"  max="500" required="" />
                                                            </div>
                                                        </div>
                                                        <div class="control-group">
                                                            <label class="control-label" for="textfield">Maximum Unit</label>
                                                            <div class="controls">
                                                                <input name="tnumax" type="number" class="input-medium"  min="0"  max="500" required="" />
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="MM_insert" value="form1" />
                                                        <div class="form-actions">
                                                            <input type="submit" value="Create New Session" class="btn btn-primary" >
                                                            <button class="btn" type="button">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p>&nbsp;</p>
                                    <table class="table table-condensed  dataTable table-condensed table-striped">
                                        <thead>
                                            <tr>
                                                <th width="10%">Session Name</th>
                                                <th width="5%">TNU Min</th>
                                                <th width="5%">TNU Max</th>
                                                <th>Session</th>
                                                <th>Admission</th>
                                                <th>Registration</th>
                                                <th>Semester</th>
                                                <th>ePass</th>
                                                <th>&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if ($total_rowses > 0) { // Show if recordset not empty  ?>
                                        <?php do { ?>
                                            <tr>
                                                <td><?php echo $row_rsses['sesname']; ?></td>
                                                <td><?php echo $row_rsses['tnumin']; ?></td>
                                                <td><?php echo $row_rsses['tnumax']; ?></td>
                                                <td><?= ($row_rsses['status'] == 'TRUE') ? '<span style="color:green">Open<span>' : '<span style="color:red">Close<span>'; ?></td>
                                                <td><?= ($row_rsses['admission'] == 'TRUE') ? '<span style="color:green">Open<span>' : '<span style="color:red">Close<span>'; ?></td>
                                                <td><?= ($row_rsses['registration'] == 'TRUE') ? '<span style="color:green">Open<span>' : '<span style="color:red">Close<span>'; ?></td>
                                                <td><?= ($row_rsses['semester'] == 'first') ? "<span style='color:green'>{$row_rsses['semester']}<span>" : "<span style='color:brown'>{$row_rsses['semester']}<span>"; ?></td>
                                                <td><?= ($row_rsses['epass'] == 'TRUE') ? "<span style='color:green'>Open<span>" : "<span style='color:red'>Close<span>"; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a href="editsession.php?sid=<?php echo $row_rsses['sesid']; ?>">Edit Session</a>
                                                            </li>
                                                            <li>
                                                                <?php if($row_rsses['status'] == 'TRUE'){?>
                                                                <a style="color:brown" href='index.php?action=ses&status=FALSE&id=<?= $row_rsses['sesid']?>'>Close Session</a>
                                                                <?php }else{?>
                                                                <a style="color:green" href='index.php?action=ses&status=TRUE&id=<?= $row_rsses['sesid']?>'>Open Session</a>
                                                                <?php }?>
                                                            </li>
                                                            <li>
                                                                <?php if($row_rsses['semester'] == 'first'){?>
                                                                <a style="color:blue" href='index.php?action=sem&status=second&id=<?= $row_rsses['sesid']?>'>Second Semester</a>
                                                                <?php }else{?>
                                                                <a style="color:green" href='index.php?action=sem&status=first&id=<?= $row_rsses['sesid']?>'>First Semester</a>
                                                                <?php }?>
                                                            </li>
                                                            <li>
                                                                <?php if($row_rsses['admission'] == 'TRUE'){?>
                                                                <a style="color:red" href='index.php?action=adm&status=FALSE&id=<?= $row_rsses['sesid']?>'>Close Admission</a>
                                                                <?php }else{?>
                                                                <a style="color:green" href='index.php?action=adm&status=TRUE&id=<?= $row_rsses['sesid']?>'>Open Admission</a>
                                                                <?php }?>
                                                            </li>
                                                            <li>
                                                                <?php if($row_rsses['registration'] == 'TRUE'){?>
                                                                <a style="color:red" href='index.php?action=reg&status=FALSE&id=<?= $row_rsses['sesid']?>'>Close Registration</a>
                                                                <?php }else{?>
                                                                <a style="color:green" href='index.php?action=reg&status=TRUE&id=<?= $row_rsses['sesid']?>'>Open Registration</a>
                                                                <?php }?>
                                                            </li>
                                                            <li>
                                                                <?php if($row_rsses['epass'] == 'TRUE'){?>
                                                                <a style="color:red" href='index.php?action=eps&status=FALSE&id=<?= $row_rsses['sesid']?>'>Close Exam Pass</a>
                                                                <?php }else{?>
                                                                <a style="color:green" href='index.php?action=eps&status=TRUE&id=<?= $row_rsses['sesid']?>'>Open Exam Pass</a>
                                                                <?php }?>
                                                            </li>
                                                            <li>
                                                                <a href="#">Delete</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                                <?php } while ($row_rsses = mysql_fetch_assoc($rsses)); ?>
                                            <?php }  ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
<?php include INCPATH."/footer.php" ?>
    </body>
</html>

