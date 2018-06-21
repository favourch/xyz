<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,32";
check_auth($auth_users, $site_root.'/admin');



$colname_rsstdnt = "-1";
$totalRows_rsstdnt = 0;
if (isset($_POST['search']) && $_POST['search'] != NULL) {
    $colname_rsstdnt = $_POST['search'];
    $seed = $colname_rsstdnt;
    
    $query_rsstdnt = "SELECT s.stdid, s.mname,s.fname,s.lname,s.phone, s.email as smail, 'FT' AS study_mode, 
                    c.colname, prg.progname, pl.cdegree, '13-11-2017' AS Date_of_grad, s.sex, 
                    s.maritalStatus, st.stname, s.jambregid, s.dob,
                    s.military_personel, s.addr, s.sponsorname,s.sponsoradrs, s.sponsorphn
                    FROM student s  
                    JOIN passlist pl on s.stdid = pl.stdid 
                    JOIN state st ON s.stid = st.stid
                    LEFT JOIN accom_student_location asl ON s.stdid = asl.stdid  
                    JOIN clearance_transactions ct ON ct.matric_no = s.stdid AND ct.status = 'APPROVED'
                    JOIN programme prg ON s.progid = prg.progid 
                    JOIN department d ON prg.deptid = d.deptid 
                    JOIN college c ON d.colid = c.colid 
                    WHERE s.lname LIKE '%" . $seed . "%'
                    OR s.fname LIKE '%" . $seed . "%'
                    OR s.stdid LIKE '%" . $seed . "%' ";
    
    $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());
    $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
    $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
}
?>
<!doctype html>
<html ng-app="TamsApp">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageController">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    <div class="breadcrumbs">
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
                    <br/>
                    <div class="span6">
                    <?php statusMsg(); ?>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Student in the University
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a href="#" class="btn  red"><?= $totalRows_rsstdnt . " students" ?></a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <form class="form form-vertical  form-validate" action="<?php echo $editFormAction; ?>" method="post">
                                                <div class="control-group span10">
                                                    <label class="control-label" for="textfield">Search By Name or Matric No </label>
                                                    <div class="controls span10">
                                                        <input name="search" type="text" class="input-xxlarge" />
                                                    </div>
                                                    <div class="controls ">
                                                        <input type="submit" class="btn " name="submit" value="Search" />
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <?php if (!empty($row_rsstdnt)) {?>
                                    <table class="table table-hover table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th width="5%">S/N</th>
                                                <th>Image</th>
                                                <th width="10%">Student ID</th>
                                                <th width="40%">Full Name</th>
                                                <th width="35%">Programme</th>
                                                <th width="10%">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i=1; do { ?>
                                                <tr>
                                                    <td><?= $i++; ?></td>
                                                    <td>
                                                    <img style="width: 60px; height: 50px;" src="<?= get_pics($row_rsstdnt['stdid'], '../../img/user/student') ?>">
                                                    </td>
                                                    <td><a href="../../student/profile.php?stid=<?php echo $row_rsstdnt['stdid']; ?>"><?php echo $row_rsstdnt['stdid']; ?></a></td>
                                                    <td><?php echo $row_rsstdnt['lname']; ?>, <?php echo ucwords(strtolower($row_rsstdnt['fname'])); ?> <?php echo ucwords(strtolower($row_rsstdnt['mname'])); ?></td>
                                                    <td><?php echo $row_rsstdnt['progname']; ?></td>
                                                    
                                                </tr>
                                            <?php }while ($row_rsstdnt = mysql_fetch_assoc($rsstdnt)); ?>
                                        </tbody>
                                    </table>
                                    <?php }else{?>
                                    <div class="alert alert-danger">
                                        SORRY!!! NO Record Available Search by Name or Matric No
                                    </div>
                                    <?php }?>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        
        
        <?php include INCPATH."/footer.php" ?>
    </body>
    
</html>