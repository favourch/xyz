<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');



$auth_users = "1,20,21,22,23,24";
check_auth($auth_users, $site_root . '/admin');



if(isset($_POST['colid']) && $_POST['colid'] != ''){
    
   $_SESSION['olv_veri_col'] = $_POST['colid'];
    
}


$currentPage = $_SERVER["PHP_SELF"];

$maxRows_Rsall = 25;
$pageNum_Rsall = 0;
if (isset($_GET['pageNum_Rsall'])) {
    $pageNum_Rsall = $_GET['pageNum_Rsall'];
}
$startRow_Rsall = $pageNum_Rsall * $maxRows_Rsall;
//***********************************************


$query = sprintf("SELECT * FROM olevel_veri_data olv, prospective p, programme pg, department d, college c "
                . "WHERE olv.stdid = p.jambregid "
                . "AND p.progoffered = pg.progid "
                . "AND pg.deptid = d.deptid "
                . "AND d.colid = c.colid  "
                . "AND c.colid = %s "
                . "AND olv.treated = 'No' "
                . "AND olv.usertype = 'pros' "
                . "ORDER BY date ASC ",
                GetSQLValueString($_SESSION['olv_veri_col'], 'int'));
$query_limit_verify = sprintf("%s LIMIT %d, %d", $query, $startRow_Rsall, $maxRows_Rsall);

$verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());
$verify_row = mysql_fetch_assoc($verify);
$verify_row_num = mysql_num_rows($verify);

if (isset($_GET['totalRows_Rsall'])) {
    
    $totalRows_Rsall = $_GET['totalRows_Rsall'];
}
else {
    
    $all_Rsall = mysql_query($query);
    $totalRows_Rsall = mysql_num_rows($all_Rsall);
}
$totalPages_Rsall = ceil($totalRows_Rsall / $maxRows_Rsall) - 1;

$queryString_Rsall = "";
if (!empty($_SERVER['QUERY_STRING'])) {
    $params = explode("&", $_SERVER['QUERY_STRING']);
    $newParams = array();
    foreach ($params as $param) {
        if (stristr($param, "pageNum_Rsall") == false &&
                stristr($param, "totalRows_Rsall") == false) {
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) {
        $queryString_Rsall = "&" . htmlentities(implode("&", $newParams));
    }
}
$queryString_Rsall = sprintf("&totalRows_Rsall=%d%s", $totalRows_Rsall, $queryString_Rsall);


if (isset($_POST['submit'])) {

    $cur_detais = $_GET['id'];
    $cur_ordid = $_GET['ordid'];

    if ($_POST['submit'] == 'Yes') {

        $msg = "<p style='color:green'>Your Olevel Result has been PRINTED by the ICT <br/> "
                . "and it is being forwarded to Admission's Office for Verification .</p>";

        mysql_query('BEGIN', $tams);

        $query = sprintf("UPDATE `olevel_veri_data` "
                . "SET `treated` = 'Yes', approve = 'Yes', "
                . "return_msg = %s, date_treated=%s, who=%s "
                . "WHERE id = %s", 
                GetSQLValueString($msg, 'text'), 
                GetSQLValueString(date('Y-m-d H:i:s'), 'date'), 
                GetSQLValueString($_SESSION['uid'], 'text'), 
                GetSQLValueString($_GET['id'], 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());

        $query = sprintf("UPDATE `olevelverifee_transactions` "
                . "SET `pay_used` = 'Yes' "
                . "WHERE status='APPROVED' "
                . "AND can_no = %s AND ordid=%s", 
                GetSQLValueString($_GET['stdid'], 'text'), 
                GetSQLValueString($cur_ordid, 'text'));
        $verify2 = mysql_query($query, $tams) or die(mysql_error());

        if ($verify2 && $verify1) {
            mysql_query('COMMIT', $tams);
        }
        else {
            mysql_query('ROLLBACK', $tams);
        }
    }
    elseif ($_POST['submit'] == 'No') {

        $msg = "<p style='color:red'>ICT could NOT PRINT your O'Level Result<br/>"
                . "Your Card details may be wronge. Please re-submit.</p>";

        mysql_query('BEGIN', $tams);

        $query = sprintf("UPDATE `olevel_veri_data` "
                . "SET `treated` = 'Yes', approve = 'No', "
                . "return_msg = %s, date_treated=%s, who=%s "
                . "WHERE id = %s",
                GetSQLValueString($msg, 'text'), 
                GetSQLValueString(date('Y-m-d H:i:s'), 'date'), 
                GetSQLValueString($_SESSION['uid'], 'text'), 
                GetSQLValueString($cur_detais, 'text'));
        $verify1 = mysql_query($query, $tams) or die(mysql_error());

        $query = sprintf("UPDATE `olevelverifee_transactions` "
                . "SET `pay_used`='Yes' "
                . "WHERE status='APPROVED' "
                . "AND can_no = %s AND ordid=%s", 
                GetSQLValueString($_GET['stdid'], 'text'), 
                GetSQLValueString($cur_ordid, 'text'));

        $verify2 = mysql_query($query, $tams) or die(mysql_error());

        if ($verify2 && $verify1) {
            mysql_query('COMMIT', $tams);
        }
        else {
            mysql_query('ROLLBACK', $tams);
        }
    }

    
    //header('Location : process.php');
    header('Location: process.php');
    exit();
    
}


    
$query = sprintf("SELECT colid, colname, coltitle "
                . "FROM college WHERE colid = %s", 
        GetSQLValueString($_SESSION['olv_veri_col'], 'int'));
$college = mysql_query($query, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/ict');
}
?>
<!doctype html>
<html>
<?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
<?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
<?php include INCPATH . "/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
<?php include INCPATH . "/page_header.php" ?>
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
                                        </div>
                                        <br/>-->
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Prospective Student O'Level Verification Page (<?= $row_college['coltitle']?>)
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="my_treated.php" target="_new">My Treaded</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <div class='row-fluid'>
<?php if ($verify_row_num > 0) { ?>
                                            <div class="span12">
                                                <table class="table  table-condensed table-striped table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Matric No</th>
                                                            <th>Exam Type</th>
                                                            <th>Exam Year</th>
                                                            <th>Exam No</th>
                                                            <th>Card S/N</th>
                                                            <th>Card Pin</th>
                                                            <th>Printed</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
    <?php do { ?>
                                                            <tr>
                                                                <td>
                                                                    <a  target="_blank" href="<?php echo (strlen($verify_row['stdid']) == 10) ? '../../../student/profile.php?stid=' . $verify_row['stdid'] : '../../../admission/viewform.php?stid=' . $verify_row['stdid'] ?>">
        <?php echo $verify_row['stdid']; ?>
                                                                    </a>    
                                                                </td>
                                                                <td><?= $verify_row['exam_type']; ?></td>
                                                                <td><?php echo $verify_row['exam_year']; ?></td>
                                                                <td><input type="text" class="input-small" readonly value="<?php echo $verify_row['exam_no']; ?>"></td>
                                                                <td><input type="text" class="input-medium" readonly value="<?php echo $verify_row['card_no']; ?>"></td>
                                                                <td><input type="text" class="input-medium" readonly value="<?php echo $verify_row['card_pin']; ?>"></td>
        <?php if ($verify_row['treated'] == 'Yes') { ?>
                                                                    <td><?php echo "Treated -" . $verify_row['approve'] . "-" ?></td>
                                                            <?php
                                                            }
                                                            else {
                                                                ?>
                                                            <form name='form1' method="POST" action="<?php echo urldecode(' process.php?id=' . $verify_row['id'] . '&stdid=' . $verify_row['stdid'] . '&ordid=' . $verify_row['ordid']) ?>">
                                                                <td><input type="submit" name='submit' class="btn btn-small btn-green" value="Yes"/>&nbsp;&nbsp; | &nbsp;&nbsp;<input type="submit" class="btn btn-small btn-red" name='submit' value="No"/></td>
                                                            </form>
        <?php } ?>
                                                        </tr>
    <?php }
    while ($verify_row = mysql_fetch_assoc($verify));
    ?>
                                                    </tbody>
                                                </table> 
                                            </div>
                                                        <?php }
                                                        else { ?>
                                            <div class="alert" style="text-align: center">No pending O'Level Result  </div>
<?php } ?>
                                    </div>
                                    <p>&nbsp;</p>
                                    <div class="row-fluid">
                                        <table  class="table table-condensed table-striped">
                                            <tr width="50" align="center">
                                                <td style="text-align: center"><a class="btn btn-small btn-blue" href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, max(0, $pageNum_Rsall - 1), $queryString_Rsall); ?>"><i class='icon-fast-backward'></i> Prev</a></td>
                                                <td style="text-align: center"><?php echo 'Page ' . ($pageNum_Rsall + 1) . " of " . ($totalPages_Rsall + 1); ?></td>
                                                <td style="text-align: center"><a class="btn btn-small btn-blue" href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, min($totalPages_Rsall, $pageNum_Rsall + 1), $queryString_Rsall); ?>">Next <i class='icon-fast-forward'></i></a></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>   
                        </div>
                    </div>
                </div>
            </div>          
        </div>
<?php include INCPATH . "/footer.php" ?>
    </body>
</html>

