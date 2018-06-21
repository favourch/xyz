<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "2,3";
check_auth($auth_users, $site_root);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,2";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_dept = "-1";
if ( getSessionValue('cid') != NULL ) {
  $colname_dept = getSessionValue('cid');
}

$query_dept = sprintf("SELECT deptid, deptname, coltitle "
                        . "FROM department d, college c "
                        . "WHERE d.colid = c.colid "
                        . "AND d.colid = %s",
                        GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);


$colname_prog = "-1";
if (isset($row_dept['deptid'])) 
  $colname_prog = $row_dept['deptid'];
	
if (isset($_GET['did']))
  $colname_prog = $_GET['did'];

$query_prog = sprintf("SELECT progid, progname, p.deptid, deptname "
                        . "FROM programme p, department d "
                        . "WHERE d.deptid = p.deptid "
                        . "AND p.deptid = %s", GetSQLValueString($colname_prog, "int"));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$colname_deptcrs = "-1";
if (isset($row_prog['deptid'])) {
  $colname_deptcrs = $row_prog['deptid'];
}
if (isset($_GET['did'])) {
  $colname_deptcrs = $_GET['did'];
}

$colname1_deptcrs = "-1";
if (isset($row_sess['sesid'])) {
  $colname1_deptcrs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname1_deptcrs = $_GET['sid'];
}

if(getAccess() == 3) {
    $colname_prog = getSessionValue('did');
}

$query_deptcrs = sprintf("SELECT c.csid, csname, upload, accepted, approve "
                        . "FROM course c, department_course dc, teaching t "
                        . "WHERE c.csid = dc.csid "
                        . "AND c.csid = t.csid "
                        . "AND dc.csid = t.csid "
                        . "AND dc.deptid = t.deptid "
                        . "AND dc.deptid = %s "
                        . "AND t.sesid = %s "
                        . "ORDER BY csid ASC", 
                        GetSQLValueString($colname_prog, "int"), 
                        GetSQLValueString($colname1_deptcrs, "int"));
$deptcrs = mysql_query($query_deptcrs, $tams) or die(mysql_error());
$row_deptcrs = mysql_fetch_assoc($deptcrs);
$totalRows_deptcrs = mysql_num_rows($deptcrs);

$name = "";
if(getAccess() == 3) {
    $name = $row_prog['deptname'];
}elseif(getAccess() == 2) {
    $name = $row_dept['coltitle'];
}

if(isset($_GET['did']) ) {
    $name = $row_prog['deptname'];
}elseif(isset($_GET['cid']) ) {
    $name = $row_dept['coltitle'];
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
                    
                    <div class="row-fluid">
                        <?php if(isset($uploadstat)) :?>
                        <div class="span12 alert alert-<?php echo $type?>">
                            <?php echo $uploadstat?>
                        </div>
                        <?php endif;?>
                    </div>
                    
                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Consider Result for <?php echo $name;?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <?php if (getAccess() == 2) { ?>
                                        <div class="span4">
                                            Choose Department
                                            <select name="deptid" id="deptid" onchange="deptfilt(this)">
                                            <?php do {?>
                                                <option value="<?php echo $row_dept['deptid'] ?>" 
                                                    <?php if (!(strcmp($row_dept['deptid'], $colname_prog))) {
                                                        echo "selected=\"selected\"";
                                                    }?>>
                                                    <?php echo $row_dept['deptname'] ?>
                                                </option>
                                            
                                            <?php } while ($row_dept = mysql_fetch_assoc($dept));?>
                                            </select>
                                        </div>
                                        <?php } ?>
                                        
                                        <div class="span4">
                                            <?php if (getAccess() == 2 || getAccess() == 3) { ?>
                                                Session
                                                <select name="sesid" id="sesid" onchange="sesfilt(this)">
                                                    <?php do {?>
                                                    <option value="<?php echo $row_sess['sesid'] ?>"<?php
                                                    if (!(strcmp($row_sess['sesid'], $colname1_deptcrs))) {
                                                        echo "selected=\"selected\"";
                                                    }?>>
                                                        <?php echo $row_sess['sesname'] ?>
                                                    </option>
                                                    <?php }while ($row_sess = mysql_fetch_assoc($sess));?>
                                                </select>
                                            </td>
                                            <?php } ?>
                                        </div>
                                    </div>    
                                    

                                    <table class="table table-striped">
                                        <?php   if ($totalRows_deptcrs > 0) { // Show if recordset not empty  
                                                    for(;$row_deptcrs;$row_deptcrs = mysql_fetch_assoc($deptcrs)) { ?>
                                        <tr>
                                            <td width="60"><?php echo $row_deptcrs['csid'] ?></td>
                                            <td width="385">
                                                <a href="result.php?csid=<?php echo $row_deptcrs['csid'] ?>&did=<?php echo $colname_deptcrs ?>&sid=<?php echo $colname1_deptcrs ?>">
                                                    <?php echo ucwords(strtolower($row_deptcrs['csname']))?>
                                                </a>
                                            </td>
                                            <td width="106"><?php echo getUploadState($row_deptcrs['upload']) ?></td>
                                            <td width="116">
                                                <?php                                                         
                                                    echo $row_deptcrs['accepted'] == 'no'? 'Not Accepted': 'Accepted';
                                                ?>
                                            </td>
                                            <td width="106"><?php echo getApproveState($row_deptcrs['approve']) ?></td>
                                        </tr>
                                        <?php }                                            
                                            }else { // Show if recordset not empty 
                                        ?> 
                                        <tr>
                                            <td>There are no results to consider!</td>
                                        </tr>
                                        <?php }?>
                                    </table>
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