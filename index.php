au<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('path.php');
require_once('param/param.php');

$logoutAction = $_SERVER['PHP_SELF'] . "?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")) {

    $logoutAction .="&" . htmlentities($_SERVER['QUERY_STRING']);
}

$query_session = sprintf("SELECT * "
                        . "FROM session "
                        . "WHERE admission = 'TRUE' ");
$ses = mysql_query($query_session, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);


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
                            <div class="box-content">
                                <div class="span7">                                                                        
                                    <div class="box box-bordered box-color ">
                                        <div class="box-title">
                                            <h3>
                                                <i class="icon-bullhorn"></i>
                                                Notifications
                                            </h3>
                                        </div>
                                        <div class="box-content">
                                        <?php if ($row_ses) { ?>
                                            <div class="span5" style="text-align: center;">
                                                <a href="/<?= $site_root?>/admission/index.php"> <img style="width: 100%;" src="img/advert/applynow.png" /></a>
                                                <p style=" font-weight: bold; font-size: 24px"><?= $row_ses['sesname']?></p>
                                                <p><h5><div class="alert alert-danger">The FINAL batch of Screening exercise will hold on Tuesday, 16th January 2018 by 8am. Application closes by Monday, 15th January 2018</p></div></h5></p>
                                            </div>
                                            <?php } else { ?>
                                            <div class="span5" style="text-align: center;">
                                                <a href="/<?= $site_root?>/admission/index.php"> <img style="width: 100%;" src="img/logo/school-logo.png" /></a>
                                            </div>
                                            
                                            <?php }?>
                                             
                                           <div class="span7" style="text-align: justify;">
                                           <p style=" font-weight: bold; font-size: 20px"> THOSE WHO MAY APPLY</p>
                                                                                       
                                          <!-- <p> Candidates who chose TASUED as their <u>first most preferred</u> institution in the  <?php echo $year[0]?> Unified Tertiary  Matriculation Examination (UTME), and scored a minimum of <strong>180</strong> in <?php echo $year[0]?> UTME.</p> -->
 <p>UTME and Direct Entry candidates with A level/OND/HND/NCE/JUPEB qualification who chose TASUED as first choice institution or seeking a change of institution to TASUED through JAMB and have obtained 2017 Direct Entry JAMB form.
                                               </p>     
<p>Each candidate is required to make the payment of N2,500 for the screening exercise and N5,000 for the portal access fee, payable online with either Master card or VISA ATM card.</p>
                                                    
                                                 <br /> <br />

<p><a href="docs/2016-2017-BROCHURE.pdf" target="_blank" class="btn btn-primary btn-xs">Download the 2017/2018 Brochure</a></p>                                        
                                                    
                                                                                       
                                                
                                            </div>
                                        </div>
                                    </div>                                    
                                </div>
                                <div class="span5 box">
                                    <div class="box-title">
                                        <h3>
                                            <i class="icon-link"></i>
                                            Quick Links
                                        </h3>
                                    </div>
                                    <div class="box-content span10">
                                        <ul class="tiles">
                                            <?php if (getLogin()){?>
                                            <li class="orange">
                                                <a href="<?= $logoutAction ?>"><span><i class="icon-lock"></i></span><span class="name">Log Out</span></a>
                                            </li>
                                            <?php }else{?>
                                            <li class="orange">
                                                <a href="/<?= $site_root ?>/login.php"><span><i class="icon-lock"></i></span><span class="name">Login</span></a>
                                            </li>
                                            <?php }?>
                                            <li class="blue">
                                                <a href="/<?= $site_root?>/admission/index.php" ><span><i class="icon-building"></i></span><span class="name">Admission</span></a>
                                            </li>
                                            <li class="teal">
                                                <a href="<?= $school_url?>" target="_blank"><span class="count"><i class="icon-globe"></i></span><span class="name">Website</span></a>
                                            </li>
                                            <li class="lime">
                                                <a href="<?= $school_helpdesk?>" target="_blank"><span class="count"><i class="icon-question-sign"></i></span><span class="name">HelpDesk</span></a>
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
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>