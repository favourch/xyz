<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../../path.php');

$auth_users = "1,2,3,4,5,6,20,21,24,28";
check_auth($auth_users, $site_root);

$sesid = -1;
$crsid = '';
$range = -1;

$data = array();

if(isset($_GET['sid'])){
    $sesid = $_GET['sid'];
}

if(isset($_GET['crs'])){
    $crsid = $_GET['crs'];
}

if(isset($_GET['rng'])){
    $range = $_GET['rng'];
}

$rangeBreak = explode('-', $range);


//get Course details 
$scDetailsSQL = sprintf("SELECT csid, csname "
                        . "FROM course "
                        . "WHERE csid = %s ", 
                        GetSQLValueString($crsid, 'text'));
$csDetailsRS = mysql_query($scDetailsSQL) or die(mysql_error());
$csDetailsRow = mysql_fetch_assoc($csDetailsRS);


//Get Result 
 $resultSQL = sprintf("SELECT r.resultid, r.stdid, r.tscore, r.escore, s.fname, s.lname, s.mname, ses.sesname,"
                    . "(r.tscore + r.escore) AS total "
                    . "FROM result r, student s, session ses "
                    . "WHERE r.stdid = s.stdid AND r.csid = %s AND r.sesid = ses.sesid  "
                    . "AND r.sesid = %s "
                    . "AND (r.tscore + r.escore)  BETWEEN  %s AND %s  ORDER BY  total ASC  " , 
                GetSQLValueString($crsid, 'text'), 
                GetSQLValueString($sesid, 'int'),
    GetSQLValueString($rangeBreak[0], 'int'), 
    GetSQLValueString($rangeBreak[1], 'int'));
$resultRS = mysql_query($resultSQL) or die(mysql_error());
$resultRow = mysql_fetch_assoc($resultRS);
$resultNumRows = mysql_num_rows($resultRS);





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
                                       <?= $resultRow['sesname']?> <?= $crsid?> Result for Score Range between <?= $rangeBreak[0]?> and <?= $rangeBreak[1]?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table  table-condensed table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="10%">Student ID</th>
                                                        <th width="20%">Full Name</th>
                                                        <th width="10%">CA</th>
                                                        <th width="10%">Exam</th>
                                                        <th width="10%">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i=1; do{ ?>
                                                    <tr>
                                                        <td><?= $i++;?></td>
                                                        <td><?= $resultRow['stdid']?></td>
                                                        <td><?= $resultRow['lname'] ." ".$resultRow['fname']." ".$resultRow['mname'] ?></td>
                                                        <td style="color: brown"><?= $resultRow['tscore']?></td>
                                                        <td  style="color: brown"><?= $resultRow['escore']?></td>
                                                        <td  style="color: green"><?= (int)($resultRow['tscore'] + $resultRow['escore'])?></td>
                                                    </tr>
                                                    <?php }while($resultRow = mysql_fetch_assoc($resultRS)); ?>
                                                </tbody>
                                            </table>
                                        </div>
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