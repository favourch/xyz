<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');


$auth_users = "1,3,20";
check_auth($auth_users, $site_root);
        
$query_session = sprintf("SELECT sesid, sesname FROM session WHERE  sesid  = %s", GetSQLValueString($_SESSION['sesid'], "int"));
$session = mysql_query($query_session, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);


$query_department = sprintf("SELECT * FROM department WHERE deptid = %s ",GetSQLValueString($_SESSION['did'], "int"));
$department = mysql_query($query_department, $tams) or die(mysql_error());
$row_department = mysql_fetch_assoc($department);
$totalRows_department = mysql_num_rows($department);


$query_pros = sprintf("SELECT * "
                        . "FROM prospective p JOIN programme pr ON pr.progid = p.progoffered JOIN department d ON pr.deptid = d.deptid "
                        . "JOIN admissions a ON p.admid = a.admid "
                        . "JOIN admission_type at ON a.typeid = at.typeid "
                        . "WHERE d.deptid  = %s AND p.sesid = %s",  GetSQLValueString($row_department['deptid'], "int"), GetSQLValueString($row_session['sesid'], "int"));
$prosRS = mysql_query($query_pros, $tams) or die(mysql_error());
$row_pros = mysql_fetch_assoc($prosRS);
$totalRows_pros = mysql_num_rows($prosRS);


$total_value = [];
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
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3><i class="icon-reorder"></i>
                                    Admitted List for <?= $row_session['sesname']?> Department of  <?= $row_department['deptname']?>
                                </h3>
                            </div>
                            <div class="box-content ">  
                            <div class="row-fluid">
                                <div class="span12">
                                    <div class="left">
                                        <a class="btn btn-primary" href="print_dept_applist.php" target="tabs">Print</a>
                                    </div>
                                </div>
                            </div>
                                <table class="table table-bordered table-condensed table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Jamb Reg. No.</th>
                                            <th>Form Number</th>
                                            <th>Name</th>
                                            <th>Admission Type</th>
                                            <th>Sex</th>
                                            <th>Programme</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-weight: normal">
                                        <?php
                                            if ($totalRows_pros > 0) :
                                                $i = 1;
                                                do {
                                        ?>
                                        <tr>
                                            <td><?php echo $i++ ?></td>
                                            <td align="center"><?php echo strtoupper($row_pros['jambregid']) ?></td>
                                            <td align="center"><?php echo $row_pros['formnum'] ?></td>
                                            <td><?php echo strtoupper("{$row_pros['lname']} {$row_pros['fname']} {$row_pros['mname']}") ?></td>
                                            <td align="center"><?php echo $row_pros['typename'] ?></td>
                                            <td align="center"><?php echo $row_pros['Sex'] ?></td>
                                            <td align="center"><?php echo $row_pros['progname'] ?></td>
                                            <td>
                                                <a target="_blank" href="/<?= $site_root ?>/admission/viewform.php?stid=<?php echo $row_pros['jambregid'] ?>">View Profile</a>
                                            </td>
                                        </tr>
                                        <?php 
                                                }while($row_pros = mysql_fetch_assoc($prosRS));
                                            else :
                                        ?>
                                        <tr>
                                            <td colspan="8">There are no applicants to display!</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
<?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>