<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');

$auth_users = "1,2,3,20";
check_auth($auth_users, $site_root . '/admin');


unset($_SESSION['olv_veri_col']);
unset($_SESSION['olv_veri_type']);


if(isset($_GET['who'])){
    $_SESSION['olv_veri_who'] = $_GET['who'];
}else{
    header('Location: index.php');
    die();
}

$query = sprintf("SELECT c.colid, c.colname "
                . "FROM college c JOIN department d ON d.colid = c.colid JOIN lecturer l ON d.deptid = l.deptid WHERE l.lectid = %s ",GetSQLValueString($_SESSION['uid'], 'text')); 
$college = mysql_query($query, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);

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
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3>
                                        <i class="icon-reorder"></i><?= ($_SESSION['olv_veri_who'] == 'pros')? 'Prospective ' : 'Returning '?> Student Departmental Clearance Module
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form method="post" action="process.php" target="_tab" class="form form-horizontal" >
                                        <div class="row-fluid">
                                            <div class="span6">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Select College</label>
                                                    <div class="controls controls-row">
                                                        <select name="colid" required="">
                                                            <option value="">--Choose--</option>
                                                            <?php do { ?>
                                                                <option value="<?= $row_college['colid'] ?>"><?= $row_college['colname'] ?></option>
                                                            <?php }while ($row_college = mysql_fetch_assoc($college)); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="span6">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">&nbsp;</label>
                                                    <div class="controls controls-row">
                                                        <input class="btn btn-brown" type="submit" name="select" value="Proceed">
                                                        <input class="btn btn-blue" type="submit" name="select" value="Proceed DE">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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



