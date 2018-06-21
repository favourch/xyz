<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../../../path.php');

$auth_users = "1, 20,21,23";
check_auth($auth_users, $site_root);

$page_title = "Tasued";

//Instantiate number of rows 
$num_row_search = 0;
$row_search = "";
  

//check if user actually post  a search parameters
if(isset($_POST['search']) && $_POST['search'] != ""){
    
    $query = sprintf("SELECT s.stdid, s.jambregid, s.fname, s.lname, s.level, s.mname, p.progname "
            . "FROM student s LEFT JOIN programme p ON p.progid = s.progid "
            . "WHERE stdid LIKE %s "
            . "OR jambregid LIKE %s "
            . "OR fname LIKE %s "
            . "OR lname LIKE %s ", 
            GetSQLValueString(trim($_POST['search']), 'text'), 
            GetSQLValueString(trim($_POST['search']), 'text'),
            GetSQLValueString(trim($_POST['search']), 'text'), 
            GetSQLValueString(trim($_POST['search']), 'text')); 
    $Rs_search = mysql_query($query) or die(mysql_error());
    $row_search = mysql_fetch_assoc($Rs_search);
    $num_row_search = mysql_num_rows($Rs_search);
    
    
}


?>
<!doctype html>
<html>
    <?php include INCPATH . "/header.php" ?>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-color box-bordered ">
                                <div class="box-title">
                                    <h3>
                                        <i class="icon-th"></i> Search Student Record
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="alert alert-info">
                                        Instruction
                                    </div>
                                    <form class="form-vertical" method="post" action="index.php">
                                        <div class="row-fluid">
                                            <div class="span6">
                                                <div class="control-group">
                                                    <label class="control-label" for="textfield">Search Student</label>
                                                    <div class="controls controls-row">
                                                        <div class="input-append input-block-level">
                                                            <input type="text" class="input-block-level" name="search">
                                                            <button type="submit" class="btn">Search</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="row-fluid">
                                        <div class="span12">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>S/n</th>
                                                        <th>Matric No</th>
                                                        <th>Jamb Reg Id</th>
                                                        <th>Full Name</th>
                                                        <th>Programme</th>
                                                        <th>Level</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if( $num_row_search > 0){?>
                                                        <?php  $i = 1; do { ?>
                                                        <tr>
                                                            <td><?= $i++; ?></td>
                                                            <td><?= $row_search['stdid']?></td>
                                                            <td><?= $row_search['jambregid']?></td>
                                                            <td><?= strtoupper($row_search['lname']) . ", ".strtolower( $row_search['fname'] . " " . $row_search['mname']) ?></td>
                                                            <td><?= $row_search['progname']?></td>
                                                            <td><?= $row_search['level']?></td>
                                                            <td><a target="_tab" href="history.php?stdid=<?=$row_search['stdid']?>" class="btn btn-small btn-blue">Pay. History</a></td>
                                                        </tr>
                                                        <?php }while($row_search = mysql_fetch_assoc($Rs_search)) ?>
                                                    
                                                    <?php } else{ ?>
                                                        <tr>
                                                            <td colspan="7" style="text-align: center"><div class="alert alert-warning">No record Found</div></td>
                                                        </tr>
                                                    <?php }?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH . "/footer.php" ?>
    </body>
</html>

