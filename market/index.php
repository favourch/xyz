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
        . "WHERE user_id = %s ", GetSQLValueString(getSessionValue('uid'), 'text'));
$enroledRS = mysql_query($enroledSQL, $tams) or die(mysql_error());

if (mysql_num_rows($enroledRS) < 1) {
    header('Location: enrollment.php');
    exit();
}

$catSQL = sprintf("SELECT * FROM market_cat");
$catRS = mysql_query($catSQL, $tams) or die(mysql_error());
$cat_row = mysql_fetch_assoc($catRS);


$query1 = "SELECT COUNT(postid) AS total FROM market_post WHERE status = 'active' ";
$postTotalRS = mysql_query($query1, $tams) or die(mysql_error());
$post_total_row = mysql_fetch_assoc($postTotalRS);
$total_avail_page = ceil((int) $post_total_row['total'] / 50);


$postSQL = sprintf("SELECT * "
                . "FROM market_post mp "
                . "JOIN market_post_img mpi "
                . "ON mp.postid = mpi.postid  AND mp.status = 'active' "
                . "GROUP BY mp.postid ");
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

?>

<!doctype html>
<html ng-app="app">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCtrl">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>
                    <div class="container-fluid">
                        <div class="row-fluid">
                            <div class="span12">
                                <div class="box box-bordered box-color">
                                    <div class="box-title">
                                        <h3><i class="icon-money"></i>Feature Product</h3>
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
                                        <?php if($post_total_row['total'] > 0) { ?>
                                        <div class="row-fluid">
                                            <div class="span12 "> 
                                                
                                                <div class="" ng-if="listingPost.length > 0">
                                                    <ul class="gallery">
                                                        <li ng-repeat="dt in listingPost | filter:cat">
                                                            <div class="thumbnail" >
                                                                <a href="#">
                                                                    <img alt="" src="assets/imgs/{{dt.img_url}}" height="200" width="200">
                                                                </a>
                                                                <div class="caption">
                                                                    <h4 class="group inner list-group-item-heading">{{dt.post_name}}</h4>
                                                                    <p class="group inner list-group-item-text" style="">
                                                                        {{dt.post_description | limitTo: 30}}...
                                                                    </p>
                                                                    <p>
                                                                        <span>
                                                                            <div class="lead" style="font-weight: bold">{{dt.price| currency : '=N= ' : 2}}</div>
                                                                        </span>
                                                                        <span>
                                                                            <a class="btn btn-success btn-small" href="details.php?pid={{dt.postid}}">Details</a>
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <p>&nbsp;</p>
                                        <div class="row-fluid">
                                            <div class="span12" style="align-content: center">
                                                <div ng-if="loading">
                                                    <span class="name">Fetching more product</span> please wait <img alt="" src="../img/loading.gif">
                                                </div>
                                                <button ng-if="!loading;" class="btn btn-block btn-purple" type="button" ng-click="loadMore()" >Load More</button>
                                            </div>
                                        </div>
                                        <?php } else {?>
                                        <div class="row-fluid">
                                            <div class="span12">
                                                <div class="alert alert-warning">No Post Available Yet</div>
                                            </div>
                                        </div>
                                        <?php }?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
            <script type="text/javascript">
                var total_avail_page = <?= (int)$total_avail_page ?>;
                var data = <?= ($post_num_row > 0) ? json_encode($data):array() ?>;
                
                var app = angular.module('app', []);
                
                //Angular Service 
                app.service('PostService', function($http){
                    return {
                        getPost: function(page){
                             return $http.get("api/index.php?action=get_post&page="+page);
                        }
                    };
                    
                });
                
                //Angular Controller 
                app.controller('PageCtrl', function($scope,  $http, $log, PostService){
                    $scope.listingPost = [];
                    $scope.getPostList = function(page){
                        var promise = PostService.getPost(page);
                        promise.then(
                            function(payload){
                                angular.forEach(payload.data, function(val){
                                    $scope.listingPost.push(val);
                                });
                                $scope.loading = false;
                            },
                            function(errorPayload) {
                                $log.error('failure loading Post', errorPayload);
                                $scope.loading = false;
                            }
                        ); 
                        console.log($scope.listingPost);
                    };
                    
                    $scope.getPostList(1);
                    $scope.pg = 1;
                    
                    $scope.loading = false;
                                       
                    $scope.loadMore = function(){
                        $scope.loading = true;
                        $scope.pg++;
                        $scope.getPostList($scope.pg);  
                        
                    };
            });
                                            
            </script>
    </body>
</html>