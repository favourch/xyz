<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../path.php');

$auth_users = "1,20, 22, 26,23,24,28";
check_auth($auth_users, $site_root.'/login');

unset($_SESSION['olv_veri_col']);


if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}
$deptname = "";



$page_title = "Tasued";
?>
<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
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
                                    <h3><i class="icon-print"></i>
                                        O'Level Result Verification Module
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <ul class="tiles"> 
                                                <?php if (in_array(getAccess(), ['20', '24', '26'])) { ?>
                                                    <li class="purple">
                                                        <a href="veri_code/index.php">
                                                            <span><i class="icon-barcode"></i></span>
                                                            <span class="name">Release Code</span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                                <?php if (in_array(getAccess(), ['20', '22','28'])) { ?>
                                                    <li class="teal">
                                                        <a href="print/">
                                                            <span><i class="icon-print"></i></span>
                                                            <span class="name">Print O'Level</span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                                
                                                <?php if (in_array(getAccess(), ['20', '24'])) { ?>
                                                    <li class="blue">
                                                        <a href="referred/index.php">
                                                            <span><i class=" icon-book"></i></span>
                                                            <span class="name">Referred to Admin</span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                                    
                                                <?php if (in_array(getAccess(), ['20', '26'])) { ?>
                                                    <li class="green">
                                                        <a href="green_file/index.php">
                                                            <span><i class=" icon-file"></i></span>
                                                            <span class="name">Process Green File</span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
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
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>

