<?php require_once('../../path.php');

if (!isset($_SESSION)) {
    session_start();
}

$csid = -1;
if(isset($_GET['crs'])){
    $csid = $_GET['crs'];
}

$sesid = -1;
if(isset($_GET['sid'])){
    $sesid = $_GET['sid'];
}

$pid = -1;
if(isset($_GET['pid'])){
    $pid = $_GET['pid'];
}

$auth_users = "1,4,20,21,22,28";
check_auth($auth_users, $site_root.'/admin');


$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);


$query = sprintf("SELECT * FROM programme ORDER BY progname ASC");
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_proramme = mysql_fetch_assoc($prog);


$query = "SELECT distinct(csid), csname, unit, status, level, semester, type, catid, deptid, level, continued FROM course "
                . "WHERE type = 'General' "
                . "AND (csid LIKE 'EDU___' "
                . "OR csid LIKE 'GNS___' "
                . "OR csid LIKE 'ENT___' "
              //  . "OR csid LIKE 'EDU____' "
              //  . "OR csid LIKE 'GNS____' "
              //  . "OR csid LIKE 'ENT____' "
                 . "OR csid LIKE 'VOS___' )"
               //   . "OR csid LIKE 'VOS____' "
                . "ORDER BY csid ASC";
$course = mysql_query($query, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);

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
                                            General University Exam Result Page
                                        </h3>
                                    </div>
                                    <div class="box-content">
                                        <form class="form-horizontal form-bordered" method="POST" action="printresult.php" target="_blank">
                                            <div class="control-group">
                                                <label class="control-label" for="password">Session</label>
                                                <div class="controls">
                                                    <select name="sesid" onchange="sesfilt(this)" required>
                                                        <option value="">-Choose-</option>
                                                        <?php do { ?>
                                                            <option <?= ($row_session['sesid'] == $sesid)? 'selected' : ''; ?> value="<?php echo $row_session['sesid'] ?>"><?php echo $row_session['sesname'] ?></option>
                                                        <?php }
                                                        while ($row_session = mysql_fetch_assoc($session)) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label" for="textfield"><?= $programme_name?></label>
                                                <div class="controls">
                                                    <select name="progid" style="width: 300px"  onchange="progfilt(this)" required>
                                                        <option value="">-Choose-</option>
                                                        <?php do { ?>
                                                        <option <?= ($row_proramme['progid'] == $pid)? 'selected' : ''; ?>  value="<?php echo $row_proramme['progid'] ?>"><?php echo ucfirst($row_proramme['progname']) ?></option>
                                                        <?php }
                                                        while ($row_proramme = mysql_fetch_assoc($prog)) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="control-group">
                                                <label class="control-label" for="textarea">Course</label>
                                                <div class="controls">
                                                    <select name="csid"  style=" width: 300px" onchange="crsfilt(this)" required>
                                                        <option value="">-Choose-</option>
                                                        <?php do { ?>
                                                            <option <?= ($row_course['csid'] == $csid )? 'selected' : ''; ?> value="<?php echo $row_course['csid'] ?>"><?php echo $row_course['csid'] . '  -  ' . $row_course['csname'] ?></option>
                                                        <?php }
                                                        while ($row_course = mysql_fetch_assoc($course)) ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-actions">
                                                
                                                <button class="btn btn-primary" type="submit">Generate Result</button>
                                                <a href="statistics/index.php?<?= $_SERVER['QUERY_STRING']?>" target="Tabs" class="btn btn-primary btn-blue">View Statistics</a>
                                                <input type="hidden" name="MM_Insert" value="form1"/>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
        <?php include INCPATH . "/footer.php" ?>
    </body>
</html>

