<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1, 2, 3, 4, 5, 6, 20, 21, 22, 23, 24";
check_auth($auth_users, $site_root);

if (isset($_GET['lid'])) {
    $colname_staff = $_GET['lid'];
}
else {
    $colname_staff = getSessionValue('MM_Username');
}



$researchSQL = sprintf("SELECT * "
                    . "FROM research "
                    . "WHERE lectid = %s",
                    GetSQLValueString($colname_staff, 'text'));
$researchRS = mysql_query($researchSQL, $tams) or die(mysql_error());

$research_list = array();


while ($research = mysql_fetch_assoc($researchRS)) {

    $data = array(
        'res_id' => $research['res_id'],
        'res_title' => $research['res_title'],
        'res_abstract' => $research['res_abstract'],
        'pub_year' => $research['pub_year'],
        'res_area' => $research['res_area'],
        'pub_type' => $research['pub_type'],
        'pub_at' => $research['pub_at'],
        'volume' => $research['volume'],
        'page_num' => $research['page_num'],
        'reference' => $research['reference'],
        'upload' => $research['upload'],
    );

    array_push($research_list, $data);
}
$research_list = json_encode($research_list);


if (isset($_POST['MM_Upload']) && $_POST['MM_Upload'] == 'upload') {

    $res_id = $_POST['res_id'];

    if (isset($_FILES['filename'])) {


        define("FILEREPOSITORY", "../research/papers/");

        // create an array of permitted image MIME types
        $permitted = ['application/pdf'];

        if (in_array($_FILES['filename']['type'], $permitted)) {

            $ext = strtolower(substr($_FILES['filename']['name'], strrpos($_FILES['filename']['name'], '.')));
            $file = $res_id . $ext;

            $success = move_uploaded_file($_FILES['filename']['tmp_name'], FILEREPOSITORY . $file);

            if ($success) {

                $updateSQL = sprintf("UPDATE research SET upload = 'yes' WHERE res_id = %s ", $res_id);
                mysql_query($updateSQL, $tams) or die(mysql_error());

                $notification->set_notification('Research Paper uploaded ', 'success');
            } else {

                $notification->set_notification('Unable to upload paper ', 'error');
            }
        } else {
            $notification->set_notification('Research Paper should be uploaded in PDF format', 'error');
        }
    }
}

if (isset($_POST['MM_Submit']) && $_POST['MM_Submit'] == 'form1') {


    $insertResSQL = sprintf("INSERT "
            . "INTO research "
            . "(lectid, res_title, res_abstract, "
            . "pub_year, res_area, pub_type, pub_at, volume, page_num, reference ) "
            . "VALUE ( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s ) ", 
            GetSQLValueString($_POST['lectid'], 'text'), 
            GetSQLValueString($_POST['r_title'], 'text'),
            GetSQLValueString($_POST['r_abstract'], 'text'), 
            GetSQLValueString($_POST['pub_year'], 'text'), 
            GetSQLValueString($_POST['r_area'], 'text'), 
            GetSQLValueString($_POST['pub_type'], 'text'),
            GetSQLValueString($_POST['pub_at'], 'text'), 
            GetSQLValueString($_POST['volume'], 'text'), 
            GetSQLValueString($_POST['page_num'], 'text'), 
            GetSQLValueString($_POST['auth_name'], 'text'));
    $res = mysql_query($insertResSQL, $tams) or die(mysql_error());
    $insert_id = mysql_insert_id();
    $affected = mysql_affected_rows();

    if ($affected) {
        
        $notification->set_notification('Operation Successful', 'success');
    } else {
        $notification->set_notification('Unable to submit your research  please try again later ', 'error');
    }
    header("Location: profile.php");
    exit();
    
}

if(isset($_POST['MM_Update']) && $_POST['MM_Update'] == 'form2'){
    
    $insertResSQL = sprintf("UPDATE research "
                            . "SET lectid = %s, res_title = %s, "
                            . "res_abstract = %s, pub_year = %s, "
                            . "res_area = %s, pub_type = %s, "
                            . "pub_at = %s, volume = %s, "
                            . "page_num = %s,"
                            . "reference = %s WHERE res_id = %s" ,
                             GetSQLValueString($_POST['lectid'], 'text'),
                            GetSQLValueString($_POST['r_title'], 'text'),
                            GetSQLValueString($_POST['r_abstract'], 'text'),
                            GetSQLValueString($_POST['pub_year'], 'text'),
                            GetSQLValueString($_POST['r_area'], 'text'),
                            GetSQLValueString($_POST['pub_type'], 'text'),
                            GetSQLValueString($_POST['pub_at'], 'text'), 
                            GetSQLValueString($_POST['volume'], 'text'), 
                            GetSQLValueString($_POST['page_num'], 'text'), 
                            GetSQLValueString($_POST['reference'], 'text'),
                            GetSQLValueString($_POST['res_id'], 'text'));
    $res = mysql_query($insertResSQL, $tams) or die(mysql_error());
    $insert_id = mysql_insert_id();
    $affected = mysql_affected_rows();
    
    if ($affected) {
        
        $notification->set_notification('Operation Successfful', 'success');
    } else {
        $notification->set_notification('No Changes detected ', 'error');
    }
    header("Location: profile.php");
    exit();
}




mysql_select_db($database_tams, $tams);
$query_staff = sprintf("SELECT l.*, d.deptname, c.colname , c.colid "
                    . "FROM lecturer l, department d, college c "
                    . "WHERE d.deptid = l.deptid "
                    . "AND d.colid = c.colid "
                    . "AND lectid = %s", 
                    GetSQLValueString($colname_staff, "text"));
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}




$img_dir = "../img/user/staff";
$img_url = get_pics($colname_staff, $img_dir);

$page_title = "Tasued";
?>
<!doctype html>
<html ng-app="research">
    <?php include INCPATH."/header.php" ?>
    <script>
        var res = <?= $research_list?>
    </script>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="ResearchController">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-user"></i>
                                        <?php echo $row_staff['lname']." ".$row_staff['fname']."'s"?> Profile
                                    </h3>
                                    <ul class="tabs">
                                        <li><a href="teaching_history.php?lid=<?= $row_staff['lectid']?>" class="btn btn-small btn-blue ">Teaching History</a></li>
                                        <?php if(! isset($_GET['lid'])) { ?>
                                        <li><a href="#add_reseach" data-toggle="modal"  class="btn btn-small btn-purple" ng-click="">Add Publication</a></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="box-content nopadding">
                                    <ul class="timeline">
                                        <li>
                                            <div class="timeline-content">
                                                <div class="row-fluid">
                                                    <div class="left">
                                                        <div class="icon lightred">
                                                            <i class="icon-user"></i>
                                                        </div>
                                                    </div>
                                                    <div class="activity">
                                                        <div class="span3">
                                                            <div class="user">
                                                                <strong><?= $row_staff['lname'] . " " . $row_staff['fname'] . "'s" ?></strong>
                                                                <p><?= $row_staff['lectid']?></p>
                                                            </div>
                                                            <p>
                                                                <img class="timeline-images" style="width: 250px; height: 280px;" src="<?= $img_url?>" />
                                                            </p>
                                                        </div>
                                                        <table class="table  table-nomargin span6"> 
                                                            <div class="user"><br><br></div>
                                                            <tbody>
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <td><?= $row_staff['lname'] . " " . $row_staff['fname'] ." ".$row_staff['mname'] ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Sex</th>
                                                                    <td><?= getSex($row_staff['sex'])  ?></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <th>College</th>
                                                                    <td><a href="../college/college.php?cid=<?= $row_staff['colid']?>"><?= $row_staff['colname']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Department</th>
                                                                    <td><a href="../department/department.php?did=<?= $row_staff['deptid']?>"><?= $row_staff['deptname']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Phone</th>
                                                                    <td><a href="callto: <?= $row_staff['phone']?>"><?= $row_staff['phone']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Email</th>
                                                                    <td><a   href=" mailto:<?= $row_staff['email']?> "><?= $row_staff['email']?></a></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Address</th>
                                                                    <td><?= $row_staff['addr']?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Profile</th>
                                                                    <td><?= $row_staff['profile']  ?></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                </div>
                                                <div class="row-fluid">
                                                    <div class="span11">
                                                        <h4>Publication</h4>

                                                        <table class="table table-bordered table-condensed table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th width="5%">S/N</th>
                                                                    <th width="85%">Reference </th>
                                                                    <th width="10%">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr ng-repeat="r in research">
                                                                    <td>{{$index +1}}</td>
                                                                    <td>{{r.reference}}({{r.pub_year}}).<b>{{r.res_title}}</b> <em>{{r.pub_at}}</em> {{'Vol ' + r.volume}} {{'Page ' + r.page_num}} </td>
                                                                    <td>
                                                                        <a href="#abstract" data-toggle="modal" ng-click="setSelected(r)" title="View Abstract"><i class="icon-file"></i></a>
                                                                        <a ng-show="r.upload == 'yes'"  target="_blank"  href="../research/papers/{{r.res_id}}.pdf" title="Download Full Paper"><i class="icon-download-alt"></i></a>
                                                                        <?php if(!isset($_GET['lid'])) {?>
                                                                        <a href="#edit_research" data-toggle="modal"   ng-click="setSelected(r)" title="Edit Abstract" ><i class="icon-edit"></i></a> 
                                                                        <a href="#upload" data-toggle="modal"  ng-click="setSelected(r)" title="Upload Full Paper"><i class="icon-upload-alt"></i></a>
                                                                        <?php }?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            <div class="line"></div>
                                        </li>
                                    </ul>                                   
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <script src="../js/research_controller.js"></script>
        <?php require "../research/research_modal.php"; ?>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

