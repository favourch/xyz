<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "20,33";
check_auth($auth_users, $site_root.'/admin');

$not_found_text = '';
$colname_rsstdnt = "";

if (isset($_GET['search']) && $_GET['search'] != NULL) {
    $seed = $colname_rsstdnt = $_GET['search'];

    $query_rsstdnt = "SELECT s.stdid, s.lname, s.fname "
            . "FROM student s JOIN passlist p ON s.stdid = p.stdid "
            . "WHERE s.lname LIKE '%" . $seed . "%' "
            . "OR s.fname LIKE '%" . $seed . "%' "
            . "OR s.stdid LIKE '%" . $seed . "%'";

    $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());

    $not_found_text = " for the search word \"{$seed}\"";
    $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
    $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
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
                        <div class="box box-bordered box-color">
                            <div class="box-title">
                                <h3><i class="icon-reorder"></i>
                                    Search on passlist 
                                </h3>
                            </div>
                            <div class="box-content ">  
                                <div class="row-fluid">
                                    <div class="span12">
                                        <div class='alert alert-info'>
                                            <p>Note: The Search student below will only return list of student that have made the passlist.</p>
                                        </div>
                                    </div>
                                </div>
                                <form id="form1" name="form1" method="get" action="">
                                    <input name="search" type="text" id="search" 
                                           class="input-xxlarge" value="<?php echo $colname_rsstdnt ?>" 
                                           placeholder="Search By First Name, Last Name, or Matric No." 
                                           data-rule-requuired="true"/>
                                    <input  style="margin-bottom: 10px" type="submit" id="submit" value="Search" 
                                            class="btn btn-primary"/>                                        
                                </form>
                                <table width="626" align="center" 
                                       class="table table-bordered table-condensed table-hover table-striped">
                                    <tr align="center">
                                        <th width="5%">S/n</th>
                                        <th width="10%">Matric No.</th>
                                        <th width="50%">Full Name</th>
                                        <th width="35%">Actions</th>
                                    </tr>
                                    <?php
                                    if (!empty($row_rsstdnt)) :
                                        $i = 1;
                                        do {
                                            ?>
                                            <tr align="center" >
                                                <td><?php echo $i++; ?></td>	
                                                <td><?php echo $row_rsstdnt['stdid'] ?></td>
                                                <td><?php echo $row_rsstdnt['fname'] . " " . $row_rsstdnt['lname'] ?></td>
                                                <td>
                                                    <?php if(in_array(getAccess(), [20,33])) :?>
                                                    <a  class="btn btn-small btn-brown" target="_blank" href="transprint.php?stdid=<?php echo $row_rsstdnt['stdid']; ?>">Print Result Profile</a>
                                                    <?php endif;?>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <?php if(in_array(getAccess(), [20,33])) :?>
                                                    <a class="btn btn-small btn-blue" target="_blank" href="gentranscript.php?stdid=<?php echo $row_rsstdnt['stdid']; ?>">Generate Transcript</a>
                                                    <?php endif;?>
                                                    
                                                </td>
                                            </tr>
                                            <?php
                                        } while ($row_rsstdnt = mysql_fetch_assoc($rsstdnt));
                                    else :
                                        ?>

                                        <tr >
                                            <td class="text-error text-center" colspan="5">
                                                No record available<?php echo $not_found_text ?>!
                                            </td>
                                        </tr>

                                    <?php endif ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
        </div>
    </body>
</html>