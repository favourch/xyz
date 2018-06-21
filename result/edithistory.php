<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "12,2,3,4,5,6";
check_auth($auth_users, $site_root);

$sesid = '';
if(isset($_GET['sid'])) {
    $sesid = $_GET['sid'];
}

$csid = '';
if(isset($_GET['csid'])) {
    $csid = $_GET['csid'];
}

$stid = '';
if(isset($_GET['stdid'])) {
    $stid = $_GET['stdid'];
}

$state = false;
$edited = false;

if($sesid && $csid && $stid) {
    $state = true;
    
    $query_crs = sprintf("SELECT csname "
                            . "FROM course "
                            . "WHERE csid=%s", 
                            GetSQLValueString($csid, "text"));
    $crs = mysql_query($query_crs, $tams) or die(mysql_error());
    $row_crs = mysql_fetch_assoc($crs);
    $totalRows_crs = mysql_num_rows($crs);
    
    $query_stud = sprintf("SELECT stdid, lname, fname "
                            . "FROM student "
                            . "WHERE stdid=%s", 
                            GetSQLValueString($stid, "text"));
    $stud = mysql_query($query_stud, $tams) or die(mysql_error());
    $row_stud = mysql_fetch_assoc($stud);
    $totalRows_stud = mysql_num_rows($stud);
    
    $query_ses = sprintf("SELECT * "
                            . "FROM session "
                            . "WHERE sesid=%s", 
                            GetSQLValueString($sesid, "text"));
    $ses = mysql_query($query_ses, $tams) or die(mysql_error());
    $row_ses = mysql_fetch_assoc($ses);
    $totalRows_ses = mysql_num_rows($ses);
    
    
    $query_edit = sprintf("SELECT * "
                            . "FROM result_log rl, result r, lecturer l "
                            . "WHERE l.lectid = rl.lectid "
                            . "AND r.csid = rl.csid "
                            . "AND r.stdid = rl.stdid "
                            . "AND r.sesid = rl.sesid "
                            . "AND r.csid=%s "
                            . "AND r.stdid=%s "
                            . "AND r.sesid=%s  "
                            . "AND r.edited = 'TRUE'", 
                            GetSQLValueString($csid, "text"),							 
                            GetSQLValueString($stid, "text"), 
                            GetSQLValueString($sesid, "int"));
    $edit = mysql_query($query_edit, $tams) or die(mysql_error());
    $row_edit = mysql_fetch_assoc($edit);
    $totalRows_edit = mysql_num_rows($edit);
    
    if($totalRows_edit > 0) {
        $edited = true;
    }
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

                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Edit History 
                                    </h3>
                                </div>

                                <div class="box-content">
                                    <div class="row-fluid">                                             
                                        <div>
                                            <strong>Course:</strong> <?php echo $row_crs['csname']; ?> <br/>
                                            <strong>Student:</strong> <?php echo $row_stud['fname'] . ' ' . $row_stud['lname']; ?> - <?php echo $row_stud['stdid'] ?> <br/>
                                            <strong>Session:</strong> <?php echo $row_ses['sesname']; ?> <br/>
                                        </div>
                                        <table width="679" border="0" class="mytext table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th rowspan="2">S/N</th>
                                                        <th rowspan="2">Lecturer Name</th>
                                                        <th colspan="2">Old Scores</th>
                                                        <th colspan="2">New Score</th>
                                                        <th rowspan="2">Date</th>         
                                                    </tr>
                                                    <tr>
                                                        <th>Test</th>                
                                                        <th>Exam</th>
                                                        <th>Test</th>
                                                        <th>Exam</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($state) {
                                                        if ($edited) {
                                                            $idx = 1;
                                                            do {
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo $idx ?></td>
                                                                    <td><?php echo '<a href=\'../staff/profile.php?lectid=' . $row_edit['lectid'] . '\'>' . $row_edit['fname'] . ' ' . $row_edit['lname'] . '</a>' ?></td>
                                                                    <td><?php echo $row_edit['old_test'] ?></td>
                                                                    <td><?php echo $row_edit['old_exam'] ?></td>
                                                                    <td><?php echo $row_edit['new_test'] ?></td>
                                                                    <td><?php echo $row_edit['new_exam'] ?></td>
                                                                    <td><?php echo date('jS F, Y', strtotime($row_edit['date'])) ?></td>
                                                                </tr>
                                                                <?php
                                                                $idx++;
                                                            } while ($row_edit = mysql_fetch_assoc($edit));
                                                        } else {
                                                            ?>
                                                            <tr>
                                                                <td colspan="7">No edited entry for the specified parameters!</td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr>
                                                            <td colspan="7">Invalid parameters!</td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH . "/footer.php" ?>
        </div>
    </body>
</html>