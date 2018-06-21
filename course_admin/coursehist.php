<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/coledit.php Page 
 *
 * *------------------------------------------------
 */




$colname_lect = "-1";
if (isset($_GET['lid'])) {
  $colname_lect = $_GET['lid'];
}
mysql_select_db($database_tams, $tams);
$query_lect = sprintf("SELECT title, fname, lname FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_lect, "text"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);

$colname_hist = "-1";
if (isset($_GET['lid'])) {
  $colname_hist = $_GET['lid'];
}
mysql_select_db($database_tams, $tams);
$query_hist = sprintf("SELECT lectid1, lectid2, t.csid, c.csname, sesid FROM teaching t, course c WHERE c.csid = t.csid AND (lectid1 = %s OR lectid2 = %s)", 
					GetSQLValueString($colname_hist, "text"), 
					GetSQLValueString($colname_hist, "text"));
$hist = mysql_query($query_hist, $tams) or die(mysql_error());
$row_hist = mysql_fetch_assoc($hist);
$totalRows_hist = mysql_num_rows($hist);


mysql_select_db($database_tams, $tams);
$query_sess = sprintf("SELECT DISTINCT t.sesid, s.sesname FROM teaching t, session s WHERE s.sesid = t.sesid AND (lectid1 = %s OR lectid2 = %s) ORDER BY sesname DESC", 
					GetSQLValueString($colname_hist, "text"), 
					GetSQLValueString($colname_hist, "text"));
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$name = ( isset($_GET['lid']) )? $row_lect['title']." ".$row_lect['lname'].", ".$row_lect['fname']:"";

$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

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
                                                    <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                                                </li>
                                                <li>
                                                    <a href="college.php">College</a>
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
                                        Teaching History for <?php echo $name?>
                                    </h3>
                                </div>
                                <div class="box-content nopadding">
                                    <table class="table">
                                        <?php if ($totalRows_sess > 0) { // Show if recordset not empty ?>
                                            <tr>
                                                <td width="92">&nbsp;</td>
                                                <td align="center">&nbsp;</td>
                                            </tr>

                                            <?php do { ?>
                                                <tr style="border-bottom:1px solid thick">
                                                    <td width="92" valign="top"><?php echo $row_sess['sesname'] ?></td>
                                                    <td>

                                                        <?php
                                                        do {
                                                            if ($row_sess['sesid'] == $row_hist['sesid']) {
                                                                ?>
                                                                <div style="border-bottom:2px solid thick #FC0; width:100%">            
                                                                    <span style="width:130px; float:right">
                                                                        <?php
                                                                        if ($colname_hist == $row_hist['lectid1'])
                                                                            echo "Convener";
                                                                        else
                                                                            echo "Assistant";
                                                                        ?>

                                                                    </span>
                                                                    <span style="width:370px; float:right"><a href="../course/course.php?csid=<?php echo $row_hist['csid'] ?>"><?php echo ucwords(strtolower($row_hist['csname'])) ?></a></span>            
                                                                    <span style="width:70px; float:right"><?php echo $row_hist['csid'] ?></span>
                                                                    <div style="clear:both"></div>
                                                                </div>
                                                            <?php }
                                                        }while ($row_hist = mysql_fetch_assoc($hist)); ?>
                                                    </td>
                                                </tr> 

                                                <tr style="border-bottom:1px solid thick">
                                                    <td width="92" valign="top"></td>
                                                    <td>  
                                                        <hr />      
                                                    </td>
                                                </tr> 
                                                <?php
                                                $rows = mysql_num_rows($hist);
                                                if ($rows > 0) {
                                                    mysql_data_seek($hist, 0);
                                                    $row_hist = mysql_fetch_assoc($hist);
                                                }
                                            }
                                            while ($row_sess = mysql_fetch_assoc($sess));
                                            ?>
                                        <?php }
                                        else { ?>
                                            No history available
                                    <?php } ?>
                                    </table>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
    
</html>
<?php
mysql_free_result($lect);

mysql_free_result($hist);
?>
