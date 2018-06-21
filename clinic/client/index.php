<?php
if (!isset($_SESSION)) {
    @session_start();
}

require_once('../../path.php');


$stdid = getSessionValue('uid');

$query_rsstdnt = sprintf("SELECT * "
                . "FROM prospective "
                . "WHERE  jambregid = %s", GetSQLValueString($stdid, 'text'));
    $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());
    $row_rsstdnt = mysql_fetch_assoc($rsstdnt);

$array_clinicq = []; 
$array_clinicq1 = [];
$array_ans = [];
$query_clinicq = sprintf("SELECT *, cr.response as ans_response "
                        ." FROM clinic_questions cq"
                        ." LEFT JOIN clinic_response cr on cr.queid = cq.id"
                        ." AND cr.stdid = '$stdid'");
$clinicq = mysql_query($query_clinicq, $tams) or die(mysql_error());
$totalRows_clinicq = mysql_num_rows($clinicq);    

if($totalRows_clinicq > 0){
    for(; $row_clinicq = mysql_fetch_assoc($clinicq); ){
      $array_clinicq1[] = $row_clinicq;
      $array_clinicq[$row_clinicq['cat']][] = $row_clinicq; 
      $array_ans[] =  $row_clinicq['ans_response'];
    }
}

//print_r($array_clinicq); die();

if($_POST){

    foreach ($array_clinicq1 as $key => $que) {
        //print_r($que); die();
        $queid = $que['id'];
        $option = isset($_POST['option'.$queid])? $_POST['option'.$queid]:'';
        $response = isset($_POST['response'.$queid])? $_POST['response'.$queid]:'';
        $responseid = $que['response_id'];
        if(!empty($option) OR !empty($response)){
            if($que['response_id']){
                $ans_sql = "UPDATE clinic_response 
                            SET stdid = '$stdid', 
                            queid = '$queid', 
                            options = '$option', 
                            response = '$response'
                            WHERE response_id = $responseid";
               // print_r($ans_sql); die(); 
            }else{
                $ans_sql = "INSERT INTO clinic_response(stdid, queid, options, response) 
                VALUE('$stdid', '$queid', '$option', '$response')";
            }
            
                $result = mysql_query($ans_sql, $tams);

            
            
        }
        
    }
    //print_r($result); die();
    
    $sql = sprintf("UPDATE prospective SET clinic_form = 'yes' WHERE jambregid = %s ",GetSQLValueString($stdid, 'text'));
    $upRS = mysql_query($sql, $tams) or die(mysql_error());
    
    header('Location: index.php');   
    exit();
}
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
                                        STUDENTS' HEALTH INFORMATION DATA 
                                    </h3>
                                    
                                </div>
                                <div class="box-content">
                                    <?php if($row_rsstdnt['clinic_form'] == 'no') {?>
                                    
                                    <?php if (count($array_ans) > 0) {?>
                                    <div class="well text-center text-success">Record Saved </div>
                                    <?php } ?>

                                    <form action="" method="POST" class='form-horizontal form-wizard' id="ss">
                                        <div class="step" id="firstStep">
                                            
                                            <?php foreach ($array_clinicq as $key => $cqs) {?>
                                            <p style="text-align: center; font-size: 22px; margin-bottom: 10px;"><strong><?= $key; ?></strong></p>
                                            
                                            <?php $cqno = 0; foreach ($cqs as $key => $cq) {?>
                                            <div class="span12">                                                
                                                <div class="span6 control-group">                                                    
                                                    <div class="controls">
                                                        <p><strong><?= ++$cqno ?>.</strong> <?php echo $cq['question']; ?>	</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="span6 control-group">
                                                <input type="hidden" name="responseid<?php echo $cq['id']; ?>" value="<?php echo $cq['response_id']; ?>">

                                                <?php if($cq['type'] == 'yesno'){ ?>
                                                    <div class="controls">
                                                        <label class='span3 radio'>
                                                            
                                                            <input type="radio" value="yes" name="option<?php echo $cq['id']; ?>" 
                                                             <?php echo ($cq['options'] == 'yes')? 'checked': ''; ?> required> Yes
                                                        </label>
                                                        <label class='span3 radio'>
                                                            <input type="radio" value="no"  
                                                              name="option<?php echo $cq['id']; ?>"
                                                             <?php echo ($cq['options'] == 'no')? 'checked': ''; ?>
                                                            required> No
                                                        </label>
                                                    </div>
                                                <?php } if($cq['type'] == 'text'){ ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $cq['response']; ?>" name="response<?php echo $cq['id']; ?>" placeholder="<?php echo $cq['placeholder']; ?>" class="input-xlarge">
                                                    </div>
                                                <?php }  if($cq['textinput'] == 'yes'){ ?>
                                                    <div class="controls">
                                                        <input type="text" value="<?php echo $cq['response']; ?>"  name="response<?php echo $cq['id']; ?>" placeholder="<?php echo $cq['placeholder']; ?>" class="input-xlarge" >
                                                    </div>
                                                <?php } ?>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        <?php } ?>

                                        <div class="form-actions">
                                            <input type="submit" class="btn btn-primary" value="Submit" id="next">
                                        </div>
                                    </form>
                                    <?php }else{ ?>
                                    <div class="well text-center text-success">
                                        You have filled and  submitted your medical form. Click the Print button to print out your form. <br />
                                        <a class="btn btn-green" target="tabs" href="printform.php"><i class="icon-print"> </i> Print Clinic Form</a> </div>
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