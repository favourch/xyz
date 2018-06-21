<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$_SESSION['admid'] = null;
$_SESSION['admname'] = null;

$ses_query = sprintf("SELECT * "
        . "FROM session "
        . "WHERE status = 'TRUE' "
        . "OR admission = 'TRUE'");
$session = mysql_query($ses_query, $tams);
$totalRows_session = mysql_num_rows($session);

if ($totalRows_session > 0) {
    $adm_session = null;
    while ($row_session = mysql_fetch_assoc($session)) {
        if ($row_session['status'] == 'TRUE') {
            $_SESSION['sesid'] = $row_session['sesid'];
            $_SESSION['sesname'] = $row_session['sesname'];
        }

        if ($row_session['admission'] == 'TRUE') {
            $adm_session = $row_session;
            $_SESSION['admid'] = $row_session['sesid'];
            $_SESSION['admname'] = $row_session['sesname'];
        }
    
	if(!is_null($adm_session)) {
	    $_SESSION['admid'] = $adm_session['sesid'];
	    $_SESSION['admname'] = $adm_session['sesname'];
        }
    }
}

$adm_query = sprintf("SELECT *, a.status as admstatus, ab.status as batchstatus "
        . "FROM admissions a "
        . "JOIN admission_type at ON a.typeid = at.typeid "
        . "LEFT JOIN application_batch ab ON a.admid = ab.admid AND ab.status ='active' "
        . "WHERE sesid = %s", GetSQLValueString($_SESSION['admid'], "int"));
$admission = mysql_query($adm_query, $tams);
$totalRows_admission = mysql_num_rows($admission);
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
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3><i class="icon-reorder"></i>
                                    Application Procedure
                                </h3>
                            </div>
                            <div class="box-content "> 
                                <?php if(getSessionValue('admid') != NULL) :?>
                                <p><h5><div class="alert alert-danger">The FINAL batch of Screening exercise will hold on Tuesday, 16th January 2018 by 8am. Application closes by Monday, 15th January 2018</div></h5></p>
                                <p>1. Apply for the Admissions process by clicking Apply Now on an open Application<br>
                                    <strong>(Please fill the form correctly and supply a VALID EMAIL ADDRESS because 
                                        your login details will be sent to whatever email address you provide)</strong>
                                </p>
                                
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Application Type</th>
                                            <th> Session Name </th>
                                            <th> Status 
                                            </th><th> Action 
                                            </th></tr>  
                                    </thead>

                                    <tbody>
                                        <?php for(;$row_admission = mysql_fetch_assoc($admission);) :
                                            $batchname = isset($row_admission['batchname'])? $row_admission['batchname']: 'No batch available';
                                        ?>
                                        <tr>
                                            <td><?php echo $row_admission['displayname'].' ('.$batchname.')'?></td>
                                            <td><?php echo getSessionValue('admname')?></td>
                                            <td><?php echo $row_admission['admstatus'] == 'TRUE'? 'OPEN': 'CLOSED'?></td>
                                            <td>
                                                <?php if($row_admission['admstatus'] == 'TRUE' && ($row_admission['batchstatus'] == 'active')) :?>
                                                <a href="crtacct.php?type=<?php echo $row_admission['appbatchid'] ?>" class="btn btn-primary btn-xs">
                                                    Click here to Apply
                                                </a>
                                                <?php endif;?>
                                            </td>
                                        </tr>
                                        <?php endfor;?>
                                    </tbody>
                                </table>
                                
                                <p>2. Check your email inbox (or Spam) for your <strong>Applicant ID </strong>and <strong>Password</strong></p>
                                <p>3. Return to the website and Click Login to access Application Form </p>
                                <p><a href="/<?php echo $site_root?>/login.php" class="btn btn-primary"> Click here to Login </a></p>
                                <p>4. Continue by paying the necessary fees and then carefully filling the Application Form and click submit</p>
                                <p>6. Print the Application form and Examination Slip with the examination date on it</p>
                                <p><strong>NOTE:</strong></p>
                                <p> Candidates should apply online with the payment of screening fee of Two thousand five hundred naira only (#2,500.00). 
                                             In addition, a portal access fee of five thousand naira only (#5,000) payable with Master card or VISA ATM card
                                                    (Please print out your receipt after payment)
                                                </p>
                                
                                <?php else :?>
                                <div class="row_fluid">
                                    <div class="span2">
                                        <img src="img/admission_closed.jpg">
                                    </div>
                                    <div class="span10">
                                        <p>
                                            Application for Admission is CLOSED at the moment. <br/><br/>Please, check back soon!
                                        </p>
                                    </div>
                                </div>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>