<?php
if (!isset($_SESSION)) {
    session_start();
}



require_once 'inc/con.php';

$request = $_REQUEST;


if (!isset($request['user_id'])) {
    die("User ID is missing you should either pass it as a post or a get request to this service ");
}

if (!isset($request['result_id'])) {
    die("Result ID is missing you should either pass it as a post or a get request to this service ");
}

if (!isset($request['key'])) {
    die("school public Key is missing you should either pass it as a post or a get request to this service ");
}

if (!isset($request['school'])) {
    die("School ID is missing you should either pass it as a post or a get request to this service ");
}


$school_id = $request['school'];
$school_key = $request['key'];
$user_id = str_replace('/', '', $request['user_id']);
$result_id = str_replace('/', '', $request['result_id']); // a uniqe key from your school result fetching table

$query_school = sprintf("SELECT * FROM schools "
        . "WHERE id = %s AND public_key = %s", GetSQLValueString($school_id, 'text'), GetSQLValueString($school_key, 'text'));
$school = mysql_query($query_school, $con) or die(mysql_error());
$row_school = mysql_fetch_assoc($school);
$totalRows_school = mysql_num_rows($school);



if ($totalRows_school > 0) {
    if ($row_school['status'] == 'active') {
        $_SESSION['school_id'] = $row_school['id'];
        $_SESSION['school_abr'] = $row_school['school_abr'];
        $_SESSION['school_name'] = $row_school['school_name'];
        $_SESSION['charges'] = $row_school['charges'];
        $_SESSION['result_id'] = $result_id;
        $_SESSION['return_url'] = $row_school['return_url'];
        $_SESSION['key'] = $school_key;


        $query_user = sprintf("SELECT * FROM users "
                . "WHERE id = %s AND school_id = %s ", GetSQLValueString($user_id, 'text'), GetSQLValueString($school_id, 'text'));
        $user = mysql_query($query_user, $con) or die(mysql_error());
        $row_user = mysql_fetch_assoc($user);
        $totalRows_user = mysql_num_rows($user);


        if ($totalRows_user > 0) {
            $_SESSION['user_id'] = $row_user['id'];
        } else {
            $insert_user = sprintf("INSERT INTO users "
                    . "(school_id, id) "
                    . "VALUE (%s, %s ) ", GetSQLValueString($school_id, 'text'), GetSQLValueString($user_id, 'text'));
            $user = mysql_query($insert_user, $con) or die(mysql_error());
            
        

            $_SESSION['user_id'] = $user_id;
        }
    } else {
        die("Your school account has been suspended");
    }
} else {
    die("There is no record for your school on our server");
}


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if(isset($_POST['pay_status'])){
    header(sprintf("Location: %s", $editFormAction));
    die();
}


$query_transactions = sprintf("SELECT s.id AS school_id, s.return_url, s.school_abr, u.id AS user_id, tr.* "
        . "FROM transactions tr "
        . "JOIN users u ON tr.user_id = u.id "
        . "JOIN schools s ON s.id = u.school_id AND tr.school_id = s.id "
        . "WHERE u.id = %s and s.id = %s ", GetSQLValueString($user_id, 'text'), GetSQLValueString($school_id, 'text'));
$transaction = mysql_query($query_transactions, $con) or die(mysql_error());
$row_transaction = mysql_fetch_assoc($transaction);
$totalRows_transaction = mysql_num_rows($transaction);

$trans = [];
if ($totalRows_transaction > 0) {
    do {
        $row_transaction['trans_status'] = getPayStatus($row_transaction['status']);
        $trans[] = $row_transaction;
    } while ($row_transaction = mysql_fetch_assoc($transaction));
}
?>
<!DOCTYPE html>
<html lang="en" ng-app="myApp">

    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>O&apos;Level Result.io</title>

        <!-- Bootstrap core CSS -->
        <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!-- Custom styles for this template -->
        <link href="../css/scrolling-nav.css" rel="stylesheet">

    </head>

    <body id="page-top" ng-controller="pageCtrl" style="font-size:12px">

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand js-scroll-trigger" href="#page-top">O&apos;Level Result.io</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <h4><a class="nav-link js-scroll-trigger" href="#"><?= $_SESSION['school_name'] ?></a></h4>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>


        <section id="about">
            <div class="container">
                <h2 class="title">Result Verification System</h2>
                <div class="row">
                    <div class="col-lg-12" style="font-size:12px">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>ESL payment gateway</h4>
                                        <ul class="nav justify-content-end">
                                            <li class="nav-item">
                                                <button type="button" class="btn btn-primary btn-sm"  onclick="return  popitup('cashenvoy/index.php')">
                                                    Pay
                                                </button>
                                            </li>
                                        </ul> 
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-sm " ng-cloak="true">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>School</th>
                                                        <th>User ID</th> 
                                                        <th>Result ID</th> 
                                                        <th>Ref.</th>
                                                        <th>Trans. Status</th>
                                                        <th>Amount </th>
                                                        <th>Date Fetched</th>
                                                        <th>&nbsp;</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="" ng-repeat="tr in trans" ng-if="trans.length > 0" >
                                                        <td>{{$index + 1}}</td>
                                                        <td>{{tr.school_abr}}</td>
                                                        <td>{{tr.user_id}}</td>
                                                        <td>{{tr.result_id}}</td>
                                                        <td>{{tr.ref}}</td>
                                                        <td>{{tr.trans_status}}</td>
                                                        <td>{{tr.amount}} </td>
                                                        <td>
                                                            <div ng-if="tr.result_json">
                                                                {{tr.updated_at}} 
                                                            </div>
                                                            
                                                        </td>
                                                        <td>
                                                            <div ng-if="tr.status == 'C00'">
                                                                <a ng-if="tr.pay_used == 'no'" ng-click="setCurrent(tr)" href="" data-toggle="modal" data-target="#fetch_result">fetch</a>
                                                                &nbsp;&nbsp;
                                                                <a href="receipt.php">receipt</a>  
                                                                &nbsp;&nbsp;
                                                                <a href="" ng-if="tr.result_json" ng-click="getResult(tr)" data-toggle="modal" data-target="#view_result" >view</a>
                                                            </div>
                                                            <div ng-if="tr.status != 'C00'">
                                                                <form method="post" action="../client_area/cashenvoy/response.php">
                                                                    <input type="hidden" name="ref" value="{{tr.ref}}">
                                                                    <button type="submit" name="pay_status">Check Pay Status</button>
                                                                </form>
                                                                  
                                                            </div>
                                                        </td>

                                                    </tr>
                                                    <tr ng-if="trans.length < 1">
                                                        <td colspan="8"><div class="alert alert-warning">You have not made any payment for result verification click the above pay button </div></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                    <div class="card-footer">Footer</div>
                                </div>
                            </div>
                        </div>




                        <!-- View Result Modal -->
                        <div class="modal fade" id="view_result">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h4 class="modal-title">Result</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                                    <!-- Modal body -->
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <b>Exam Name :</b> {{result.exam_name}} <br/>
                                                <b>Exam Type :</b> {{result.exam_type}} <br/>
                                                <b>Exam Year :</b> {{result.exam_year}} <br/>
                                                <b>Exam Number :</b> {{result.exam_number}} <br/>
                                                <b>Candidate Name :</b> {{result.candidate_name}} <br/>
                                                <b>Exam Center :</b> {{result.exam_center}} <br/>
                                                <br/>
                                                <b>Subject/Score</b> 
                                                <table class="table table-sm ">
                                                    <tbody>
                                                        <tr ng-repeat="rs in result.result">
                                                            <td>{{rs.subject}}</td>
                                                            <td>{{rs.score}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Modal footer -->
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ESL Payment Modal -->
                        <div class="modal fade" id="fetch_result">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <!-- Modal Header -->
                                    <div class="modal-header">
                                        <h4 class="modal-title">Fetch O&apos;Level Result</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                                    <!-- Modal body -->
                                    <div class="modal-body">
                                        <div class="align-content-center" ng-if="loader">
                                            <b>processing, {{actions}} please wait </b> <img src="../img/loading.gif">
                                        </div>
                                        <div class="row" ng-if="fetched_result">
                                            <div class="col-md-12">
                                                <b>Exam Name :</b> {{fetched_result.exam_name}} <br/>
                                                <b>Exam Type :</b> {{fetched_result.exam_type}} <br/>
                                                <b>Exam Year :</b> {{fetched_result.exam_year}} <br/>
                                                <b>Exam Number :</b> {{fetched_result.exam_number}} <br/>
                                                <b>Candidate Name :</b> {{fetched_result.candidate_name}} <br/>
                                                <b>Exam Center :</b> {{fetched_result.exam_center}} <br/>
                                                <br/>
                                                <b>Subject/Score</b> 
                                                <table class="table table-sm ">
                                                    <tbody>
                                                        <tr ng-repeat="rs in fetched_result.result">
                                                            <td>{{rs.subject}}</td>
                                                            <td>{{rs.score}}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <div class="alert alert-info">
                                                    <p>Is this  O&apos;Level result actually yours ? </p>
                                                </div>

                                            </div>
                                        </div>
                                        <form novalidate name="olevel_form" ng-show="fetch_form">
                                            <div class="form-control">
                                                <div class="form-group">
                                                    <label for="sel1">Exam:</label>
                                                    <select class="form-control" name="exam_name" ng-model="olevel.exam_name" required="">
                                                        <option value="">--Choose--</option>
                                                        <option value="waec">WAEC</option>
                                                        <option value="neco">NECO</option>
                                                        <option value="nabteb">NABTEB</option>
                                                    </select>
                                                </div> 
                                                <div class="form-group">
                                                    <label for="sel1">Exam Type:</label>
                                                    <select class="form-control" name="exam_type" ng-if="olevel.exam_name == 'waec'" ng-model="olevel.exam_type" required="">
                                                        <option value="MAY/JUN">SCHOOL CANDIDATE RESULTS</option>
                                                        <option value="NOV/DEC">PRIVATE CANDIDATE RESULTS</option>
                                                    </select>
                                                    <select class="form-control" name="exam_type" ng-if="olevel.exam_name == 'neco'" ng-model="olevel.exam_type" required="">
                                                        <option value="1">June / July</option>
                                                        <option value="2">Nov / Dec</option>
                                                        <option value="3">BECE</option>
                                                        <option value="4">NCEE</option>
                                                    </select>
                                                    <select class="form-control" name="exam_type" ng-if="olevel.exam_name == 'nabteb'" ng-model="olevel.exam_type" required="">
                                                        <option value="01" selected="">MAY/JUN</option>
                                                        <option value="02">NOV/DEC</option>
                                                        <option value="03">Modular (March)</option>
                                                        <option value="04">Modular (December)</option>
                                                        <option value="05">Modular (July)</option>
                                                    </select>
                                                </div> 
                                                <div class="form-group">
                                                    <label for="pwd">Exam Year.:</label>
                                                    <input type="text"  class="form-control" id="exam_year" ng-model="olevel.exam_year">
                                                </div>
                                                <div class="form-group">
                                                    <label for="pwd">Exam No.:</label>
                                                    <input type="text" class="form-control" id="exam_num" ng-model="olevel.exam_num">
                                                </div>
                                                <div class="form-group">
                                                    <label for="pwd">Card pin.:</label>
                                                    <input type="text" class="form-control" id="card_pin" ng-model="olevel.card_pin">
                                                </div>
                                                <div class="form-group">
                                                    <label for="pwd">Card Sn.:</label>
                                                    <input type="text" class="form-control" id="card_sn" ng-model="olevel.card_sn">
                                                </div>
                                            </div>
                                        </form>  
                                    </div>

                                    <!-- Modal footer -->
                                    <div class="modal-footer">

                                        <button type="button" ng-show="fetch_form" ng-click="fetchResult(olevel)" class="btn btn-success btn-sm" >Submit</button>

                                        <button type="button" ng-if="fetched_result" ng-click="saveResult(fetched_result)" class="btn btn-success btn-sm" >Yes</button>
                                        <button type="button" ng-if="fetched_result" ng-click="resetForm()" class="btn btn-warning btn-sm" >No</button>

                                        <button type="button"  class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- Footer -->
        <footer class="py-5 bg-dark">
            <div class="container">
                <p class="m-0 text-center text-white">Copyright &copy; Your Website 2017</p>
            </div>
            <!-- /.container -->
        </footer>

        <!-- Angularjs library -->
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular-route.js"></script>

        <script>
            var trans_data = <?= json_encode($trans) ?>;
        </script>
        <!-- App contrller -->
        <script src="../js/app.js"></script>

        <!-- Bootstrap core JavaScript -->
        <script src="../vendor/jquery/jquery.min.js"></script>
        <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- Plugin JavaScript -->
        <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

        <!-- Custom JavaScript for this theme -->
        <script src="../js/scrolling-nav.js"></script>


    </body>

</html>