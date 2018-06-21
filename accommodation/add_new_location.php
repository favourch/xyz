<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

$haveSumittedSQL = sprintf("SELECT * FROM accom_student_location "
        . "WHERE stdid = %s ", GetSQLValueString($_SESSION['uid'], 'text'));
$haveSumittedRS = mysql_query($haveSumittedSQL, $tams) or die(mysql_error());
$haveSumittedRow = mysql_fetch_assoc($haveSumittedRS);
$found = mysql_num_rows($haveSumittedRS);

if ($found > 0) {
    header(sprintf("location: %s", '/'.$site_root . '/student/profile.php'));
    die();
}

$locSQL = sprintf("SELECT * FROM accom_hostel_location ");
$loc = mysql_query($locSQL, $tams) or die(mysql_error());

$buildTypeSQL = sprintf("SELECT * FROM accom_building_type ");
$buildType = mysql_query($buildTypeSQL, $tams) or die(mysql_error());
//$buildTypeRow = mysql_fetch_assoc($buildType);

$buildFeatSQL = sprintf("SELECT * FROM accom_features ");
$buildFeat = mysql_query($buildFeatSQL, $tams) or die(mysql_error());

if (isset($_POST['submit122'])) {
    var_dump($_POST);
    die();
}

if (isset($_POST['submit'])) {
    mysql_query('BEGIN', $tams) ;
    
    $accom = sprintf("INSERT INTO accom_accomodation "
            . "(caretaker_name, caretaker_phone, "
            . "building_name, building_address, "
            . "location, no_of_rooms, "
            . "building_type, gender, pay_amount, pay_mode, stdid )  "
            . "VALUES"
            . "(%s, %s, %s, %s, %s, %s, "
            . "%s, %s, %s, %s, %s)", 
            GetSQLValueString($_POST['land_name'], 'text'), 
            GetSQLValueString($_POST['land_phone'], 'text'), 
            GetSQLValueString($_POST['build_name'], 'text'), 
            GetSQLValueString($_POST['build_address'], 'text'), 
            GetSQLValueString($_POST['location'], 'int'), 
            GetSQLValueString($_POST['no_of_room'], 'text'), 
            GetSQLValueString($_POST['build_type'], 'text'), 
            GetSQLValueString($_POST['gender'], 'text'), 
            GetSQLValueString($_POST['pay_amount'], 'text'), 
            GetSQLValueString($_POST['pay_mode'], 'text'),
            GetSQLValueString($_SESSION['uid'], 'text')
            );
    $insertRS = mysql_query($accom, $tams) or die(mysql_error());
    $insertId = mysql_insert_id();
    
    if(!empty($_POST['feat'])){
        $featValArray = array();
        foreach($_POST['feat'] as $key => $val){
            array_push($featValArray, "({$insertId}, {$val})");
        }
        
        $featVal = implode(',', $featValArray);
        $query2 = sprintf("INSERT INTO accom_accomodation_features "
                . "(accomid, featid ) "
                . "VALUES %s ", $featVal);
        $featRS = mysql_query($query2, $tams) or die(mysql_error());
    }
    
    $saveSQL = sprintf("INSERT INTO accom_student_location "
                    . "(stdid, locid) "
                    . "VALUES(%s, %s)", 
                    GetSQLValueString($_SESSION['uid'], 'text'), 
                    GetSQLValueString($insertId, 'int'));
    $saveRS = mysql_query($saveSQL, $tams) or die(mysql_error());
    
    mysql_query('COMMIT', $tams) ;
    
    
    
    $cont = array('Accommodation' => array(
            'caretaker_name' => array("old" => '', "new" => $_POST['land_name']),
            'caretaker_phone' => array('old' => '', 'new' => $_POST['land_phone']),
            'building_name' => array('old' => '', 'new' => $_POST['build_name']),
            'building_address' => array('old' => '', 'new' => $_POST['build_address']),
            'location' => array('old' => '', 'new' => $_POST['location']),
            'no_of_rooms' => array('old' => '', 'new' => $_POST['no_of_room']),
            'building_type' => array('old' => '', 'new' => $_POST['build_type']),
            'gender' => array('old' => '', 'new' => $_POST['gender']),
            'pay_amount' => array('old' => '', 'new' => $_POST['pay_amount']),
            'pay_mode' => array('old' => '', 'new' => $_POST['pay_mode']),
            'stdid' => array('old' => '', 'new' => $_SESSION['uid'])  
        )
    );

    $param['entid'] = $_SESSION['uid'];
    $param['enttype'] = 'accommodation';
    $param['action'] = 'create';
    $param['cont'] = json_encode($cont);
    audit_log($param);


    header('location:index.php');
    die();
}



$page_title = "Tasued";
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
                                    <h3><i class="icon-list"></i> Campus Accomodation Information form</h3>
                                </div>                                
                                <div class="box-content nopadding">
                                    <p style="color: red; font-size: 18px; text-align: center; margin-bottom: 5px;">Note: All students are obliged to provide the University with accurate information of their respective campus address.</p>
                                    <form action="#" method="POST" class='form-horizontal form-column form-bordered'>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Landlord/Caretaker Details</label>
                                            <div class="controls controls-row">
                                                <input style="margin-right: 6%;" type="text" name="land_name" id="textfield" placeholder="Name" class="input-xlarge" required="">
                                                <input type="text" name="land_phone" id="textfield" placeholder="Mobile" class="input-xlarge mask_phone" required="">                                                
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Building Information 1</label>
                                            <div class="controls controls-row">
                                                <input style="margin-right: 6%;" type="text" name="build_name" id="textfield" placeholder="Building Name (e.g Fantasy Hall)" class="input-xlarge" required="">                                                
                                                <select name="build_type" id="select"  class='input-large'>
                                                    <option value="">--Building Type--</option>
                                                    <?php for (; $buildTypeRow = mysql_fetch_assoc($buildType);) { ?>
                                                    <option value="<?= $buildTypeRow['buidid']?>"><?= $buildTypeRow['name'] ?></option>
                                                    <?php } ?>
                                                </select>                                                
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Building Information 2</label>
                                            <div class="controls controls-row">                      
                                                <textarea style="margin-right: 6%; width: 273px; height: 57px;" name="build_address" placeholder="Full Address" class="input input-large" required=""></textarea>   
                                                <select name="location" id="select"  class='input-large' required="">
                                                    <option value="">-- Select Location --</option>
                                                        <?php for (; $row_loc = mysql_fetch_assoc($loc);) { ?>
                                                    <option value="<?= $row_loc['locid']?>"><?= $row_loc['locname'] ?></option>
                                                        <?php } ?>
                                                </select> 
                                            </div>
                                        </div>

                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Amount Paid / Mode</label>
                                            <div class="controls controls-row">                                                
                                                <div style="margin-right: 6%;" class="input-append input-prepend">
                                                    <span class="add-on">#</span>
                                                    <input type="text" placeholder="XX"  name="pay_amount" required="" class='input-large'>
                                                    <span class="add-on">.00</span>
                                                </div>                                                                      
                                                <select name="pay_mode" id="select" class='input-large' required="">
                                                    <option value="">--Mode--</option>
                                                    <option value="Session">Per Session</option>
                                                    <option value="Anunual">Annual</option>                                                    
                                                </select>     
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="control-group">
                                                <label for="textfield" class="control-label">Hostel Type <small>(Gender)</small></label>
                                                <div class="controls">
                                                    <select name="gender" id="select" class='input-large' required="">
                                                        <option value="M">Male Only</option>
                                                        <option value="F">Female only</option>
                                                        <option value="Mix">Mixed</option>
                                                    </select>     
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label for="password" class="control-label">No. of Room</label>
                                                <div class="controls">
                                                    <select name="no_of_room" id="select" class='input-large' required="">
                                                        <option value="0-5">0-5</option>
                                                        <option value="5-10">5-10</option>
                                                        <option value="10-20">10-20</option>
                                                        <option value="20-30">20-30</option>
                                                        <option value="30-40">30-40</option>
                                                        <option value="40-50">40-50</option>
                                                    </select> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="control-group">
                                                <label class="control-label">Facility<small>Available for use</small></label>
                                                <div class="controls">
                                                    <div class="span6">
                                                        <?php for (; $buildFeatRow = mysql_fetch_assoc($buildFeat);) { ?> 
                                                        <label class='checkbox'>
                                                            <input type="checkbox" name="feat[]" value="<?= $buildFeatRow['featid']?>"> <?= $buildFeatRow['featname'] ?>
                                                        </label>  
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>                                            
                                        </div>
                                        <div class="span12">
                                            <div class="form-actions" style="text-align: center">
                                                <button type="submit" name="submit" class="btn btn-primary">Save changes</button>
                                                <button type="button" class="btn">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
<?php include INCPATH . "/footer.php" ?>
    </body>
</html>
<?php ?>
