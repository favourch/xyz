<?php 
if (!isset($_SESSION)) {
  session_start();
}



require_once ('../../../phpexcel/PHPExcel/IOFactory.php');
require_once('../../../path.php');

$auth_users = "4,20";
check_auth($auth_users, $site_root);


$col_sesid = -1;
if(isset($_GET['sid'])){
    $col_sesid = $_GET['sid'];
}


$col_csid = -1;
if(isset($_GET['crs'])){
    $col_csid = $_GET['crs'];
}

//Query for select boxes in the result view
$query_score = sprintf("SELECT cr.resultid, cr.stdid, cr.tscore, st.fname, st.lname, st.mname "
                        . "FROM course_reg cr JOIN student st ON cr.stdid = st.stdid "
                        . "WHERE cr.sesid = %s "
                        . "AND cr.csid = %s ", 
                        GetSQLValueString($col_sesid, "int"), 
                        GetSQLValueString($col_csid, "text"));
$scores = mysql_query($query_score, $tams) or die(mysql_error());
$row_scores = mysql_fetch_assoc($scores);
$totalRows_scores = mysql_num_rows($scores);

$query_Rssess = "SELECT * FROM `session` where status = 'true' order by sesid desc";
$Rssess = mysql_query($query_Rssess, $tams) or die(mysql_error());
$row_Rssess = mysql_fetch_assoc($Rssess);
$totalRows_Rssess = mysql_num_rows($Rssess);


$query_Rsscs = "SELECT * FROM `course` where type = 'general' ";
$Rsscs = mysql_query($query_Rsscs, $tams) or die(mysql_error());
$row_Rsscs = mysql_fetch_assoc($Rsscs);
$totalRows_Rsscs = mysql_num_rows($Rsscs);


if (isset($_POST['submit']) && $_POST['submit'] == "Upload Test Score") { //database query to upload result	
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
   
    $allowed_type = [
    	'text/csv',
    	'text/comma-separated-values',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/octet-stream' //TODO remove, only used for testing
    ];
    
    if (is_uploaded_file($_FILES['filename']['tmp_name']) && in_array($_FILES['filename']['type'], $allowed_type)) {
        
        //Query for select boxes in the result view
        $query_exist = sprintf("SELECT cr.resultid, cr.stdid "
                                . "FROM course_reg cr "
                                . "WHERE cr.sesid = %s "
                                . "AND cr.csid=%s ", 
                                GetSQLValueString($sesid, "int"), 
                                GetSQLValueString($csid, "text"));
        $exist = mysql_query($query_exist, $tams) or die(mysql_error());
        $row_exist = mysql_fetch_assoc($exist);
        $totalRows_exist = mysql_num_rows($exist);
        $existing_courses = [];
        
        for ($idx = 0; $idx < $totalRows_exist; $idx++, $row_exist = mysql_fetch_assoc($exist)) {
            $existing_courses[$row_exist['stdid']] = $row_exist['resultid'];
        }
        
        
        
        
        //Import uploaded file to Database	
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['filename']['tmp_name']);
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        $objIterator = $objWorkSheet->getRowIterator();
       
        unset($objPHPExcel);
        unset($objWorkSheet);
        
        foreach ($objIterator as $row) {
            $stdid = (string) $row->getColumnValue(0)->getValue();
            $tscore = $row->getColumnValue(1)->getValue();
            
            
            if(array_key_exists($stdid, $existing_courses))
            {
                $updateCourseRegSQL = sprintf("UPDATE course_reg SET tscore = %s WHERE resultid = %s", 
                        GetSQLValueString($tscore, 'int'), GetSQLValueString($existing_courses[$stdid], 'int'));
                $RS = mysql_query($updateCourseRegSQL, $tams) or die(mysql_error());
                
                header(sprintf("location:%s", $editFormAction));
            }   
        }     
    }
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
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
                                    <h3>Upload General Course Test Score </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <form name="summarysheet" method="post"  action="<?= $editFormAction; ?>" enctype="multipart/form-data" >
                                            
                                            <div class="row-fluid">
                                                <div class="span3">Choose Session: </div>
                                                <div class="span4">  
                                                    <select name="sesid" id="session" onchange="sesfilt(this)" >
                                                        <option value="-1">--Session--</option>
                                                        <?php do { ?>                                                        
                                                        <option value="<?=  $row_Rssess['sesid'] ?>" <?= ($col_sesid == $row_Rssess['sesid'] ) ? 'selected' : '' ?> ><?= $row_Rssess['sesname'] ?></option>
                                                        <?php } while ($row_Rssess = mysql_fetch_assoc($Rssess));?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row-fluid">
                                                <div class="span3">Choose Course : </div>
                                                <div class="span4">   
                                                    <select name="csid" id="level" onchange="crsfilt(this)">
                                                        <option value="-1">--Course--</option>
                                                        <?php do{ ?>
                                                        <option value="<?= $row_Rsscs['csid']?>" <?= ($col_csid == $row_Rsscs['csid'] ) ? 'selected' : '' ?> ><?= $row_Rsscs['csid']?></option>
                                                        <?php }while($row_Rsscs = mysql_fetch_assoc($Rsscs))?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row-fluid">
                                                <div class="span3">CSV File: </div>
                                                <div class="span4">   
                                                    <input type="file" name="filename" >
                                                </div>
                                            </div>
                                            
                                            <input type="submit" name="submit" id="submit" value="Upload Test Score" class="btn btn-primary"/>
                                            
                                        </form>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3>Uploaded <?= $col_csid?> Test Score </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table table-bordered table-hover table stripe">
                                                <thead>
                                                    <tr>
                                                        <th width="5">#</th>
                                                        <th width="10">Matric Number</th>
                                                        <th width="80">Full Name</th>
                                                        <th width="5">Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i = 1; do{ ?>
                                                    <tr>
                                                        <td><?= $i++ ?></td>
                                                        <td><?= $row_scores['stdid']?></td>
                                                        <td><?= $row_scores['lname'] .' '.$row_scores['fname']. ' '. $row_scores['mname']?></td>
                                                        <td><?= $row_scores['tscore']?></td>
                                                    </tr>
                                                    <?php }while($row_scores = mysql_fetch_assoc($scores))?>
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