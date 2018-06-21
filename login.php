<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('path.php');



$loginState = false;

if (isset($_POST['username'])) {
    $loginUsername = $_POST['username'];
    $password = $_POST['upw'];
    
    $loginState = true;
    
    if ($_POST['who'] != -1)
        $loginState = doLogin($_POST['who'], $loginUsername, $password);
        
}
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="login-content">            
            <div id="advt">
                <div class="container-fluid">                      
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="row-fluid span7">
                                <ul class="unstyled">                                    
                                    <li>
                                        <img style="width: 80%;" src="img/advert/applicants.png" />
                                    </li>
                                </ul>
                            </div>
                            <div class="row-fluid span5">
                                <ul class="tiles">
                                    <li class="well highlong span7">                                        
                                        <form method="post" class="form-validate" id="test" novalidate="novalidate">
                                            <?php if ($loginState) : ?>
                                                <div class="alert alert-error">
                                                    Your username and password combination is incorrect. 
                                                    Please try again with your correct credentials!
                                                </div>
                                            <?php elseif (isset($_GET['accesscheck'])) : ?>
                                                <div class="alert alert-error">
                                                    You are not authorized to view the resource you just attempted to view. 
                                                    Please log in with an account with the right privilege! 

                                                    <?php if (isset($_SERVER['HTTP_REFERER'])) : ?>
                                                        <!--<a href="<?php echo $_SERVER['HTTP_REFERER'] ?>">Click here</a> to go back.-->
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="control-group">
                                                <div class="email controls">
                                                    <input type="text" name="username" placeholder="Username" class="input-block-level" data-rule-required="true">
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="pw controls">
                                                    <input type="password" name="upw" placeholder="Password" class="input-block-level" data-rule-required="true">
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="controls">
                                                    <select name="who" id="who" class='chosen-select input-block-level'>
                                                        <option value="">-- Login As --</option>
                                                        <option value="1">Prospective Student</option>
                                                        <option value="2">Returning Student</option>
                                                        <option value="3">Academic Staff</option>
                                                        <option value="6">Non-teaching Staff</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <div class="controls">
                                                    <input type="submit" value="Login" class="btn btn-primary btn-block">
                                                </div>
                                            </div>
                                            <div class="pull-right">
                                                <p><a href="reset_password.php">Forgot Password?</a></p>
                                            </div>
                                            <div class="pull-left">
                                                <p><a href="<?= $school_helpdesk?>" target="_blank">Helpdesk</a></p>
                                            </div>
                                        </form>
                                    </li>
                                    <li class="blue">
                                        <a href="/<?= $site_root?>/college"><span><i class="icon-globe"></i></span><span class='name'><?= $college_name?></span></a>
                                    </li>           
                                    <li class="lime">
                                        <a href="<?= $school_url?>" target="_blank"><span><i class="icon-desktop"></i></span><span class='name'> Website</span></a>
                                    </li>                               
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
    </body>
</html>