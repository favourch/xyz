
<?php

///DEMO DATA 
$stdid = '20150404260';
$deptid =  '43';

if (!isset($_SESSION)) {
    session_start();
}

$lgid = isset($_GET['lg'])? $_GET['lg']: 1;

require_once('../path.php');

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


$list_lg = query_result('SELECT * FROM `state_lga` where stid = 28');
$list_tpsch = query_result("SELECT * FROM `tp_sch` where lgid = $lgid AND count < slot ");
//$list_tpsch = query_result("SELECT * FROM `tp_sch` 
 //                           JOIN tp_prog ON tp_sch.id = tp_prog.tpid 
  //                           WHERE lgid = $lgid AND tp_prog.slot > 0");
//print_r($list_tpsch); die();

//CHECK IF STUDENT IS REGISTERED AGAIN
$registered_student = query_result("SELECT * FROM `tp_student` where $stdid = '$stdid'");

?>


<p align="center">
    <strong>TAI SOLARIN UNIVERSITY OF EDUCATION, IJAGUN</strong>
</p>
<p align="center">
    <strong></strong>
</p>
<p align="center">
    <strong>UNIVERSITY TEACHING PRACTICE BOARD</strong>
</p>
<p>
    The Principal/Head Teacher Date: 4<sup>th</sup> August, 2017
</p>
<p>
    ____________________________
</p>
<p>
    ____________________________
</p>
<p>
    <strong>Dear Madam,</strong>
</p>
<p align="center">
    <strong>
        LETTER OF POSTING FOR 2017-2018 TEACHING PRACTICE EXERCISE
    </strong>
</p>
<p>
    This is to inform you that the Teaching Practice Exercise of the above
    named University commences on Monday 18<sup>th</sup> September 2017 and
    ends on Friday 15<sup>th</sup> December 2017 in line with the University
    Calendar for 2017/2018 Session.
</p>
<p>
    The student whose particulars appear below is to undergo his/her Teaching
    Practice Exercise in your school for the period specified above and should
    be specially monitored throughout the period of Teaching Practice.
</p>
<p>
    <strong><u> </u></strong>
    <u></u>
</p>
<p>
    Name:   <?php echo($_SESSION['lname']) ?> <?php echo($_SESSION['fname']) ?>  <?php echo($_SESSION['mname']) ?>
<p>
    Matriculation Number:________________________________________________
</p>
<p>
    Department:________________________________________________________
</p>
<p>
    Combination:________________________________________________________
</p>
<p>
    Phone No:__________________________________________________________
</p>
<p>
    <strong><u></u></strong>
</p>
<p>
    <strong>
        The student should be made to experience all aspects of school life,
        resume and close like your permanent teachers and should not be allowed
        to leave school on flimsy excuses.
    </strong>
    <u></u>
</p>
<p>
    Thanking you for your continued cooperation
</p>
<p>
    <strong></strong>
</p>
<p>
    <strong> Prof. Olugbemiga O. Oworu </strong>
    <strong></strong>
</p>
<p>
    <em> <strong>Chairman </strong></em>
</p>
<p>
    <strong><em> </em></strong>
    (08037272990)
</p>
<p align="center">
    <strong>
        <u>
            DO NOT DETACH THIS SLIP, IT MUST BE RETURNED WHOLE AFTER BEING
            FILLED
        </u>
    </strong>
</p>
<p align="center">
    <strong></strong>
</p>
<p align="center">
    <strong>LETTER OF REJECTION</strong>
</p>
<p>
The Management of …………………………………………………………………………………………………    <strong>hereby</strong> <strong>reject</strong>
    ………………………………………………………………………………………….Matric No. ___________________
Department …………………………………………………………………………….    <strong>Reason(s) for rejection</strong> is/are
</p>
<p>
    …………………………………………………………………………………………………………………………………………………………………
</p>
<p>
    <strong>Name/Signature of officer-in-charge</strong>
</p>
<p>
    <strong>Official School Stamp Phone Number:</strong>
</p>
<p align="center">
    <strong></strong>
</p>
<p>
    <strong></strong>
</p>
<p>
    <em></em>
</p>
<p>
    <em></em>
</p>
<?php ?>
