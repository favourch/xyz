<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,7,8,9,10,20,21,22,23";
check_auth($auth_users, $site_root);

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


$postid = -1;
if(isset($_GET['pid'])){
    $postid = $_GET['pid'];
}

$data = array();
$initial_data = array();

$postSQL  = sprintf("SELECT mp.*, mu.*,
    
                        st.fname AS  sfname, 
                        st.lname AS slname, 
                        st.mname as smname,
                        st.email as semail,
                        st.phone as sphone,
                        st_dept.deptname AS st_deptname,
                        st_col.coltitle  AS st_coltitle,
                        st.level AS slevel,
                        
                        l.fname as lfname, 
                        l.mname as  lmname, 
                        l.lname as llname,
                        l.email as lemail,
                        l.phone as lphone,
                        l_dept.deptname AS l_deptname,
                        l_col.coltitle  AS l_coltitle,
                        '-' AS llevel

                        FROM market_post mp 

                        JOIN market_user mu ON mp.user_id = mu.user_id
                        LEFT JOIN student st ON st.stdid = mu.user_id
                        LEFT JOIN programme st_prg ON st.progid = st_prg.progid
                        LEFT JOIN department st_dept ON st_prg.deptid = st_dept.deptid
                        LEFT JOIN college st_col ON st_dept.colid = st_col.colid

                        LEFT JOIN lecturer l ON l.lectid = mu.user_id
                        LEFT JOIN department l_dept ON l.deptid = l_dept.deptid
                        LEFT JOIN college l_col ON l_dept.colid = l_col.colid    

                        WHERE mp.postid = %s ", GetSQLValueString($postid, 'int'));

$postRS = mysql_query($postSQL, $tams) or die(mysql_error());
$post_row = mysql_fetch_assoc($postRS);
$post_num_row = mysql_num_rows($postRS);

$post_data = [
    'postid'            => $post_row['postid'],
    'post_name'         => $post_row['post_name'],
    'level'             =>($post_row['user_type_id'] == 'stud') ? $post_row['slevel'] : '' ,
    'user_type'         => $post_row['user_type_id'],
    'post_description'  => $post_row['post_description'],
    'price'             => $post_row['price'],
    'post_cat'          => $post_row['post_cat'],
    'status'            => $post_row['status'],
    'created_at'        => $post_row['created_at'],
    'user_id'           => $post_row['user_id'],
    'full_name'         => ($post_row['user_type_id'] == 'stud') ? $post_row['sfname'].' '. $post_row['slname'] . ' '. $post_row['smname'] : $post_row['lfname'].' '. $post_row['llname'] .' '. $post_row['lmname'],
    'email'             => ($post_row['user_type_id'] == 'stud') ? $post_row['semail'] : $post_row['lemail'],
    'phone'             => ($post_row['user_type_id'] == 'stud') ? $post_row['sphone'] : $post_row['lphone'],
    'dept_name'         =>  ($post_row['user_type_id'] == 'stud') ? $post_row['st_deptname'] : $post_row['l_deptname'],
    'col_title'         =>  ($post_row['user_type_id'] == 'stud') ? $post_row['st_coltitle'] : $post_row['l_coltitle'],
];

$initial_data['rs'] = $post_data;

//Get All Image for this post
$postImgSQL = sprintf("SELECT * "
                    . "FROM market_post_img "
                    . "WHERE postid = %s ", 
                    GetSQLValueString($post_row['postid'], 'int'));
$postImgRS = mysql_query($postImgSQL, $tams) or die(mysql_error());
$post_Img_row = mysql_fetch_assoc($postImgRS);

do {
    $initial_data['img'][] = $post_Img_row;
} while ($post_Img_row = mysql_fetch_assoc($postImgRS));

//Get All review related to this product
$reviewSQL = sprintf("SELECT * "
                    . "FROM market_review mv "
                    . "JOIN market_user ms ON mv.revhead = ms.user_id "
                   . "WHERE mv.post_id = %s", 
                   GetSQLValueString($postid, 'int'));
$reviewRS = mysql_query($reviewSQL, $tams) or die(mysql_error());
$review_row = mysql_fetch_assoc($reviewRS);
$review_num_row = mysql_num_rows($reviewRS);



$rev = array();

do{
   array_push($rev, $review_row); 
}while($review_row = mysql_fetch_assoc($reviewRS));

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
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3>
                                        <i class="icon-reorder"></i>
                                        {{data.rs.post_name}}
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span6" id="slider">
                                            <div class="span12 well well-large" id="carousel-bounding-box">
                                                <div class="carousel slide" id="myCarousel">
                                                    <!-- Carousel items -->
                                                    <div class="carousel-inner">
                                                        <div class="item" data-slide-number="0" ng-repeat="ig in data.img" ng-class="{'active': $first }">
                                                            <img src="assets/imgs/{{ig.img_url}}" height="700" width="700"> 
                                                        </div>   
                                                    </div><!-- Carousel nav -->
                                                    <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                                                        <span class="glyphicon glyphicon-chevron-left"></span>                                       
                                                    </a>
                                                    <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                                                        <span class="glyphicon glyphicon-chevron-right"></span>                                       
                                                    </a>                                
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div>
                                                <div class="tabs-container">
                                                    <ul class="tabs tabs-inline tabs-left">
                                                        <li class="active">
                                                            <a data-toggle="tab" href="#first"><i class="icon-bar-chart"></i> Description</a>
                                                        </li>
                                                        <li>
                                                            <a data-toggle="tab" href="#second"><i class="icon-comments"></i>Review</a>
                                                        </li>
                                                        <li>
                                                            <a data-toggle="tab" href="#thirds"><i class="icon-user"></i>Seller </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-content padding tab-content-inline">
                                                    <div id="first" class="tab-pane active">
                                                        <h5>Product Price and Description</h5><hr/>
                                                        <h6><b>Product Price</b></h6>
                                                        <p>
                                                        <h3 class="well well-small" style="color: blue; text-align: center">{{data.rs.price | currency : '=N= ' : 2}}</h3>
                                                        </p>
                                                        <h6><b>Product Description</b></h6>
                                                        <p class="well well-small" style="text-align: center">
                                                            {{data.rs.post_description}}
                                                        </p>
                                                    </div>
                                                    <div id="second" class="tab-pane">
                                                        <h5>Product Review</h5><hr/>
                                                        <div>
                                                            <ul class="messages well well-small" style="height: 300px; overflow-x :scroll;">
                                                                <li class="left"  ng-repeat="rev in reviews track by $index | orderBy:revid">
                                                                    <div class="message" style="background-color: #ffccea">
                                                                        <span class="caret"></span>
                                                                        <span class="name">
                                                                            <a  href="../staff/profile.php?id={{rev.user_id}}" target="tabs" ng-if="rev.user_type_id == 'staff'">{{rev.revhead}}</a>
                                                                            <a  href="../student/profile.php?stid={{rev.user_id}}" target="tabs" ng-if="rev.user_type_id == 'stud'">{{rev.revhead}}</a>
                                                                        </span>
                                                                        <p>{{rev.comment}}</p>
                                                                        <span class=" small time">
                                                                            <em>{{rev.created_at}}</em>
                                                                        </span>
                                                                    </div>
                                                                </li>
                                                                <li ng-if="reviews.length == 0">
                                                                    <div class="alert alert-warning">No Comment Available for this product Yet</div>
                                                                </li>
                                                            </ul>
                                                            <div class="row-fluid">
                                                                <div class="span12">
                                                                    <div ng-if="loading">
                                                                        <span class="name">Sending message</span> please wait <img alt="" src="../img/loading.gif">
                                                                    </div>
                                                                    
                                                                    <div id="message-form">
                                                                        <div class="text">
                                                                            <textarea rows="2" ng-model="comment" class="input-block-level" placeholder="Write here..." name="comment"></textarea>
                                                                        </div>
                                                                        <div class="submit">
                                                                            <button type="button" ng-click="sendReview(comment)" ng-disabled="disable"><i class="icon-share-alt"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="thirds" class="tab-pane">
                                                        <h5>Contact Details</h5> <hr/>
                                                        <h6><b>Posted By</b></h6>
                                                        <p>
                                                        <h5 style="text-align: center; color:#802420" class="well well-small">
                                                            <a  href="../staff/profile.php?id={{data.rs.user_id}}" target="tabs"  ng-if="data.rs.user_type == 'staff'">{{data.rs.full_name}}</a>
                                                            <a  href="../student/profile.php?stid={{data.rs.user_id}}" target="tabs" ng-if="data.rs.user_type == 'stud'">{{data.rs.full_name}}</a>
                                                        </h5>
                                                        </p>
                                                        <h6><b>Phone Number</b></h6>
                                                        <p >
                                                            <h5 style="text-align: center; color:#802420" class="well well-small">{{data.rs.phone}}</h5>
                                                        </p>
                                                        <h6><b>E-mail</b></h6>
                                                        <p >
                                                            <h5 style="text-align: center; color:#802420" class="well well-small">{{data.rs.email}}</h5>
                                                        </p>
                                                        <h6><b>College</b></h6>
                                                        <p >
                                                            <h5 style="text-align: center; color:#802420" class="well well-small">{{data.rs.col_title}}</h5>
                                                        </p>
                                                        <h6><b>Department</b></h6>
                                                        <p >
                                                            <h5 style="text-align: center; color:#802420" class="well well-small">{{data.rs.dept_name}}</h5>
                                                        </p>
                                                        <h6 ng-if="data.rs.user_type == 'stud'"><b>Level</b></h6>
                                                        <p ng-if="data.rs.user_type == 'stud'">
                                                            <h5 style="text-align: center; color:#802420" class="well well-small">{{data.rs.level}}</h5>
                                                        </p>
                                                        
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
            </div>
            <?php include INCPATH."/footer.php" ?>
            <script type="text/javascript">
                var Data = <?= ($post_num_row > 0 ) ? json_encode($initial_data) : json_encode(array()) ?>;
                var Reviews = <?= ($review_num_row > 0 ) ? json_encode($rev) : json_encode(array()) ?>;
                var ActiveUser = <?= GetSQLValueString(getSessionValue('uid'), 'text') ?>;
                var PostId = <?= $postid;?>
                
                var app = angular.module('app', []);
                app.controller('PageCtrl', function($scope, $http){
                    $scope.data = Data;
                    $scope.reviews = Reviews;
                    $scope.active_user = ActiveUser;
                    $scope.postid = PostId;
                    $scope.disable = false;
                    
                    $scope.loading = false;
                   
                    $scope.getReview = function(id){
                       $scope.loading = true;  
                       $http({
                            method : "GET",
                            url : "api/index.php?action=get_reviews&id="+id
                        }).then(function mySucces(response) {
                            $scope.reviews = response.data;
                            $scope.loading = false; 
                        }, function myError(response) {
                            $scope.error = response.statusText;
                            $scope.loading = false;
                        });
                    };
                    
                    $scope.sendReview = function(content){
                        $scope.loading = true;
                        $scope.disable = true;
                        $scope.comment = '';
                        $http({
                            method : "POST",
                            url : "api/index.php?action=send_reviews",
                            data: {
                                postid :PostId,
                                comment: content,
                                who: ActiveUser
                            }
                        }).then(function mySucces(response) {
                            $scope.reviews = response.data;
                            $scope.getReview(PostId);
                            $scope.loading = false;
                            $scope.disable = false;
                        }, function myError(response) {
                            $scope.error = response.statusText;
                            $scope.loading = false;
                            $scope.disable = false;
                        });
                        
                        
                        
                    }
                });
    </script>
    </body>
    
</html>