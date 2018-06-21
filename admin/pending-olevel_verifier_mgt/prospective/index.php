<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */


$auth_users = "1,20,21,22,23,24";
check_auth($auth_users, $site_root.'/admin');

unset($_SESSION['olv_veri_col']);

$query = sprintf("SELECT colid, colname "
                . "FROM college ");
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
                                        Prospective Student O'Level Verification Management Page
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="my_treated.php" target="_tab">My Treated</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <form method="post" action="process.php" target="_tab" class="form form-horizontal" >
                                        <div class="row-fluid">
                                            <div class="span4">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Select College</label>
                                                    <div class="controls controls-row">
                                                        <select name="colid" required="">
                                                            <option value="">--Choose--</option>
                                                            <?php do { ?>
                                                                <option value="<?= $row_college['colid'] ?>"><?= $row_college['colname'] ?></option>
                                                            <?php }
                                                            while ($row_college = mysql_fetch_assoc($college)); ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="span4">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">&nbsp;</label>
                                                    <div class="controls controls-row">
                                                        <input class="btn btn-brown" type="submit" name="select" value="Proceed">
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

