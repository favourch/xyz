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


$auth_users = "1,20,26,24";
check_auth($auth_users, $site_root.'/admin');


$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);


$query = sprintf("SELECT * "
                . "FROM programme "
                . "WHERE continued = 'Yes' "
                . "ORDER BY progname ASC");
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_proramme = mysql_fetch_assoc($prog);

$query = sprintf("SELECT * "
        . "FROM college "
        . "ORDER BY colid ASC");
$col = mysql_query($query, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root.'login.php');
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
                                    <h3><i class="icon-barcode"></i>
                                        Released Verification Code Report
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span6">
                                            <h4>Generate Report</h4>
                                            <form class="form-horizontal form-column form-bordered" method="POST" action="printreport.php" target="_blank">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield"><?= $programme_name?></label>
                                                    <div class="controls">
                                                        <select name="progid" required="">
                                                            <option value="">-Choose-</option>
                                                            <?php do { ?>
                                                                <option value="<?php echo $row_proramme['progid'] ?>"><?php echo $row_proramme['progname'] ?></option>
                                                            <?php }
                                                            while ($row_proramme = mysql_fetch_assoc($prog)) ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="utype">Student Type</label>
                                                    <div class="controls">
                                                        <select name="utype" required="required">
                                                            <option value="">-Choose-</option>
                                                            <option value="pros">Prospective</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="sesid">Session</label>
                                                    <div class="controls">
                                                        <select name="sesid" required="required">
                                                            <option value="">-Choose-</option>
                                                            <?php do { ?>
                                                                <option value="<?php echo $row_session['sesid'] ?>"><?php echo $row_session['sesname'] ?></option>
                                                            <?php }while ($row_session = mysql_fetch_assoc($session)) ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <input type="hidden" name="MM_Search" value="form1">
                                                    <button class="btn btn-primary" type="submit">Generate</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="span6">
                                            <form class="form-horizontal form-bordered" method="POST" action="printreport2.php" target="_blank">
                                                <h4>Generate by Date</h4>
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">User Type</label>
                                                    <div class="controls">
                                                        <select name="utype" required="required">
                                                            <option value="">-Choose-</option>
                                                            <option value="pros">Prospective</option>
<!--                                                            <option value="stud">Returning</option>-->
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield"><?= $college_name?></label>
                                                    <div class="controls">
                                                        <select name="colid" required="required">
                                                            <option value="">-Choose-</option>
                                                            <?php do{?>
                                                            <option value="<?= $row_col['colid'] ?>"><?= $row_col['colname'] ?></option>
                                                            <?php }while($row_col = mysql_fetch_assoc($col))?>  
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
                                                            <input type="text" class="input-medium datepick" data-date-format="yyyy-mm-dd" id="textfield" name="to">
                                                            <span class="help-block">YYYY-MM-DD</span>
                                                    </div>
                                                </div>
                                                <div class="form-actions">
                                                    <input type="hidden" name="MM_Search" value="form2">
                                                    <button class="btn btn-primary" type="submit">Generate</button>
                                                </div>
                                            </form>  
                                        </div>
                                    </div>
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

