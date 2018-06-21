<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



$auth_users = "4";
check_auth($auth_users, $site_root);

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM programme ORDER BY progname ASC");
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_proramme = mysql_fetch_assoc($prog);

mysql_select_db($database_tams, $tams);
$query = "SELECT * FROM course "
        . "WHERE type = 'General' "
        . "AND csid LIKE 'EDU___' "
        . "OR csid LIKE 'GNS___' "
        . "OR csid LIKE 'ENT___' "
        . "OR csid LIKE 'EDU____' "
        . "OR csid LIKE 'GNS____' "
        . "OR csid LIKE 'ENT____' "
        . "ORDER BY csid ASC";
$course = mysql_query($query, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root . '/ict');
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
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        General University Exam Result Page 
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <form class="form-horizontal form-striped" method="POST" action="printresult.php" target="_blank">
                                            <div class="control-group">
                                                <label class="control-label" for="progid"><?= $programme_name?></label>
                                                <div class="controls">
                                                    <select name="progid" class="input-xlarge" required>
                                                        <option value="">-Choose-</option>
                                                        <?php do { ?>
                                                            <option value="<?php echo $row_proramme['progid'] ?>"><?php echo ucfirst($row_proramme['progname']) ?></option>
                                                        <?php }
                                                        while ($row_proramme = mysql_fetch_assoc($prog)) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="sesid">Session</label>
                                                <div class="controls">
                                                    <select name="sesid" class="input-xlarge" required>
                                                        <option value="">-Choose-</option>
                                                        <?php do { ?>
                                                            <option value="<?php echo $row_session['sesid'] ?>"><?php echo $row_session['sesname'] ?></option>
                                                        <?php }
                                                        while ($row_session = mysql_fetch_assoc($session)) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="csid">Course</label>
                                                <div class="controls">
                                                    <select name="csid" class="input-xlarge" required>
                                                        <option value="">-Choose-</option>
                                                        <?php do { ?>
                                                            <option value="<?php echo $row_course['csid'] ?>"><?php echo $row_course['csid'] . '  -  ' . $row_course['csname'] ?></option>
                                                        <?php }
                                                        while ($row_course = mysql_fetch_assoc($course)) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-actions">
                                                <button class="btn btn-primary" type="submit">Generate</button>
                                                <button class="btn" type="button">Cancel</button>
                                            </div>
                                        </form>
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