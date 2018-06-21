<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,7,8,9,10,20,21,22,23";
check_auth($auth_users, $site_root);

$data = array();

$userid = getSessionValue('uid');

//Check if user already enrol
$enroledSQL = sprintf("SELECT user_id "
                    . "FROM market_user "
                    . "WHERE user_id = %s ", 
                    GetSQLValueString(getSessionValue('uid'), 'text'));
$enroledRS = mysql_query($enroledSQL, $tams) or die(mysql_error());

if (mysql_num_rows($enroledRS) < 1) {
    header('Location: enrollment.php');
    exit();
}

$catSQL = sprintf("SELECT * FROM market_cat");
$catRS = mysql_query($catSQL, $tams) or die(mysql_error());
$cat_row = mysql_fetch_assoc($catRS);




$postSQL = sprintf("SELECT * FROM market_post mp "
                . "JOIN market_cat mc "
                . "ON mp.post_cat = mc.catid  AND mp.status = 'active' "
                . "JOIN market_user ms ON ms.user_id = mp.user_id AND ms.user_id = %s", 
                GetSQLValueString($userid, 'text'));
$postRS = mysql_query($postSQL, $tams) or die(mysql_error());
$post_row = mysql_fetch_assoc($postRS);
$post_num_row = mysql_num_rows($postRS);


$initial_data = array();
do {
    $initial_data['rs'] = $post_row;

    $postImgSQL = sprintf("SELECT * "
                        . "FROM market_post_img "
                        . "WHERE postid = %s "
                        . "LIMIT 1 ", 
                        GetSQLValueString($post_row['postid'], 'int'));
    $postImgRS = mysql_query($postImgSQL, $tams) or die(mysql_error());
    $post_Img_row = mysql_fetch_assoc($postImgRS);

    $initial_data['img'] = $post_Img_row['img_url'];

    array_push($data, $initial_data);
} while ($post_row = mysql_fetch_assoc($postRS));


if(isset($_POST['delete'])){
    $deleteSQL = sprintf("UPDATE market_post "
                        . "SET status = 'deleted' "
                        . "WHERE postid = %s ",
                        GetSQLValueString($_POST['delete'], 'int'));
    $deleteRS = mysql_query($deleteSQL, $tams) or die(mysql_error());
    
    header('Location:mypost.php');
    exit();
}
?>

<!Doctype html>
<html ng-app="app">
    <?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCtrl">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>
                    <div class="container-fluid">
                        <div class="row-fluid">
                            <div class="span12">
                                <div class="box box-bordered box-color">
                                    <div class="box-title">
                                        <h3><i class="icon-money"></i>MY PRODUCT</h3>
                                    </div>
                                    <div class="box-content"> 
                                        <div class='well well-small'>
                                            <div class="row-fluid">
                                                <div class="span6">
                                                    <div class="alert alert-danger">
                                                        Kindly note that the University or the ICT Centre is 
                                                        in no way and by no means connected with any 
                                                        transaction or dealing that takes place 
                                                        through this platform. Market Users are to beware.
                                                    </div>
                                                </div>
                                                <div class="span6">
                                                    <div class="pull-right">
                                                        <input type="text" class="input input-medium" ng-model="cat.post_name">
                                                        <select class="input input-medium" ng-model="cat.post_cat">
                                                            <option value="">--Choose--</option>
                                                            <?php do { ?>
                                                                <option value="<?= $cat_row['catid'] ?>"><?= $cat_row['cat_name'] ?></option>
                                                            <?php } while ($cat_row = mysql_fetch_assoc($catRS)) ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row-fluid">
                                            <div class="span12 "> 
                                                <div class="">
                                                    <ul class="gallery">
                                                        <li ng-repeat="dt in data | filter :cat">
                                                            <div class="thumbnail" >
                                                                <a href="#">
                                                                    <img alt="" src="assets/imgs/{{dt.img}}" height="200" width="200">
                                                                </a>
                                                                <div class="caption">
                                                                    <h4 class="group inner list-group-item-heading">{{dt.rs.post_name}}</h4>
                                                                    <p class="group inner list-group-item-text" style="">
                                                                        {{dt.rs.post_description | limitTo: 20}}...
                                                                    </p>
                                                                    <p>
                                                                        <span>
                                                                            <div class="lead" style="font-weight: bold">{{dt.rs.price| currency : '=N= ' : 2}}</div>
                                                                        </span>
                                                                        <span>
                                                                            <a class="btn btn-success" href="details.php?pid={{dt.rs.postid}}">Details</a>
                                                                            <a class="btn btn-warning" href="edit.php?pid={{dt.rs.postid}}">Edit</a>
                                                                            <a ng-click="setSelected(dt.rs)" href="#modal-3" role="button" class="btn btn-danger" data-toggle="modal">Delete</a>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
        <?php include INCPATH . "/footer.php" ?>
            <div id="modal-3" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="myModalLabel">Confirm Delete Post </h3>
                </div>
                <form method="post" action="mypost.php">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            You have choose to delete Post Product <b> {{selectedItem.post_name}}. </b> <br/>
                            Are you sure you want to proceed ? 
                            <p>Click Yes to continue or No to Cancle</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
                        <button type="submit" class="btn btn-primary btn-red">Yes</button>
                    </div>
                    <input type="hidden" name="delete" value="{{selectedItem.postid}}">
                </form>
            </div>
            <script type="text/javascript">
                        var data = <?= ($post_num_row > 0 ) ? json_encode($data) : json_encode(array()) ?>;
                        var app = angular.module('app', []);
                        app.controller('PageCtrl', function ($scope) {
                            $scope.data = data;
                            
                            $scope.setSelected = function(val){
                                $scope.selectedItem = val;
                            };
                        });
            </script>
    </body>
</html>

