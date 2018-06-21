
<?php


if (!isset($_SESSION)) {
  session_start();
}

///DEMO DATA 
$stdid = $_SESSION['MM_Username'];
$deptid =  $_SESSION['did'];
$progid = $_SESSION['pid'];

if (!isset($_SESSION)) {
    session_start();
}

$total_paid = 0;
$lgid = isset($_GET['lg'])? $_GET['lg']: 1;

require_once('../path.php');

// $query_tpschs = sprintf("SELECT *"
//                         ." FROM tp_sch tps"
//                         ." LEFT JOIN clinic_response cr on cr.queid = cq.id"
//                         ." AND cr.stdid = '$stdid'");
// $clinicq = mysql_query($query_clinicq, $tams) or die(mysql_error());

//print_r($stdid); die();

$dbc=$tams; 

//print_r($_POST); die();
if(isset($_POST['apply_repost'])){

  $list_tpsch = query_result("SELECT tp_sch.*, COUNT(student.progid) progcount 
                            FROM tp_sch
                            LEFT JOIN tp_student ON tp_student.tpschid = tp_sch.id 
                            LEFT JOIN student ON tp_student.stdid = student.stdid AND student.progid = '$progid'
                            GROUP BY tp_sch.id, student.progid
                            HAVING progcount < 10 AND lgid = $lgid  
                            ORDER BY tp_sch.schname ");

    //die(print_r($_POST));
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
        header("Location: tp_payment/index.php");
        exit();
    }{
     

      /// UPDATE POSITNG ACCEPTANCE
      $register_student_sql = "UPDATE tp_student SET accepted  = 'false' WHERE id = $postingid";
      $register_student = query_result($register_student_sql);
    } 
}

if(isset($_POST['repost'])){

      $tpschid = $_POST['tpschid'];

       /// UPDATE POSITNG ACCEPTANCE
      $update_payment_sql = "UPDATE tp_transactions SET used  = 'true' WHERE matric_no = '$stdid'";
      query_result($update_payment_sql);

        //REGISTER STUDENT 
      $register_student_sql = "INSERT INTO tp_student (`stdid`, `tpschid`) VALUES ('$stdid', '$tpschid')";
      //print_r($register_student); die();
      $register_student = query_result($register_student_sql);
    } 



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


$list_lg = query_result('SELECT * FROM `state_lga` where stid = 28');  /// LIST LG FROM OGUN STATE 


$list_tpsch = query_result("SELECT tp_sch.*, COUNT(student.progid) progcount 
                            FROM tp_sch
                            LEFT JOIN tp_student ON tp_student.tpschid = tp_sch.id 
                            LEFT JOIN student ON tp_student.stdid = student.stdid AND student.progid = '$progid'
                            GROUP BY tp_sch.id, student.progid
                            HAVING progcount < 2 AND lgid = $lgid  
                            ORDER BY tp_sch.schname ");

if(isset($_POST['apply_repost'])){

  $list_tpsch = query_result("SELECT tp_sch.*, COUNT(student.progid) progcount 
                            FROM tp_sch
                            LEFT JOIN tp_student ON tp_student.tpschid = tp_sch.id 
                            LEFT JOIN student ON tp_student.stdid = student.stdid AND student.progid = '$progid'
                            GROUP BY tp_sch.id, student.progid
                            HAVING progcount < 10 AND lgid = $lgid  
                            ORDER BY tp_sch.schname ");

    //die(print_r($_POST));
    //$tpschid =  GetSQLValueString($_POST['tpschid'], 'text');
    $postingid =   GetSQLValueString($_POST['postingid'], 'text');

    $total_paid = 0;
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
        header("Location: tp_payment/index.php");
        exit();
    }{
     

      /// UPDATE POSITNG ACCEPTANCE
      $register_student_sql = "UPDATE tp_student SET accepted  = 'false' WHERE id = $postingid";
      $register_student = query_result($register_student_sql);
    } 
}
  


if (isset($_POST['postnew'])) {
  $tpschid = $_POST['tpschid'];
  //die(print_r($_POST));
   
  //$tpsch = query_result("SELECT * FROM `tp_sch` where id = $tpschid");
  //CHECK IF STUDENT IS REGISTERED
  $registered_student = query_result("SELECT * FROM `tp_student` where stdid = '$stdid' AND accepted = 'true'");
  
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
    $register_student_sql = "INSERT INTO tp_student (`stdid`, `tpschid`) VALUES ('$stdid', '$tpschid')";
    $register_student = query_result($register_student_sql);

  //UPDATE TPSCHOOL SLOT 
    // $sch_slot_sql = "UPDATE tp_sch SET count = $schscount WHERE id = $tpschid";
    // print_r($sch_slot_sql); die();
    // $update_sch_slot =  query_result($sch_slot_sql);
  }  
}

//CHECK IF STUDENT IS REGISTERED AGAIN
$registered_student = query_result("SELECT * FROM `tp_student` WHERE stdid = '$stdid' AND accepted = 'true'");
//print_r($registered_student); die();
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
                                        Students' Teaching Practice Registration
                                    </h3>
                                    <!-- <ul class="tabs pull-right form">
                                        <li>                             
                                            <a class="btn btn-green btn-small" href="#" "=""><i class="icon-print"> </i> </a>                       
                                        </li>
                                    </ul> -->
                                </div>
                                <div class="box-content">
                                    <?php if((count($registered_student) > 0)  AND (!isset($_POST['apply_repost']))){?>
                                      <div class="well text-center text-success">
                                      <h3>YOUR TEACHING PRACTICE SCHOOL HAS BEEN ASSIGNED </h3> 
                                      <a href="print_tp_letter.php" class="btn btn-primary"> PRINT POSTING LETTER</a> </div> 

                                      <div class="well text-center text-info">
                                      <h5>You can apply for REPOSTING, after payment of a processing fee.</h5>
                                      <h5> Be sure of ACCEPTANCE by the School before making the selection.</h5> 
                                      <form method="post">
                                      <?php $result = query_result("SELECT id FROM tp_student WHERE stdid = '$stdid' ORDER BY id DESC LIMIT 1"); 
                                            $postingid = $result[0]['id']; 
                                            //print_r($postingid); die();
                                            ?>
                                      <input type="hidden" name="postingid" value="<?php echo $postingid; ?>">
                                      <input type="submit" name="apply_repost" class="btn btn-info" value="APPLY FOR REPOSTING">
                                       </div> 
                                      </form>
                                    <?php }else{ ?>

                                    <form action="" method="POST" class=' form-wizard' id="ss">
                                    
                                      <?php  if($total_paid > 0){ ?>
                                      <div class="form-group ">
                                          <label class="control-label requiredField" for="reason">
                                           Reason
                                           <span class="asteriskField">
                                            *
                                           </span>
                                          </label>
                                          <select class="select form-control" id="reason" name="reason" required="">
                                            <option value="need repost">Need Repost</option>
                                            <option value="Schools Rejection">School's Rejection</option>
                                          </select>
                                         </div>
                                         <?php } ?>

                                         <div class="form-group ">
                                          <label class="control-label requiredField" for="lg">
                                           Local Government
                                           <span class="asteriskField">
                                            *
                                           </span>
                                          </label>
                                          <select class="select form-control" id="lg" name="lg"  onchange="lgfilt(this)" required="">
                                            
                                            <?php foreach ($list_lg as $lg) {?>
                                                <option value="<?= $lg['lgaid']; ?>" <?php echo ($lgid == $lg['lgaid'])? 'selected':''; ?>>
                                                    <?= $lg['lganame']; ?>
                                               </option>
                                            <?php } ?>
                                           
                                          </select>
                                         </div>
                                         <!-- <div class="form-group ">
                                          <label class="control-label requiredField" for="tpschid">
                                           School Category 
                                           <span class="asteriskField">
                                            *
                                           </span>
                                          </label>
                                          <select class="select form-control" name="schcat" required="">
                                                <option value="pry">PRIMARY SCHOOL </option>                                               </option>
                                                <option value="sec">SECONDARY SCHOOL </option>                                               </option>
                                          </select>
                                         </div> -->

                                        <?php if(isset($_GET['lg'])){?>
                                        
                                         <div class="form-group ">
                                          <label class="control-label requiredField" for="tpschid">
                                           Available School
                                           <span class="asteriskField">
                                            *
                                           </span>
                                          </label>
                                          <select class="select form-control" id="tpschid" name="tpschid" required="">
                                            <?php foreach ($list_tpsch as $sch) {?>
                                                <option value=" <?php echo $sch['id']; ?>">
                                                    <?php echo ucwords(strtolower($sch['schname'])); ?>
                                               </option>
                                            <?php } ?>
                                          </select>
                                         </div>
                                         <div class="form-group">
                                          <div>
                                          <?php if(isset($registered_student[0]['accepted'])){
                                           if(($registered_student[0]['accepted'] == 'true')){?>
                                           <button class="btn btn-primary " name="postnew" type="submit">
                                            SUBMIT
                                           </button>
                                            <?php }
                                            }else{?>
                                              <button class="btn btn-primary " name="repost" type="submit">
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
