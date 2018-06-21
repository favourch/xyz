<?php
require_once('../../../path.php');

if (!isset($_SESSION)) {
    session_start();
}


$auth_users = "1,2,20,23";
check_auth($auth_users, $site_root.'/admin');


$query_rssess = "SELECT * FROM `session` WHERE sesid  > 9 ORDER BY sesname DESC";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);


$query_prog = sprintf("SELECT progname, progid "
                    . "FROM  programme ");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);


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
                                    <h3><i class="icon-search"></i>
                                        Search Payment List 
                                    </h3>
                                </div>
                                <div class="box-content nopadding">                                   
                                    <form class="form-horizontal form-bordered" method="POST" action="res.php" target="_blank">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Session</label>
                                            <div class="controls">
                                                <select name='ses' required>
                                                    <option value="">--Choose--</option>
                                                    <?php for (; $row_rssess != false; $row_rssess = mysql_fetch_assoc($rssess)) { ?>
                                                        <option value="<?php echo $row_rssess['sesid'] ?>"><?php echo $row_rssess['sesname']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">User type</label>
                                            <div class="controls">
                                                <select name='usertype' required>
                                                    <option value="">--Choose--</option>
                                                    <option value="pros">Prospective</option>
                                                    <option value="stud">Student</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Payment head</label>
                                            <div class="controls">
                                                <select name='payhead' required>
                                                    <option value="">--Choose--</option>
                                                    <option value="acc">Acceptance Fee</option>
                                                    <option value="app">Application Fee</option>
                                                    <option value="clr">Clearance Fee</option>
                                                    <option value="rep">Reparation Fee</option>
                                                    <option value="sch">School Fee</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Search by <?= $programme_name ?></label>
                                            <div class="controls controls-row">
                                                <select name='prog'>
                                                    <option value="all">All</option>
                                                    <?php for (; $row_prog != false; $row_prog = mysql_fetch_assoc($prog)) { ?>
                                                        <option value="<?php echo $row_prog['progid'] ?>"><?php echo $row_prog['progname']; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Level</label>
                                            <div class="controls">
                                                <select name='lvl'>
                                                    <option value="">--Choose--</option>
                                                    <option value="0">Prospective</option>
                                                    <option value="1">100</option>
                                                    <option value="2">200</option>
                                                    <option value="3">300</option>
                                                    <option value="4">400</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">From</label>
                                            <div class="controls">
                                                <input type="text" class="input-medium datepick" data-date-format="yyyy-mm-dd" id="textfield" name="from">
                                                <span class="help-block">YYYY-MM-DD</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">To</label>
                                            <div class="controls">
                                                <input type="text" class="input-medium datepick"  data-date-format="yyyy-mm-dd" id="textfield" name="to">
                                                <span class="help-block">YYYY-MM-DD</span>
                                            </div>
                                        </div>
                                        <input type="hidden" name="MM_Search" value="form1">
                                        <div class="form-actions">
                                            <button class="btn btn-primary" type="submit">Search Record</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>