<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');



$auth_users = "1,2,20,21,22,23";
check_auth($auth_users, $site_root . '/admin');

$query_rssess = "SELECT * FROM `session`  ORDER BY sesid DESC";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$sesname = $row_rssess['sesname'];


$level = 'all';
$filter = '';
$pid = 'all';

$ses = $row_rssess['sesid'];

if (isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

//if (isset($_GET['lvl'])) {
//    $level = $_GET['lvl'];
//
//    if ($level != 'all') {
//        $filter = 'AND st.level = ' . GetSQLValueString($level, 'int');
//    }
//}

if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    if ($pid != 'all') {
        $filter .= ' AND s.progid1 = ' . GetSQLValueString($pid, 'int');
    }
}


$query_prog = sprintf("SELECT  p.progname,  p.progid "
        . "FROM programme p  ");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);


$query_stud = sprintf("SELECT st.can_no, s.fname, s.lname, s.admtype, s.sex, st.percentPaid, adt.typename "
                    . "FROM prospective s "
                    . "JOIN programme p ON p.progid = s.progid1 "
                    . "JOIN appfee_transactions st "
                    . "ON st.can_no = s.jambregid "
                    . "JOIN admissions ad ON ad.admid = s.admid "
                    . "JOIN admission_type adt ON adt.typeid = ad.typeid "
                    . "WHERE st.sesid = %s "
                    . "AND st.status = 'APPROVED' "
                    . " %s "
                    . "ORDER BY s.lname ASC ",
                    GetSQLValueString($ses, "int"), 
                    GetSQLValueString($filter, "defined", $filter));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_levels = sprintf("SELECT max(duration) as `max` FROM programme ");
$levels = mysql_query($query_levels, $tams) or die(mysql_error());
$row_levels = mysql_fetch_assoc($levels);
$totalRows_levels = mysql_num_rows($levels);

$name = 'Paid students';

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}

$page_title = "Tasued";
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

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-money"></i>
                                        Application Fee Payment List
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Search by <?= $programme_name ?></label>
                                                <div class="controls controls-row">
                                                    <select name='prog' onchange="progfilt(this)">
                                                        <option value="all" <?= ($pid == "all") ? 'selected' : ''; ?>>All</option>
<?php for (; $row_prog != false; $row_prog = mysql_fetch_assoc($prog)) { ?>
                                                            <option value="<?php echo $row_prog['progid'] ?>" <?= ($pid == $row_prog['progid']) ? 'selected' : '' ?>><?php echo $row_prog['progname']; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">Session</label>
                                                <div class="controls controls-row">
                                                    <select name='ses' onchange="sesfilt(this)">
                                                    <?php for (; $row_rssess != false; $row_rssess = mysql_fetch_assoc($rssess)) { ?>
                                                            <option value="<?php echo $row_rssess['sesid'] ?>" <?= ($ses == $row_rssess['sesid']) ? 'selected' : '' ?>><?php echo $row_rssess['sesname']; ?>
                                                            </option>
                                                    <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span3">
                                            <div class="control-group">
                                                <label class="control-label" for="textfield">&nbsp;</label>
                                                <div class="controls controls-row">

                                                    <button class="btn"><i class="icon-user"></i> Total Paid <span class="label label-lightred"><?= $totalRows_stud ?></span></button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>  
                                    <table width="670" class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Jamb Reg.ID</th>
                                                <th>Name</th>
                                                <th>Sex</th>
                                                <th>Admission Mode</th>
                                                <th>Level</th>
                                                <th>Percentage Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if ($totalRows_stud > 0) {
                                            $i = 1;
                                            do {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $i++; ?></td>
                                                        <td>
                                                            <a href="../../../admission/viewform.php?stid=<?= $row_stud['can_no'] ?>">
                                                                <?= $row_stud['can_no']; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                        <?= "{$row_stud['fname']} {$row_stud['lname']}"; ?>
                                                        </td>
                                                        <td><?= getSex($row_stud['sex']) ?></td>
                                                        <td><?= $row_stud['typename']; ?></td>
                                                        <td>Prospective</td>
                                                        <td><?= $row_stud['percentPaid'] . '%' ?></td>
                                                    </tr>
        <?php
    }
    while ($row_stud = mysql_fetch_assoc($stud));
}
else {
    ?>
                                                <tr>
                                                    <td colspan="7"><div class="alert alert-error">No record available!</div></td>
                                                </tr>
    <?php
}
?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
<?php include INCPATH . "/footer.php" ?>
    </body>
</html>