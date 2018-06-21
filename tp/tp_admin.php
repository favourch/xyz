
<?php

if (!isset($_SESSION)) {
  session_start();
}

///DEMO DATA 
$userid = $stdid = $_SESSION['MM_Username'];
$deptid =  $_SESSION['did'];
$progid = $_SESSION['pid'];
$cid = $_SESSION['cid'];

if (!isset($_SESSION)) {
    session_start();
}

$lgid = isset($_GET['lg'])? $_GET['lg']: 0;
$tpschid = isset($_GET['tpschid'])? $_GET['tpschid']: '';

require_once('../path.php');

// $query_tpschs = sprintf("SELECT *"
//                         ." FROM tp_sch tps"
//                         ." LEFT JOIN clinic_response cr on cr.queid = cq.id"
//                         ." AND cr.stdid = '$stdid'");
// $clinicq = mysql_query($query_clinicq, $tams) or die(mysql_error());

//print_r($array_clinicq); die();

$dbc=$tams; 
function query_result($query, $tams=''){
    
    if(empty($tams)){
        global $tams;        
    }

    $array_result = [];
    $run_query = mysql_query($query, $tams) or die(mysql_error());

    //check if query returns resource 
    if(is_resource($run_query)){
      $numrows = mysql_num_rows($run_query);    

        //check if record exist 
        if($numrows > 0){
            for(; $row_result = mysql_fetch_assoc($run_query); ){
              $array_result[] = $row_result;
            }
        }  
    }else{
        return true; 
    }

    //Next Grab Try to grab the required info and return 
    return $array_result;   

}


$list_lg = query_result('SELECT * FROM `state_lga` where stid = 28');/// LIST LG FROM OGUN STATE 
$list_tpsch = query_result("SELECT tp_sch.*, COUNT(student.progid) progcount 
                            FROM tp_sch
                            LEFT JOIN tp_student ON tp_student.tpschid = tp_sch.id 
                            LEFT JOIN student ON tp_student.stdid = student.stdid AND student.progid = '$progid'
                            GROUP BY tp_sch.id, student.progid
                            HAVING progcount < 2 AND lgid = $lgid  
                            ORDER BY tp_sch.schname ");


$list_registered_students_sql = "SELECT tp_student.id, progname, student.stdid, student.lname, student.mname, student.phone, student.progid, schname, accepted 
                            FROM tp_sch
                            JOIN tp_student ON tp_student.tpschid = tp_sch.id 
                            JOIN student ON tp_student.stdid = student.stdid 
                            JOIN programme pr ON pr.progid = student.progid
                            JOIN department dp ON dp.deptid = pr.deptid AND dp.colid = $cid
                            JOIN tp_admin ON tp_admin.collegeid = $cid AND tp_admin.userid = '$userid' ";
if($lgid){$list_registered_students_sql .=" WHERE lgid = '$lgid' "; }
if($tpschid){$list_registered_students_sql .=" AND tp_sch.id = '$tpschid'"; }
$list_registered_students_sql .=" GROUP BY tp_student.stdid
                                  ORDER BY tp_sch.id, tp_student.id DESC";
//sprint_r($list_registered_students_sql); die();
$list_registered_students = query_result($list_registered_students_sql);

//print_r($_SESSION); die();

?>   

<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>                 
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-color box-bordered">
                                <div class="box-title">
                                    <h3>
                                        <i class="icon-pencil"></i>
                                        Students' Teaching Practice - Administration
                                    </h3>
                                    <!-- <ul class="tabs pull-right form">
                                        <li>                             
                                            <a class="btn btn-green btn-small" href="#" "=""><i class="icon-print"> </i> </a>                       
                                        </li>
                                    </ul> -->
                                </div>
                                <div class="box-content">
                                    
                                    <form action="" method="POST" class=' form-wizard' id="ss">
                                        <div class="span4">
                                          <div class="form-group ">
                                            <label class="control-label requiredField" for="lg">
                                             Local Government
                                             <span class="asteriskField">
                                              *
                                             </span>
                                            </label>
                                            <select class="select form-control" id="lg" name="lg"  onchange="lgfilt(this)" required="">
                                              <option value=""></option>
                                              <?php foreach ($list_lg as $lg) {?>
                                                  <option value="<?= $lg['lgaid']; ?>" <?php echo ($lgid == $lg['lgaid'])? 'selected':''; ?>>
                                                      <?= $lg['lganame']; ?>
                                                 </option>
                                              <?php } ?>
                                             
                                            </select>
                                         </div>
                                        </div>

                                        <?php if(isset($_GET['lg'])){?>
                                        <div class="span4">
                                          
                                           <div class="form-group ">
                                            <label class="control-label requiredField" for="tpschid">
                                             Available School
                                             <span class="asteriskField">
                                              *
                                             </span>
                                            </label>
                                            <select class="select form-control" id="tpschid" name="tpschid" required="" onchange="tpschfilt(this)">
                                            <option value=""></option>
                                              <?php foreach ($list_tpsch as $sch) {?>
                                                  <option value="<?php echo $sch['id']; ?>"  <?php echo ($tpschid == $sch['id'])? 'selected':''; ?>>
                                                      <?php echo ucwords(strtolower($sch['schname'])); ?>
                                                 </option>
                                              <?php } ?>
                                            </select>
                                           </div>
                                          
                                        </div>
                                        <div class="span4">
                                          
                                        </div>
                                        <?php } //getlg ?>

                                        

                                        <!-- <div class="form-actions">
                                            <input type="submit" class="btn btn-primary" value="Submit" id="next">
                                        </div> -->
                                    </form>
                                    <?php //} ?>
        
                                      <table class="table">                                                                                                                            
                                        <thead>
                                            <tr>
                                                <th>No </th>
                                                <th>Student Name </th>
                                                <th>Matric No</th>
                                                <th>School</th>
                                                <th>Program</th>
                                                <th>Phone No</th>
                                                <th>Rejection</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <?php $n = 1;  foreach ($list_registered_students as $regstu) {?>
                                            <tr>
                                                <td> <?php echo $n++; ?> </td>
                                                <td><?php echo $regstu['lname']; ?> <?php echo $regstu['lname']; ?></td>
                                                <td><?php echo $regstu['stdid']; ?></td>
                                                <td><?php echo $regstu['schname']; ?></td>
                                                <td><?php echo $regstu['progname']; ?></td>
                                                <td><?php echo $regstu['phone']; ?></td>
                                                <td><?php echo ($regstu['accepted'] == 'true')? 'false': 'true'  ?></td>
                                                <td>
                                                <?php if ($regstu['accepted'] == 'false') {?>
                                                <a class="btn btn-info" href="tp_admin_repost.php?stpid=<?php echo $regstu['id']; ?>">REPOST</a>
                                                <?php } ?>
                                                </td>
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
    </body>
</html>
<?php ?>
