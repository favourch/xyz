<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/*-----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 **------------------------------------------------
 */


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
    $insertSQL = sprintf("INSERT INTO college (colid, colname, colcode, coltitle, remark) "
                        . "VALUES (%s, %s, %s, %s, %s)",
                        GetSQLValueString($_POST['colid'], "int"),
                        GetSQLValueString($_POST['colname'], "text"),
                        GetSQLValueString($_POST['colcode'], "text"),
                        GetSQLValueString($_POST['coltitle'], "text"),
                        GetSQLValueString($_POST['remark'], "text"));
    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

    
    $insertGoTo = "index.php";
    if ($Result1)
        $insertGoTo = ( isset($_GET['success']) ) ? $insertGoTo : $insertGoTo . "?success";
    else
        $insertGoTo = ( isset($_GET['error']) ) ? $insertGoTo : $insertGoTo . "?error";

    if (isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

$maxRows_rscol = 10;
$pageNum_rscol = 0;

if (isset($_GET['pageNum_rscol'])) {
    
    $pageNum_rscol = $_GET['pageNum_rscol'];
}

$startRow_rscol = $pageNum_rscol * $maxRows_rscol;

mysql_select_db($database_tams, $tams);
$query_rscol = "SELECT * FROM college";
$query_limit_rscol = sprintf("%s LIMIT %d, %d", $query_rscol, $startRow_rscol, $maxRows_rscol);
$rscol = mysql_query($query_limit_rscol, $tams) or die(mysql_error());
$row_rscol = mysql_fetch_assoc($rscol);

if (isset($_GET['totalRows_rscol'])) {
    
    $totalRows_rscol = $_GET['totalRows_rscol'];
}
else {
    
    $all_rscol = mysql_query($query_rscol);
    $totalRows_rscol = mysql_num_rows($all_rscol);
}

$totalPages_rscol = ceil($totalRows_rscol / $maxRows_rscol) - 1;

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}









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
<!--                    <div class="breadcrumbs">
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
                    </div>-->

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        <?= $college_name;?>s in the <?= $institution?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <p>
                                        <?= $college_page_top_content; ?>
                                    </p>
                                    
                                    <?php if ($totalRows_rscol > 0){?>
                                    
                                    <table class="table table-hover tale-nomargin table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="2">List of <?= $college_name; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php do { ?>
                                                <tr>
                                                    <td><a href="college.php?cid=<?php echo $row_rscol['colid']; ?>"><?= $row_rscol['colname']; ?></a></td>
                                                    <td>
                                                        <?php $access = array(1,2);if( in_array(getAccess(),$access) && ( getAccess() == 1 || getSessionValue('cid') == $row_rscol['colid']) ){?>
                                                        <a href="coledit.php?cid=<?php echo $row_rscol['colid'];?>" class="btn btn-small"> <i class="icon-cogs"></i> Edit</a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php }
                                            while ($row_rscol = mysql_fetch_assoc($rscol)); ?>
                                        </tbody>  
                                    </table>
                                    
                                    <?php }else{?>
                                    
                                    <div class="alert alert-danger"> No <?= $college_name; ?> created yet </div>
                                    <?php }?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

