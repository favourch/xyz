<?php 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../path.php');

if(isset($_POST['MM_list'])){
    
     $query_passlist = sprintf("SELECT * FROM passlist p "
                        . " JOIN student s ON p.stdid = s.stdid "
                        . " JOIN registration r ON r.stdid = s.stdid JOIN session ses ON r.sesid = ses.sesid JOIN level_name ln ON r.level = ln.levelid  "
                        . " JOIN programme pr ON s.progid = pr.progid "
                        . " JOIN department d ON pr.deptid = d.deptid "
                        . " JOIN college c ON d.colid = c.colid "
                        . " WHERE s.disciplinary = 'FALSE' AND r.progid = %s AND r.sesid = %s AND r.level = %s ORDER BY s.progid ASC ", 
                        GetSQLValueString($_POST['progid'], "int"), GetSQLValueString($_POST['sesid'], "int"), GetSQLValueString($_POST['level'], "int")); 
    $passlist = mysql_query($query_passlist, $tams) or die(mysql_error());
    $row_passlist = mysql_fetch_assoc($passlist);
    $totalRows_passlist = mysql_num_rows($passlist);
    
    
     $query_leftover = sprintf("SELECT * FROM  student s "
                        . " JOIN registration r ON r.stdid = s.stdid JOIN session ses ON r.sesid = ses.sesid JOIN level_name ln ON r.level = ln.levelid "
                        . " JOIN programme pr ON s.progid = pr.progid "
                        . " JOIN department d ON pr.deptid = d.deptid "
                        . " JOIN college c ON d.colid = c.colid "
                        . " WHERE s.disciplinary = 'FALSE' AND s.passlist = 'No' AND r.progid = %s AND r.sesid = %s AND r.level = %s ORDER BY s.progid ASC ", 
                        GetSQLValueString($_POST['progid'], "int"), GetSQLValueString($_POST['sesid'], "int"), GetSQLValueString($_POST['level'], "int"));
    $leftover = mysql_query($query_leftover, $tams) or die(mysql_error());
    $row_leftover = mysql_fetch_assoc($leftover);
    $totalRows_leftover = mysql_num_rows($leftover);
    
}

$query = '';
if (getAccess() == 3) {
    $query = "AND p.deptid = " . GetSQLValueString(getSessionValue('did'), 'int');
}

if (getAccess() == 2) {
    $query = "AND d.colid = " . GetSQLValueString(getSessionValue('cid'), 'int');
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
                                    <h3><?=$row_leftover['sesname'] .' '. $row_leftover['progname'] . ' '. $row_leftover['levelname'] . ' Result Profile'?></h3>
                                </div>
                                <div class="box-content"> 
                                    <div class="row-fluid">
                                        <div class="alert alert-warning">Note: The list below are the list of stdent who have NOT made the passlist. </div>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                  <th>S/N</th>
                                                  <th>Matric</th>
                                                  <th>Name</th>
                                                  <th>Sex</th>
                                                  <th>Programme</th>
                                                  <th>Class of Degree</th>
                                                  <th>Computaion Method</th>
                                                  <th>Actions</th>
                                                  
                                                </tr>
                                            </thead>
                                            
                                            <tbody>
                                            <?php $j = 1; do{ ?>
                                                <tr>
                                                    <td><?= $j++;?></td>
                                                    <td><?= $row_leftover['stdid']?></td>
                                                    <td><?= $row_leftover['lname'] . ' '. $row_leftover['fname']. ' '. $row_leftover['mname']?></td>
                                                    <td><?= $row_leftover['sex']?></td>
                                                    <td><?= $row_leftover['progname']?></td>
                                                    <td><?= $row_leftover['cdegree']?></td>
                                                    <td><?= $row_leftover['source']?></td>
                                                    
                                                    <td>
                                                        
                                                        <a class="btn btn-small btn-warning" href="transprint.php?stdid=<?= $row_leftover['stdid'] ?>" target="_tabs">Print Result Profile</a>
                                                        
                                                    </td>
                                                </tr>
                                            <?php }while($row_leftover = mysql_fetch_assoc($leftover))?>    
                                                
                                            </tbody>
                                            
                                        </table>
                                    </div>  
                                    <p>&nbsp;</p>
                                    <div class="row-fluid">
                                        <div class="alert alert-success">Note: The list below are the list of stdent who have made the passlist. </div>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                  <th>S/N</th>
                                                  <th>Matric</th>
                                                  <th>Name</th>
                                                  <th>Sex</th>
                                                  <th>Programme</th>
                                                  <th>Class of Degree</th>
                                                  <th>Computaion Method</th>
                                                  <th>Actions</th>
                                                  
                                                </tr>
                                            </thead>
                                            
                                            <tbody>
                                            <?php $i = 1; do{ ?>
                                                <tr>
                                                    <td><?= $i++; ?></td>
                                                    <td><?= $row_passlist['stdid']?></td>
                                                    <td><?= $row_passlist['lname'] . ' '. $row_passlist['fname']. ' '. $row_passlist['mname']?></td>
                                                    <td><?= $row_passlist['sex']?></td>
                                                    <td><?= $row_passlist['progname']?></td>
                                                    <td><?= $row_passlist['cdegree']?></td>
                                                    <td><?= $row_passlist['source']?></td>
                                                    
                                                    <td>
                                                        <?php if($row_passlist['source'] != 'excel' ) {?>
                                                        <a class="btn btn-small btn-success" href="result_profile.php?stdid=<?= $row_passlist['stdid'] ?>" target="_tabs">Print Result Profile</a>
                                                        <?php } else { ?>
                                                        <div class="alert alert-warning"> Result was computed manually</div>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php }while($row_passlist = mysql_fetch_assoc($passlist))?>    
                                                
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