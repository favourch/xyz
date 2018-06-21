<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

if (isset($_POST['username'])) {
    $loginUsername = $_POST['username'];
    $password = $_POST['upw'];
    $MM_fldUserAuthorization = "access";
    $MM_redirecttoReferrer = false;
    
    $loginState = true;
    
    $loginState = doLogin(5, $loginUsername, $password);
        
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <!-- Apple devices fullscreen -->
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <!-- Apple devices fullscreen -->
        <meta names="apple-mobile-web-app-status-bar-style" content="black-translucent" />

        <title><?php echo $university?></title>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <!-- Bootstrap responsive -->
        <link rel="stylesheet" href="../css/bootstrap-responsive.min.css">
        <!-- icheck -->
        <link rel="stylesheet" href="../css/plugins/icheck/all.css">

        <link href="../css/plugins/chosen/chosen.css" rel="stylesheet">
        <!-- Theme CSS -->
        <link rel="stylesheet" href="../css/style.css">
        <!-- Color CSS -->
        <link rel="stylesheet" href="../css/themes.css">


        <!-- jQuery -->
        <script src="../js/jquery.min.js"></script>

        <!-- Nice Scroll -->
        <script src="../js/plugins/nicescroll/jquery.nicescroll.min.js"></script>
        <!-- Validation -->
        <script src="../js/plugins/validation/jquery.validate.min.js"></script>
        <script src="../js/plugins/validation/additional-methods.min.js"></script>
        <!-- icheck -->
        <script src="../js/plugins/icheck/jquery.icheck.min.js"></script>

        <script src="../js/plugins/chosen/chosen.jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/eakroko.js"></script>

        <!--[if lte IE 9]>
                <script src="../js/plugins/placeholder/jquery.placeholder.min.js"></script>
                <script>
                        $(document).ready(function() {
                                $('input, textarea').placeholder();
                        });
                </script>
        <![endif]-->


        <!-- Favicon -->
        <link rel="shortcut icon" href="../img/favicon.ico" />
        <!-- Apple devices Homescreen icon -->
        <link rel="apple-touch-icon-precomposed" href="../img/apple-touch-icon-precomposed.png" />

    </head>

    <body class='login'>
        <div class="wrapper">
            <h1>
                <a>
                    <img src="../img/logo-big.png" alt="" class='retina-ready' width="59" height="49">
                        <?php echo $school_short_name?>
                </a>
            </h1>
            <div class="login-body">
                
                <h2>SIGN IN</h2>
                <?php if(isset($_GET['accesscheck'])) :?>
                <div class="alert alert-error">
                    You are not authorized to view the resource you just attempted to view. 
                    Please log in with an account with the right privilege! 
                    
                    <?php if(isset($_SERVER['HTTP_REFERER'])) :?>
                    <a href="<?php echo $_SERVER['HTTP_REFERER']?>">Click here</a> to go back.
                    <?php endif;?>
                </div>
                <?php endif;?>
                <form method='post' class='form-validate'>
                    <div class="control-group">
                        <div class="email controls">
                            <input type="text" name='username' placeholder="Enter your username" class='input-block-level' data-rule-required="true">
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="pw controls">
                            <input type="password" name="upw" placeholder="Enter your password" class='input-block-level' data-rule-required="true">
                        </div>
                    </div>
                    <div class="submit">
                        <div class="remember">
                            <input type="checkbox" name="remember" class='icheck-me' data-skin="square" data-color="blue" id="remember"> <label for="remember">Remember me</label>
                        </div>
                        <input type="submit" value="Sign me in" class='btn btn-primary'>
                    </div>
                </form>
                <div class="forget">
                    <a href="reset_password.php"><span>Forgot password?</span></a>
                </div>
            </div>
        </div>

    </body>

</html>
