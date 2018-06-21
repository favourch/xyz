<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

///////////////////////////////////
// Start of Database Connection  //
///////////////////////////////////

$hostname_conn_burmas = "localhost";
$database_conn_burmas = "tasueded_gradportal";
$username_conn_burmas = "tasueded_grad";
$password_conn_burmas = "123gradportal456";
$conn_burmas = mysql_pconnect($hostname_conn_burmas, $username_conn_burmas, $password_conn_burmas) or trigger_error(mysql_error(), E_USER_ERROR);

/////////////////////////////////
// End of Database Connection  //
/////////////////////////////////


$auth_users = "1,2,20,21,22,23";
check_auth($auth_users, $site_root . '/admin');

mysql_select_db($database_tams, $tams);
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


mysql_select_db($database_conn_burmas, $conn_burmas);
mysql_query("SET SQL_BIG_SELECTS=1");
$query_stud = sprintf("SELECT s.*, st.amt "
                    . "FROM student_details s, gradfees_transactions st "
                    . "WHERE st.can_no = s.mat_number "
                    . " AND s.sesid = %s "
                    . " AND st.status = 'APPROVED' "
                    . " %s "
                    . "ORDER BY s.mat_number  ASC ", 
                    GetSQLValueString($ses, "int"), 
                    GetSQLValueString($filter, "defined", $filter)); 
$stud = mysql_query($query_stud, $conn_burmas) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);


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
                                        Convocation Fee Payment List
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">

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
                                                <th>Matric</th>
                                                <th>Name</th>
                                                <th>Amount</th>
                                                <th>Programme</th>
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
                                                            <a href="../../../student/profile.php?stid=<?= $row_stud['mat_number'] ?>">
                                                                <?= $row_stud['mat_number']; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <?= "{$row_stud['First_Name']} {$row_stud['Last_Name']}"; ?>
                                                        </td>
                                                        <td>
                                                            <?= $row_stud['amt']; ?>
                                                        </td>
                                                        <td>
                                                            <?= $row_stud['Preferred_Course']; ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                } while ($row_stud = mysql_fetch_assoc($stud));
                                            } else {
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