<?php 

if (!isset($_SESSION)) {
  session_start();
}

require_once('../../path.php');

$query = sprintf("SELECT * FROM prospective WHERE jambregid=%s", GetSQLValueString(getSessionValue('uid'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

$paid= mysql_query($query_paid, $tams);

$paydesc = "POST UTME/DE APPLICATION FEE";
	
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
                                    <h3><i class="icon-reorder"></i>
                                        Payment Notification
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <table class="table table-bordered table-condensed table-striped">
                                            <tr>
                                                <th width="170">Full Name :</th>
                                                <td><?php echo $row_result['lname'] . ' ' . $row_result['fname'] . ' ' . $row_result['mname'] ?></td>
                                            </tr>
                                            <tr>
                                                <th width="170">UTME Reg. No. :</th>
                                                <td><?php echo $row_result['jambregid'] ?></td>
                                            </tr>
                                            <tr>
                                                <th colspan="2">
                                            <p style="color: red; alignment-adjust: central">Your payment transaction has been canceled</p>
                                            </th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>