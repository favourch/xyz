
<?php

if (!isset($_SESSION)) {
  session_start();
}

if (!isset($_GET['stpid'])) {
  header('Location: tp_admin.php');
}
///DEMO DATA 
$stpid = $_GET['stpid'];
$deptid =  $_SESSION['did'];
$progid = $_SESSION['pid'];



$lgid = isset($_GET['lg'])? $_GET['lg']: 0;

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


$student_details = query_result("SELECT * FROM `tp_student` WHERE id = '$stpid'")[0];
$stdid = $student_details['stdid']; 
//print_r($student_details); die();

$registered_student = query_result("SELECT * FROM `tp_student` where stdid = '$stdid' and accepted = 'true'");

$list_lg = query_result('SELECT * FROM `state_lga` where stid = 28');/// LIST LG FROM OGUN STATE 
$list_tpsch = query_result("SELECT tp_sch.*, COUNT(student.progid) progcount 
                            FROM tp_sch
                            LEFT JOIN tp_student ON tp_student.tpschid = tp_sch.id 
                            LEFT JOIN student ON tp_student.stdid = student.stdid AND student.progid = '$progid'
                            GROUP BY tp_sch.id, student.progid
                            HAVING progcount < 10 AND lgid = $lgid  
                            ORDER BY tp_sch.schname ");

if(isset($_POST['apply_repost'])){

  $list_tpsch = query_result("SELECT tp_sch.*, COUNT(student.progid) progcount 
                            FROM tp_sch
                            LEFT JOIN tp_student ON tp_student.tpschid = tp_sch.id 
                            LEFT JOIN student ON tp_student.stdid = student.stdid AND student.progid = '$progid'
                            GROUP BY tp_sch.id, student.progid
                            HAVING progcount < 10 AND lgid = $lgid  
                            ORDER BY tp_sch.schname ");

    die(print_r($_POST));
    //$tpschid =  GetSQLValueString($_POST['tpschid'], 'text');
    $postingid =   GetSQLValueString($_POST['postingid'], 'text');

    $query_paid = sprintf("SELECT * "
                          . "FROM tp_transactions  "
                          . "WHERE status='APPROVED' "
                          . "AND used = 'false' "
                          . "AND matric_no= %s ",
                          GetSQLValueString($stdid, 'text'));
    $paid = mysql_query($query_paid, $tams) or die(mysql_error());
    $row_paid = mysql_fetch_assoc($paid);
    $total_paid = mysql_num_rows($paid);

    //print_r($total_paid); die();

    if($total_paid < 1) {
        //header("Location: tp_payment/index.php");
        //exit();
    }{
     

      /// UPDATE POSITNG ACCEPTANCE
      $register_student_sql = "UPDATE tp_student SET accepted  = 'false' WHERE id = $postingid";
      $register_student = query_result($register_student_sql);
    } 
}
  

if ($_POST['submit']) {
  $tpschid = $_POST['tpschid'];
  //die(print_r($_POST));
   
  //$tpsch = query_result("SELECT * FROM `tp_sch` where id = $tpschid");
  //CHECK IF STUDENT IS REGISTERED
  $registered_student = query_result("SELECT * FROM `tp_student` where stdid = '$stdid' and accepted = 'true'");
  
  //CHECK DEPT

  //SCHOOL SLOT 
   // $schslot  = $tpsch[0]['slot'];
   // $schscount = $tpsch[0]['count'];

    //CHECK REMAINING SLOT 
    // if($schslot > $schscount){
    //     $schscount = $schscount + 1; 
    //  }else{
    //     die('NO SLOT AVAILABLE FOR THE SELECTED SCHOOL');
    // }

  //CHECK IF STUDENT HAS REGISTERED 
  if(count($registered_student) == 0){

  //REGISTER STUDENT 
    $register_student_sql = "INSERT INTO tp_student (`stdid`, `tpschid`, `status`) VALUES ('$stdid', '$tpschid', 'reposted')";
    $register_student = query_result($register_student_sql);

  //UPDATE TPSCHOOL SLOT 
    // $sch_slot_sql = "UPDATE tp_sch SET count = $schscount WHERE id = $tpschid";
    // print_r($sch_slot_sql); die();
    // $update_sch_slot =  query_result($sch_slot_sql);
  }  
}

//CHECK IF STUDENT IS REGISTERED AGAIN
  $registered_student = query_result("SELECT * FROM `tp_student` where stdid = '$stdid' and accepted = 'true'");
//print_r("SELECT * FROM `tp_student` where stdid = '$stdid' and accepted = 'true'"); die();
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
                                        Students' Teaching Practice - Admin Repost for <?php echo $stdid; ?>
                                    </h3>
                                    <!-- <ul class="tabs pull-right form">
                                        <li>                             
                                            <a class="btn btn-green btn-small" href="#" "=""><i class="icon-print"> </i> </a>                       
                                        </li>
                                    </ul> -->
                                </div>
                                <div class="box-content">
                                    <?php if((count($registered_student) > 0)  AND (!isset($_POST['repost']))){?>
                                      <div class="well text-center text-success">
                                      <h3>THE STUDENT'S TEACHING PRACTICE SCHOOL HAS BEEN ASSIGNED </h3> 
                                      <a href="print_tp_letter.php" class="btn btn-primary"> PRINT POSTING LETTER</a> </div>


                                      <div class="well text-center text-info">
                                      <!-- <h6>In Case of Rejection</h6>  -->
                                      
                                      <form method="post">
                                        <?php $result = query_result("SELECT id FROM tp_student WHERE stdid = '$stdid' ORDER BY id DESC LIMIT 1"); 
                                              $postingid = $result[0]['id']; 
                                              //print_r($postingid); die();
                                              ?>
                                        <input type="hidden" name="postingid" value="<?php echo $postingid; ?>">
                                        <input type="submit" name="apply_repost" class="btn btn-info" value="APPLY FOR REPOSTING">
                                         </div> 
                                      </form>

                                      <div class="well text-center text-success">
                                         <a href="tp_admin.php" class="btn btn-success"> BACK TO POSTING ADMIN </a>
                                      </div>

                                      </div> 

                                      

                                                                            

                                    <?php }else{ ?>
                                    
                                    <form action="" method="POST" class=' form-wizard' id="ss">
                                         <div class="form-group ">
                                          <label class="control-label requiredField" for="lg">
                                           Local Government
                                           <span class="asteriskField">
                                            *
                                           </span>
                                          </label>
                                          <select class="select form-control" id="lg" name="lg"  onchange="lgfilt(this)" required="">
                                            <option value="">Select Local Government</option>
                                            <?php foreach ($list_lg as $lg) {?>
                                                <option value="<?= $lg['lgaid']; ?>" <?php echo ($lgid == $lg['lgaid'])? 'selected':''; ?>>
                                                    <?= $lg['lganame']; ?>
                                               </option>
                                            <?php } ?>
                                           
                                          </select>
                                         </div>

                                        <?php if(isset($_GET['lg'])){?>
                                         <div class="form-group ">
                                          <label class="control-label requiredField" for="tpschid">
                                           Available School
                                           <span class="asteriskField">
                                            *
                                           </span>
                                          </label>
                                          <select class="select form-control" id="tpschid" name="tpschid" required="">
                                            <option value="">Select School</option>
                                            <?php foreach ($list_tpsch as $sch) {?>
                                                <option value=" <?php echo $sch['id']; ?>">
                                                    <?php echo ucwords(strtolower($sch['schname'])); ?>
                                               </option>
                                            <?php } ?>
                                          </select>
                                         </div>
                                         <div class="form-group">
                                          <div>
                                          <?php if((count($registered_student) == 0)){?>
                                           <button class="btn btn-primary " name="submit" type="submit">
                                            SUBMIT
                                           </button>
                                            <?php }else{?>
                                              <button class="btn btn-primary " name="report" type="submit">
                                                REPORT
                                               </button>
                                            <?php } //getlg ?>
                                          </div>
                                         </div>
                                        <?php } //getlg ?>

                                        <!-- <div class="form-actions">
                                            <input type="submit" class="btn btn-primary" value="Submit" id="next">
                                        </div> -->
                                    </form>
                                    <?php } ?>


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
