<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,7,8,9,10,20,21,22,23";
check_auth($auth_users, $site_root);

$data = array();

$userid = getSessionValue('uid');

$selerSQL = sprintf("SELECT * FROM market_seler WHERE id = %s ", GetSQLValueString($userid, 'text'));
$selerRS = mysql_query($selerSQL, $tams) or die(mysql_error());
$seler_row = mysql_fetch_assoc($selerRS);
$seler_num_row = mysql_num_rows($selerRS);

if($seler_num_row < 1){
    header('Location: enrollment.php');
    exit();
}

$catSQL = sprintf("SELECT * FROM market_cat");
$catRS = mysql_query($catSQL, $tams) or die(mysql_error());
$cat_row = mysql_fetch_assoc($catRS);


$query1 = "SELECT COUNT(postid) AS total FROM market_post mp";
$postTotalRS = mysql_query($query1, $tams) or die(mysql_error());
$post_total_row = mysql_fetch_assoc($postTotalRS);
$total_avail_page = ceil((int) $post_total_row['total'] / 50);


$postSQL = sprintf("SELECT * FROM market_post mp "
                 . "JOIN market_cat mc "
                 . "ON mp.post_cat = mc.catid JOIN market_seler ms ON ms.id = mp.seler_id");
$postRS = mysql_query($postSQL, $tams) or die(mysql_error());
$post_row = mysql_fetch_assoc($postRS);
$post_num_row = mysql_num_rows($postRS);


$initial_data = array();
do{
    $initial_data['rs'] = $post_row;
    
    $postImgSQL = sprintf("SELECT * "
                    . "FROM market_post_img "
                    . "WHERE postid = %s "
                    . "LIMIT 1 ", 
                    GetSQLValueString($post_row['postid'], 'int'));
    $postImgRS = mysql_query($postImgSQL, $tams) or die(mysql_error());
    $post_Img_row = mysql_fetch_assoc($postImgRS);
    
    $initial_data['img'] = $post_Img_row['img_url'];
    
    array_push($data, $initial_data );
    
}while($post_row = mysql_fetch_assoc($postRS));


//var_dump($data); die();

?>

<!doctype html>
<html ng-app="app">
    <?php include INCPATH."/header.php" ?>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/script.js"></script>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCtrl">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    
                    <div class="container">
                        <div class="well well-sm">
                            <strong>Category Title</strong>
                            <div class="btn-group">
                                <a href="#" id="list" class="btn btn-default btn-sm">
                                    <i class="glyphicon-list"></i> List
                                </a> 
                                <a href="#" id="grid" class="btn btn-default btn-sm">
                                    <i class="glyphicon-show_thumbnails"></i> Grid
                                </a>
                            </div>
                            <div class="pull-right">
                                <input type="text" class="input input-medium">
                                <select class="input input-medium" ng-model="cat.rs.post_cat">
                                    <option value="">--Choose--</option>
                                    <?php do{ ?>
                                    <option value="<?= $cat_row['catid']?>"><?= $cat_row['cat_name']?></option>
                                    <?php }while($cat_row = mysql_fetch_assoc($catRS))?>
                                </select>
                            </div>
                        </div>
                        <ul id="products" class="row list-group">
                            <li class="item span3" ng-repeat="dt in data track by $index">
                                <div class="thumbnail">
                                    <img class="group list-group-image" src="assets/imgs/{{dt.img}}" height="200" width="200" alt="" />
                                    <div class="caption">
                                        <h4 class="group inner list-group-item-heading">{{dt.rs.post_name}}</h4>
                                        <p class="group inner list-group-item-text" style="">
                                            {{dt.rs.post_description }}
                                        </p>
                                        <div class="row">
                                            <div class="span3">
                                                <div class="lead" style="font-weight: bold">{{dt.rs.price| currency : '=N= ' : 2}}</div>
                                            </div>
                                            <div class="span3">
                                                <a class="btn btn-success" href="details.php?pid={{dt.rs.postid}}">Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>   
                        </ul>
                        <div class="row-fluid">
                            <div class="span12" style="align-content: center">
                                <div ng-if="getMore">
                                    <span class="name">Fetching more product</span> please wait <img alt="" src="../img/loading.gif">
                                </div>
                                <input type="hidden" ng-bind="pg">
                                <button class="btn btn-block btn-purple" type="button" ng-click="loadMore(pg)" >Load More</button>
                            </div>
                        </div>
                        
                        
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
            <script type="text/javascript">
                var total_avail_page = <?= (int)$total_avail_page ?>;
                var app = angular.module('app', []);
                var data = <?= ($post_num_row > 0)? json_encode($data):array() ?>;
                
                app.controller('PageCtrl', function($scope, $http){
                    
                    $scope.pg = 1;
                    $scope.data = data;
                    $scope.getMore = false;
                    
                    $scope.getPost =  function(page){
                                           
                                            $http({
                                                    method: "GET",
                                                    url: "api/index.php?action=post&page="+page
                                                }).then(function mySucces(response) {
                                                        //$scope.post = response.data;
                                                        angular.forEach(response.data, function(val){
                                                            $scope.data.push(val);
                                                        });
                                                }, function myError(response) {

                                                }); 
                                        };
                                        
                    $scope.loadMore = function(p){
                                    $scope.getMore = true;
                                     
                                    if(p <= total_avail_page)
                                        $scope.pg ++;
                                    else
                                        $scope.pg = 1;
                                    
                                    
                                        $scope.getPost(p);
                                        $scope.getMore = false;
                                };
            });
                                            
            </script>
    </body>
</html>