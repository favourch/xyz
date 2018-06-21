<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

$haveSumittedSQL = sprintf("SELECT * FROM accom_student_location "
                        . "WHERE stdid = %s ",
                        GetSQLValueString($_SESSION['uid'], 'text'));
$haveSumittedRS = mysql_query($haveSumittedSQL, $tams) or die(mysql_error());
$haveSumittedRow = mysql_fetch_assoc($haveSumittedRS);
$found = mysql_num_rows($haveSumittedRS);

if($found > 0){
    header(sprintf("location: %s", '/'.$site_root.'/student/profile.php'));
    die();
}

$id= -1;
if(isset($_GET['id'])){
    $id = $_GET['id'];
}


$accomSQL = sprintf("SELECT * FROM accom_accomodation "
                    . "WHERE accomid = %s", 
                    GetSQLValueString($id, 'int'));
$accomRS = mysql_query($accomSQL, $tams) or die(mysql_error());
$accomRow = mysql_fetch_assoc($accomRS);

$accomFeatSQL = sprintf("SELECT * FROM accom_accomodation_features  "
                      . "WHERE accomid = %s ", 
                     GetSQLValueString($id, 'int'));
$accomFeatRS = mysql_query($accomFeatSQL, $tams) or die(mysql_error());

$locSQL = sprintf("SELECT * FROM accom_hostel_location ");
$loc = mysql_query($locSQL, $tams) or die(mysql_error());

$buildTypeSQL = sprintf("SELECT * FROM accom_building_type ");
$buildType = mysql_query($buildTypeSQL, $tams) or die(mysql_error());
//$buildTypeRow = mysql_fetch_assoc($buildType);

$buildFeatSQL = sprintf("SELECT * FROM accom_features ");
$buildFeat = mysql_query($buildFeatSQL, $tams) or die(mysql_error());



if (isset($_POST['submit'])) {
    mysql_query('BEGIN', $tams);

    $accom = sprintf("UPDATE accom_accomodation "
            . " SET caretaker_name = %s, caretaker_phone = %s, "
            . "building_name = %s, building_address = %s, "
            . "location = %s, no_of_rooms = %s, "
            . "building_type = %s , gender = %s, pay_amount = %s, pay_mode = %s  "
            . "WHERE accomid = %s ",
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
            GetSQLValueString($_POST['edit_id'], 'text')
    );
    $insertRS = mysql_query($accom, $tams) or die(mysql_error());
    

    if (!empty($_POST['feat'])) {
        
        $featValArray = array();
        foreach ($_POST['feat'] as $key => $val) {
            array_push($featValArray, "({$_POST['edit_id']}, {$val})");
        }
        $featVal = implode(',', $featValArray);
        
        $query3 = sprintf("DELETE FROM accom_accomodation_features "
                . "WHERE accomid = %s ", 
                GetSQLValueString($_POST['edit_id'], 'int'));
        $featdelRS = mysql_query($query3, $tams) or die(mysql_error());

        $query2 = sprintf("INSERT INTO accom_accomodation_features "
                . "(accomid, featid ) "
                . "VALUES %s ", $featVal);
        $featRS = mysql_query($query2, $tams) or die(mysql_error());
    }
    
    $saveSQL = sprintf("INSERT INTO accom_student_location "
                    . "(stdid, locid) "
                    . "VALUES(%s, %s)", 
                    GetSQLValueString($_SESSION['uid'], 'text'), 
                    GetSQLValueString($_POST['edit_id'], 'int'));
    $saveRS = mysql_query($saveSQL, $tams) or die(mysql_error());

    mysql_query('COMMIT', $tams);
    
    $cont = array('Accommodation' => array(
            'caretaker_name' => array("old" => $accomRow['caretaker_name'], "new" => $_POST['land_name']),
            'caretaker_phone' => array('old' => $accomRow['caretaker_phone'], 'new' => $_POST['land_phone']),
            'building_name' => array('old' => $accomRow['building_name'], 'new' => $_POST['build_name']),
            'building_address' => array('old' => $accomRow['building_address'], 'new' => $_POST['build_address']),
            'location' => array('old' => $accomRow['location'], 'new' => $_POST['location']),
            'no_of_rooms' => array('old' => $accomRow['no_of_rooms'], 'new' => $_POST['no_of_room']),
            'building_type' => array('old' => $accomRow['building_type'], 'new' => $_POST['build_type']),
            'gender' => array('old' => $accomRow['gender'], 'new' => $_POST['gender']),
            'pay_amount' => array('old' => $accomRow['pay_amount'], 'new' => $_POST['pay_amount']),
            'pay_mode' => array('old' => $accomRow['pay_mode'], 'new' => $_POST['pay_mode'])
        )
    );

    $param['entid'] = $_SESSION['uid'];
    $param['enttype'] = 'accommodation';
    $param['action'] = 'edit';
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
                                                <input style="margin-right: 6%;" type="text" name="land_name" id="textfield" placeholder="Name" class="input-xlarge" required="" value="<?= $accomRow['caretaker_name']?>">
                                                <input type="text" name="land_phone" id="textfield" placeholder="Mobile" class="input-xlarge mask_phone" required="" value="<?= $accomRow['caretaker_phone']?>">                                                
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Building Information 1</label>
                                            <div class="controls controls-row">
                                                <input style="margin-right: 6%;" type="text" name="build_name" id="textfield" placeholder="Building Name (e.g Fantasy Hall)" class="input-xlarge" required="" value="<?= $accomRow['building_name'] ?>">                                                
                                                <select name="build_type" id="select"  class='input-large'>
                                                    <option value="">--Building Type--</option>
                                                        <?php for (; $buildTypeRow = mysql_fetch_assoc($buildType);) { ?>
                                                        <option value="<?= $buildTypeRow['buidid'] ?>" <?= ($accomRow['building_type'] == $buildTypeRow['buidid'] ) ? 'selected' : ''?>><?= $buildTypeRow['name'] ?></option>
                                                        <?php } ?>
                                                </select>                                                
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Building Information 2</label>
                                            <div class="controls controls-row">                      
                                                <textarea style="margin-right: 6%; width: 273px; height: 57px;" name="build_address" placeholder="Full Address" class="input input-large" required=""><?= $accomRow['building_address']?></textarea>   
                                                <select name="location" id="select"  class='input-large' required="">
                                                    <option value="">-- Select Location --</option>
                                                    <?php for (; $row_loc = mysql_fetch_assoc($loc);) { ?>
                                                        <option value="<?= $row_loc['locid'] ?>" <?=($accomRow['location'] == $row_loc['locid'])? 'selected': '' ?> ><?= $row_loc['locname'] ?></option>
                                                    <?php } ?>
                                                </select> 
                                            </div>
                                        </div>

                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Amount Paid / Mode</label>
                                            <div class="controls controls-row">                                                
                                                <div style="margin-right: 6%;" class="input-append input-prepend">
                                                    <span class="add-on">#</span>
                                                    <input type="text" placeholder="XX"  name="pay_amount" required="" class='input-large' value="<?= $accomRow['pay_amount']?>">
                                                    <span class="add-on">.00</span>
                                                </div>                                                                      
                                                <select name="pay_mode" id="select" class='input-large' required="">
                                                    <option value="">--Mode--</option>
                                                    <option value="Session" <?= ($accomRow['pay_mode'] == 'Session') ? 'selected' : ''?>>Per Session</option>
                                                    <option value="Anunual" <?= ($accomRow['pay_mode'] == 'Annual') ? 'selected' : ''?>>Annual</option>                                                    
                                                </select>     
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="control-group">
                                                <label for="textfield" class="control-label">Hostel Type <small>(Gender)</small></label>
                                                <div class="controls">
                                                    <select name="gender" id="select" class='input-large' required="">
                                                        <option value="M" <?= ($accomRow['gender'] == 'M') ? 'selected' : ''?>>Male Only</option>
                                                        <option value="F" <?= ($accomRow['gender'] == 'F') ? 'selected' : ''?>>Female only</option>
                                                        <option value="Mix" <?= ($accomRow['gender'] == 'Mix') ? 'selected' : ''?>>Mixed</option>
                                                    </select>     
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label for="password" class="control-label">No. of Room</label>
                                                <div class="controls">
                                                    <select name="no_of_room" id="select" class='input-large' required="">
                                                        <option value="0-5" <?= ($accomRow['no_of_rooms'] == '0-5') ? 'selected' : ''?>>0-5</option>
                                                        <option value="5-10" <?= ($accomRow['no_of_rooms'] == '5-10') ? 'selected' : ''?>>5-10</option>
                                                        <option value="10-20" <?= ($accomRow['no_of_rooms'] == '10-20') ? 'selected' : ''?>>10-20</option>
                                                        <option value="20-30" <?= ($accomRow['no_of_rooms'] == '20-30') ? 'selected' : ''?>>20-30</option>
                                                        <option value="30-40" <?= ($accomRow['no_of_rooms'] == '30-40') ? 'selected' : ''?>>30-40</option>
                                                        <option value="40-50" <?= ($accomRow['no_of_rooms'] == '40-50') ? 'selected' : ''?>>40-50</option>
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
                                                                <input type="checkbox"  name="feat[]"  value="<?= $buildFeatRow['featid'] ?>"> <?= $buildFeatRow['featname'] ?>
                                                            </label>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>                                            
                                        </div>
                                        <input type="hidden" name="edit_id" value="<?= $id ?>">
                                        <div class="span12">
                                            <div class="form-actions" style="text-align: center">
                                                <button type="submit" name="submit" class="btn btn-primary btn-warning">Update</button>
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
