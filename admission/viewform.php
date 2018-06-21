<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



$auth_users = "1,11,20,21,22,23,24,28";
check_auth($auth_users, $site_root);

$jambregid = getSessionValue('uid');
if(isset($_GET['stid']) && in_array(getAccess(), [1,20,21,22,23,24,28])) {
    $jambregid = $_GET['stid'];
}

$query_rspros = sprintf("SELECT p.*, s.sesid, st.stname, formsubmit, a.admid, regtype, pr.progname AS prog1, "
                        . "pr2.progname AS prog2, at.typename, s1.subjname as jamb1, s2.subjname as jamb2, s3.subjname as jamb3, s4.subjname as jamb4 "
                        . "FROM prospective p "
                        . "LEFT JOIN admissions a ON p.admid = a.admid "
                        . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                        . "LEFT JOIN session s ON a.sesid = s.sesid "
                        . "LEFT JOIN programme pr ON p.progid1 = pr.progid "
                        . "LEFT JOIN programme pr2 ON p.progid2 = pr2.progid "
                        . "LEFT JOIN subject s1 ON p.jambsubj1 = s1.subjid "
                        . "LEFT JOIN subject s2 ON p.jambsubj2 = s2.subjid "
                        . "LEFT JOIN subject s3 ON p.jambsubj3 = s3.subjid "
                        . "LEFT JOIN subject s4 ON p.jambsubj4 = s4.subjid "
                        . "LEFT JOIN state st ON st.stid = p.stid "
                        . "WHERE p.jambregid = %s", 
                        GetSQLValueString($jambregid, "text"));
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros); 


$sesid = $row_rspros['sesid'];

$query_info = sprintf("SELECT * "
        . "FROM payschedule "
        . "WHERE level = '0' "
        . "AND sesid = %s "
        . "AND admid = %s "
        . "AND status = %s "
        . "AND payhead = %s",
        GetSQLValueString($sesid, 'int'), 
        GetSQLValueString($row_rspros['admid'], 'text'), 
        GetSQLValueString($row_rspros['regtype'], 'text'),
        GetSQLValueString('app', 'text'));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$amt = $row_info['amount'];

$pay_status = checkPaymentPros($sesid, $jambregid, $amt);
if (!$pay_status['status']) {
    header('Location: admission_payment/index.php');
    exit;
}

if ($row_rspros['formsubmit'] == 'No' && !in_array(getAccess(), [20, 24, 28])) {
    header('Location: appform.php');
    exit;
}

$jambtotal = ($row_rspros['jambscore1'] + $row_rspros['jambscore2'] + $row_rspros['jambscore3'] + $row_rspros['jambscore4']);

$query_rssit1 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='first'", GetSQLValueString($jambregid, "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);

$query_rssit2 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        JOIN subject s ON l.subject = s.subjid 
                        JOIN grade g ON l.grade = g.grdid 
                        WHERE o.jambregid=%s
                        AND sitting='second'", GetSQLValueString($jambregid, "text"));
$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);

if (isset($_POST['frmsubmit'])) {
    $query_update = sprintf("UPDATE prospective SET formsubmit =%s WHERE jambregid=%s", GetSQLValueString("Yes", "text"), GetSQLValueString(getSessionValue('uid'), "text"));
    $update = mysql_query($query_update, $tams) or die(mysql_error());

    $updateGoTo = "viewform.php";
    if (isset($_SERVER['QUERY_STRING'])) {
        $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
        $updateGoTo .= $_SERVER['QUERY_STRING'];
    }

    header(sprintf("Location: %s", $updateGoTo));
    exit;
}

$ses_folder = explode('/', $_SESSION['admname']);
$image_url = get_pics($jambregid, "../img/user/prospective/{$ses_folder[0]}");
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
<!--                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="more-login.html">Home</a><i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="student.php">Profile</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>-->

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        <?php echo $row_rspros['typename']?> Application Form
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <strong>Bio Data</strong>
                                    <div class="well">
                                        <table class="table table-striped table-bordered table-hover">                                          
                                            <tbody>
                                                <tr>
                                                    <th width="100">Surname :</th>
                                                    <td><?php echo $row_rspros['lname']?></td>
                                                    <td rowspan="5" colspan="2"> 
                                                        <img width="160" height="160" align="top" name="placeholder" id="placeholder" alt="Image" src="<?php echo $image_url;?>" style="alignment-adjust: central"></td> 
                                                </tr>
                                                <tr>
                                                    <th>First Name :</th>
                                                    <td><?php echo $row_rspros['fname']?></td>
                                                </tr>
                                                <tr>
                                                    <th>Middle Name :</th>
                                                    <td><?php echo $row_rspros['mname']?></td>
                                                </tr>
                                                <tr>
                                                    <th>Email :</th>
                                                    <td><?php echo $row_rspros['email']?></td>
                                                </tr>
                                                <tr>
                                                    <th>Phone :</th>
                                                    <td><?php echo $row_rspros['phone']?></td>
                                                </tr>
                                                <tr>
                                                    <th>Address :</th>
                                                    <td><?php echo $row_rspros['address'] ?></td>
                                                    <td><strong>State of Origin : </strong><?php echo $row_rspros['stname'] ?></td>
                                                    <td><strong>Sex : </strong><?php echo ucfirst($row_rspros['Sex']); ?> </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div><br>

                                    <?php if($row_rspros['admid']== 2) : ?>
                                   <strong>UTME RESULT</strong>
                                    <div class="well">
                                        <table class="table table-hover table-striped table-bordered">
                                            <thead>

                                            </thead>
                                            <tbody>                                            
                                                <tr>
                                                    <td>UTME Reg No. :</td>
                                                    <td><?php echo $row_rspros['jambregid']?></td>
                                                </tr>
                                                <tr>
                                                    <td>UTME Year. : </td>
                                                    <td><?php echo $row_rspros['jambyear']?></td>
                                                </tr>
                                                <tr>
                                                    <th align="center" colspan="2">Subjects / Scores </th>

                                                </tr>
                                                <tr>
                                                    <td><?php echo $row_rspros['jamb1']?></td>
                                                    <td><?php echo $row_rspros['jambscore1']?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $row_rspros['jamb2']?></td>
                                                    <td><?php echo $row_rspros['jambscore2']?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $row_rspros['jamb3']?></td>
                                                    <td><?php echo $row_rspros['jambscore3']?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $row_rspros['jamb4']?></td>
                                                    <td><?php echo $row_rspros['jambscore4']?></td>
                                                </tr>
                                                <tr>
                                                    <th>Total </th>
                                                    <td style="color:green; font-weight: bold"><?php echo $jambtotal?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div><br>
                                    <?php elseif($row_rspros['admid'] == 1) :?>
                                 <strong>DIRENT ENTRY</strong>
                                    <div class="well">
                                        <table width="320" class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th colspan="2"> DIRECT ENTRY </th>
                                            </tr>
                                            <tr>
                                                <td>UTME Reg No.</td>
                                                <td align="left"><?php echo $row_rspros['jambregid'] ?></td>
                                            </tr>
                                            <tr>
                                                <td>UTME Year.</td>
                                                <td align="left"><?php echo $row_rspros['jambyear'] ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"style="font-weight: bold" align="center"> Previous Qualification </td>
                                            </tr>
                                            <tr>
                                                <td>School Name :</td>
                                                <td align="left"><?php echo $row_rspros['deschname'] ?></td>
                                            </tr>
                                            <tr>
                                                <td>Graduation year :</td>
                                                <td align="left"><?php echo $row_rspros['degradyear'] ?></td>
                                            </tr>
                                            <tr>
                                                <td>Grade : </td>
                                                <td align="left">
                                                    <?php echo getDeGrade($row_rspros['degrade']); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div><br/>
                                    <?php endif;?>
                                    
                                    <strong>Programme Choices</strong>
                                    <div class='well'>
                                        <table class='table table-hover table-striped table-bordered'>                                            
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        1st Choice of Programme
                                                    </td> 
                                                    <td>
                                                        <?php echo $row_rspros['prog1']?>
                                                    </td>                                                                                                                                       
                                                </tr>
                                                <tr>
                                                    <td>
                                                        2nd Choice of Programme
                                                    </td> 
                                                    <td>
                                                        <?php echo $row_rspros['prog2']?>
                                                    </td>                                                                                                                                       
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div> <br>

                                    <strong>O&apos;LEVEL</strong>
                                    <div class="well">
                                        <table class="table table-hover table-striped table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <table width="320" class="table table-hover table-striped table-bordered">
                                                            <tbody><tr><th colspan="2">First Sitting</th></tr>
                                                                <?php
                                                                if ($totalRows_rssit1 > 0) {
                                                                    for ($i = 0; $i < $totalRows_rssit1; $i++) {
                                                                        ?>
                                                                        <tr>
                                                                            <td><?php echo $row_rssit1['subjname'] ?></td>
                                                                            <td><?php echo $row_rssit1['grdname'] ?></td>
                                                                        </tr>
                                                                        <?php
                                                                        $row_rssit1 = mysql_fetch_assoc($rssit1);
                                                                    }
                                                                } else {
                                                                    ?>
                                                                    <tr><td colspan='2'>No result</td></tr>
<?php } ?>
                                                            </tbody></table>                    
                                                    </td>
                                                    <td>
                                                        <table width="320" class="table table-hover table-striped table-bordered">
                                                            <tbody><tr><th colspan="2">Second Sitting</th></tr>
                                                                <?php
                                                                if ($totalRows_rssit2 > 0) {
                                                                    for ($i = 0; $i < $totalRows_rssit2; $i++) {
                                                                        ?>
                                                                        <tr>
                                                                            <td><?php echo $row_rssit2['subjname'] ?></td>
                                                                            <td><?php echo $row_rssit2['grdname'] ?></td>
                                                                        </tr>
                                                                        <?php
                                                                        $row_rssit2 = mysql_fetch_assoc($rssit2);
                                                                    }
                                                                } else {
                                                                    ?>
                                                                    <tr><td colspan='2'>No result</td></tr>
<?php } ?>
                                                            </tbody></table>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="form-actions">
                                        <a target="_blank" href="printform.php">
                                            <button>Print Form</button>
                                        </a>
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