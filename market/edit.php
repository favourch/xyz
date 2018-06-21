<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "1,2,3,4,5,6,7,8,9,10,20,21,22,23";
check_auth($auth_users, $site_root);

$post_id = -1;
if (isset($_GET['pid'])) {
    $post_id = $_GET['pid'];
}


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

$postSQL = sprintf("SELECT * "
                . "FROM market_post "
                . "WHERE postid = %s ",
                GetSQLValueString($post_id, 'int') );
$postRS = mysql_query($postSQL, $tams) or die(mysql_error());
$post_row = mysql_fetch_assoc($postRS);


$post_imageSQL = sprintf("SELECT * "
                        . "FROM market_post_img "
                        . "WHERE postid = %s ",
                        GetSQLValueString($post_id, 'int') );
$post_imageRS = mysql_query($post_imageSQL, $tams) or die(mysql_error());
$post_image_row = mysql_fetch_assoc($post_imageRS);


$catSQL = sprintf("SELECT * FROM market_cat");
$catRS = mysql_query($catSQL, $tams) or die(mysql_error());
$cat_row = mysql_fetch_assoc($catRS);



if (isset($_POST['MM_SUBMIT']) && $_POST['MM_SUBMIT'] == 'form1') {

    $file_array = reArrayFiles($_FILES);

    $insertSQL  = sprintf("UPDATE "
                        . "market_post "
                        . "SET post_name = %s, post_description = %s, price = %s, "
                        . "post_cat = %s, status = %s, updated_at = %s "
                        . "WHERE posid = %s", 
                        GetSQLValueString($_POST['product_name'], 'text'), 
                        GetSQLValueString($_POST['product_desc'], 'text'), 
                        GetSQLValueString($_POST['product_price'], 'text'), 
                        GetSQLValueString($_POST['product_cat'], 'text'), 
                        GetSQLValueString('sale', 'text'), 
                        GetSQLValueString(date('Y-m-d'), 'text'), 
                        GetSQLValueString($post_id, 'int')
                );
    $insertRS = mysql_query($insertSQL, $tams) or die(mysql_error());
    $affected_row = mysql_affected_rows();

    if ($insert_id) {

        $j = 0;
        $target_path = "../market/assets/imgs/";
        $name = '';
        for ($i = 0; $i < count($_FILES['filename']['name']); $i++) {
            if($_FILES['filename']['name'][$i] != ''){
                // Loop to get individual element from the array
                $validextensions = array("jpeg", "jpg", "png");
                $ext = explode('.', basename($_FILES['filename']['name'][$i]));

                $file_extension = end($ext); // Store extensions in the variable.

                $name = 'Post_' . $post_id . '_img_' . $i . "." . $file_extension;
                $j = $j + 1;

                if (($_FILES["filename"]["size"][$i] < 100000) && in_array($file_extension, $validextensions)) {

                    if (move_uploaded_file($_FILES['filename']['tmp_name'][$i], $target_path . $name)) {

                        $resizeImg = new resize($target_path . $name);
                        $resizeImg->resizeImage(100, 100, 'crop');
                        $success = $resizeImg->saveImage($target_path . $name, 100);

                        if ($success) {
                            $deleteSQL  = sprintf("DELETE "
                                                . "FROM market_post_img "
                                                . "WHERE postid = %s", 
                                                GetSQLValueString($post_id, 'int'));
                            mysql_query($deleteSQL, $tams);
                            
                            $insertImgSQL = sprintf("INSERT INTO "
                                                    . "market_post_img (postid, img_url ) "
                                                    . "VALUES (%s, %s)", 
                                                    GetSQLValueString($insert_id, 'int'), 
                                                    GetSQLValueString($name, 'text'));
                            $insertImgRS = mysql_query($insertImgSQL, $tams) or die(mysql_error());
                        }
                    } else {     //  If File Was Not Moved.
                        echo $j . ').<span id="error">please try again!.</span><br/><br/>';
                    }
                } else {     //   If File Size And File Type Was Incorrect.
                    echo $j . ').<span id="error">***Invalid file Size or Type***</span><br/><br/>';
                }
            }
        }
        header('location: mypost.php');
        exit();
    }
}
?>

<!doctype html>
<html ng-app="app">
<?php include INCPATH . "/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="PageCtrl">
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
                                    <h3>
                                        <i class="icon-reorder"></i>
                                        Sell Product
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-horizontal form-bordered" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" enctype="multipart/form-data">
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Product Title</label>
                                            <div class="controls">
                                                <input type="text" class="input-xlarge" placeholder="Text input" id="textfield" name="product_name" value="<?= $post_row['post_name']?>">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Product Category</label>
                                            <div class="controls">
                                                <select class="input input-medium" name="product_cat">
                                                    <option value="">--Choose--</option>
                                                    <?php do { ?>
                                                    <option <?= ($post_row['post_cat'] == $cat_row['catid'])? 'selected' : ''?> value="<?= $cat_row['catid'] ?>"><?= $cat_row['cat_name'] ?></option>
                                                    <?php } while ($cat_row = mysql_fetch_assoc($catRS)) ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textfield">Product Price</label>
                                            <div class="controls">
                                                <input type="number" class="input-xlarge" id="textfield" name="product_price" value="<?= $post_row['price']?>">
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label" for="textarea">Product Description</label>
                                            <div class="controls">
                                                <textarea class="input-block-level" rows="5" id="textarea" name="product_desc"><?= $post_row['post_description']?> </textarea>
                                            </div>
                                        </div>
                                                    <?php $i = 1;
                                                    do { ?>
                                            <div class="control-group">
                                                <label class="control-label" for="image1">Image <?= $i; ?></label>
                                                <div class="controls">
                                                    <div data-provides="fileupload" class="fileupload fileupload-new">
                                                        <div style="width: 200px; height: 150px;" class="fileupload-new thumbnail"><img style="width: 200px; height: 150px;" src="<?= 'assets/imgs/'.$post_image_row['img_url']?>"></div>
                                                        <div style="max-width: 200px; max-height: 150px; line-height: 20px;" class="fileupload-preview fileupload-exists thumbnail"></div>
                                                        <div>
                                                            <span class="btn btn-file">
                                                                <span class="fileupload-new">Select image</span>
                                                                <span class="fileupload-exists">Change</span>
                                                                <input type="file" multiple="multiple" name="filename[]" >
                                                            </span>
                                                            <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $i++;
                                        } while ($post_image_row = mysql_fetch_assoc($post_imageRS)); ?>                                     
                                        <div class="form-actions">
                                            <button class="btn btn-warning" type="submit">Update my Product</button>
                                            <button class="btn" type="button">Cancel</button>
                                        </div>
                                        <input type="hidden" name="MM_SUBMIT" value="form1">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
<?php include INCPATH . "/footer.php" ?>
            <script type="text/javascript">
                        var Data = <?= ($post_num_row > 0 ) ? json_encode($initial_data) : json_encode(array()) ?>;
                        var Reviews = <?= ($review_num_row > 0 ) ? json_encode($rev) : json_encode(array()) ?>;
                        var ActiveUser = <?= GetSQLValueString(getSessionValue('uid'), 'text') ?>;
                        var PostId = <?= $postid; ?>

                        var app = angular.module('app', []);
                        app.controller('PageCtrl', function ($scope, $http) {
                            $scope.data = Data;
                            $scope.reviews = Reviews;
                            $scope.active_user = ActiveUser;
                            $scope.postid = PostId;
                            $scope.disable = false;

                            $scope.loading = false;

                            $scope.getReview = function (id) {
                                $scope.loading = true;
                                $http({
                                    method: "GET",
                                    url: "api/index.php?action=get_reviews&id=" + id
                                }).then(function mySucces(response) {
                                    $scope.reviews = response.data;
                                    $scope.loading = false;
                                }, function myError(response) {
                                    $scope.error = response.statusText;
                                    $scope.loading = false;
                                });
                            };

                            $scope.sendReview = function (content) {
                                $scope.loading = true;
                                $scope.disable = true;
                                $scope.comment = '';
                                $http({
                                    method: "POST",
                                    url: "api/index.php?action=send_reviews",
                                    data: {
                                        postid: PostId,
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