<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once 'inc/con.php';

$request = $_REQUEST;


if(!isset($request['user_id'])){
    die("User ID is missing you should either pass it as a post or a get request to this service ");
}

if(!isset($request['result_id'])){
    die("Result ID is missing you should either pass it as a post or a get request to this service ");
}

if(!isset($request['key'])){
    die("school public Key is missing you should either pass it as a post or a get request to this service ");
}

if(!isset($request['school'])){
    die("School ID is missing you should either pass it as a post or a get request to this service ");
}


$school_id = $request['school'];
$school_key = $request['key'];
$user_id = str_replace('/', '', $request['user_id']);
$result_id = str_replace('/', '',$request['result_id']); // a uniqe key from your school result fetching table

$query_school = sprintf("SELECT * FROM schools "
                      . "WHERE id = %s AND public_key = %s", 
                      GetSQLValueString($school_id, 'text'), 
                      GetSQLValueString($school_key, 'text'));
$school = mysql_query($query_school, $con) or die(mysql_error());
$row_school = mysql_fetch_assoc($school);
$totalRows_school = mysql_num_rows($school);



if($totalRows_school > 0){
    if($row_school['status'] == 'active'){
        $_SESSION['school_id'] = $row_school['id'];
        $_SESSION['school_abr'] = $row_school['school_abr'];
        $_SESSION['school_name'] = $row_school['school_name'];
        $_SESSION['charges'] = $row_school['charges'];
        $_SESSION['result_id'] = $result_id;
        $_SESSION['return_url'] = $row_school['return_url'];
        $_SESSION['key'] = $school_key;
        
        
        $query_user = sprintf("SELECT * FROM users "
                            . "WHERE id = %s AND school_id = %s ", 
                            GetSQLValueString($user_id, 'text'), 
                            GetSQLValueString($school_id, 'text'));
        $user = mysql_query($query_user, $con) or die(mysql_error());
        $row_user = mysql_fetch_assoc($user);
        $totalRows_user = mysql_num_rows($user);
        
        
        if($totalRows_user > 0){
            $_SESSION['user_id'] = $row_user['id'];
        }
        else{
            $insert_user = sprintf("INSERT INTO users "
                    . "(school_id, id) "
                    . "VALUE (%s, %s ) ",
                    GetSQLValueString($school_id, 'text'),
                    GetSQLValueString($user_id, 'text'));
            $user = mysql_query($insert_user, $con) or die(mysql_error());
            
            $_SESSION['user_id'] = mysql_insert_id($user);
            
        }
        
    }
    else
    {
        die("Your school account has been suspended");
    }
}
else
{
    die("There is no record for your school on our server");
}


$query_transactions = sprintf("SELECT s.id AS school_id, s.return_url, s.school_abr, u.id AS user_id, tr.* "
                            . "FROM transactions tr "
                            . "JOIN users u ON tr.user_id = u.id "
                            . "JOIN schools s ON s.id = u.school_id AND tr.school_id = s.id "
                            . "WHERE u.id = %s and s.id = %s ", 
                            GetSQLValueString($user_id, 'text'),
                            GetSQLValueString($school_id, 'text'));
$transaction = mysql_query($query_transactions, $con) or die(mysql_error());
$row_transaction = mysql_fetch_assoc($transaction);
$totalRows_transaction = mysql_num_rows($transaction);

$trans = [];
if($totalRows_transaction > 0){
    do{
        $trans[] = $row_transaction;
    }while($row_transaction = mysql_fetch_assoc($transaction));
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
                <h4><a class="nav-link js-scroll-trigger" href="#"><?= $_SESSION['school_name']?></a></h4>
            </li>
        </ul>
      </div>
    </div>
  </nav>


  <section id="about">
    <div class="container">
      <h2 class="title">Result Verification System</h2>
      
      <div class="row">
        <div class="col-lg-3">
        <nav id="sidebar">
              <!-- Sidebar Links -->
              <ul class="list-unstyled components">
                    <li class="active"><a href="#!">Home</a></li>
                    <li><a href="#!pay">pay</a></li>
                    <li><a href="#!result">Portfolio</a></li>
                    <li><a href="#">Contact</a></li>
              </ul>
        </nav>
        </div>
        <div class="col-lg-9" style="font-size:10px">
          <ng-view></ng-view>     
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
    var trans_data = <?= json_encode($trans)?>;
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