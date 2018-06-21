<?php
require_once('../../../path.php');

if (!isset($_SESSION)) {
    session_start();
}




$auth_users = "1,2,20,23";
check_auth($auth_users, $site_root.'/admin');

$desc = "";
$totalRows_stud = 0;
$filter = "";

if( isset($_POST['MM_Search']) && ($_POST['MM_Search'] == 'form1') ){
  $formValue = $_POST;
  
    if($formValue['usertype'] == 'stud'){
        
        if (isset($formValue['prog']) && ($formValue['prog'] != 'all')) {
            $filter .= sprintf("AND s.progid = %s ", GetSQLValueString($formValue['prog'], 'text'));
        }
        
        if (isset($formValue['lvl']) && $formValue['lvl'] != '') {
            $filter .= sprintf("AND st.level = %s ", GetSQLValueString($formValue['lvl'], 'text'));
        }
        
        if(isset($formValue['from']) && $formValue['to'] != ''){
            $filter .= sprintf("AND st.trans_date BETWEEN %s AND %s ",
                    GetSQLValueString($formValue['from'], 'text'), 
                    GetSQLValueString($formValue['to'], 'text')); 
        }

        switch ($formValue['payhead']) {
            
            case 'sch':
                $query_stud = sprintf("SELECT st.matric_no, st.can_name, st.ordid, p.progname, ss.sesname, "
                                    . "st.auth_code, st.amt, st.level, st.percentPaid, st.date_time "
                                    . "FROM schfee_transactions st "
                                    . "JOIN student s ON st.matric_no = s.stdid  "
                                    . "JOIN session ss ON st.sesid = ss.sesid  "
                                    . "JOIN programme p ON s.progid = p.progid "
                                    . "WHERE st.sesid = %s "
                                    . "AND st.status = 'APPROVED'  %s  "
                                    . "ORDER BY st.ordid ASC ", 
                                    GetSQLValueString($formValue['ses'], 'text'),
                                    GetSQLValueString($filter, "defined", $filter) );
                $stud = mysql_query($query_stud, $tams) or die(mysql_error());
                $row_stud = mysql_fetch_assoc($stud);
                $totalRows_stud = mysql_num_rows($stud);
                $desc = "{$row_stud['sesname']} School Fee Payment List ";
                
                if(isset($formValue['prog']) && ($formValue['prog'] != 'all')){
                   $desc .= "({$row_stud['progname']})";
                }
                if (isset($formValue['lvl']) && $formValue['lvl'] != '') {
                   $desc .=" ({$row_stud['level']}00Level)" ;
                }
                
                 if (isset($formValue['from']) && $formValue['to'] != '') {
                     
                    $desc .=" FROM - {$formValue['from']}  TO - {$formValue['to']}" ;
                }
                break;
                
                case 'clr':
                
                $query_stud = sprintf("SELECT st.matric_no, st.can_name, st.ordid, p.progname, ss.sesname, "
                                    . "st.auth_code, st.amt, st.level, st.percentPaid, st.date_time "
                                    . "FROM clearance_transactions st "
                                    . "JOIN student s ON st.matric_no = s.stdid  "
                                    . "JOIN session ss ON st.sesid = ss.sesid  "
                                    . "JOIN programme p ON s.progid = p.progid "
                                    . "WHERE st.sesid = %s "
                                    . "AND st.status = 'APPROVED'  %s  "
                                    . "ORDER BY st.ordid ASC ", 
                                    GetSQLValueString($formValue['ses'], 'text'),
                                    GetSQLValueString($filter, "defined", $filter) );
                $stud = mysql_query($query_stud, $tams) or die(mysql_error());
                $row_stud = mysql_fetch_assoc($stud);
                $totalRows_stud = mysql_num_rows($stud);
                
                $desc = "{$row_stud['sesname']} Clearance Fee Payment List ";
                
                if(isset($formValue['prog']) && ($formValue['prog'] != 'all')){
                   $desc .= "({$row_stud['progname']})";
                }
                if (isset($formValue['lvl']) && $formValue['lvl'] != '') {
                   $desc .=" ({$row_stud['level']}00Level)" ;
                }
                
                 if (isset($formValue['from']) && $formValue['to'] != '') {
                     
                    $desc .=" FROM - {$formValue['from']}  TO - {$formValue['to']}" ;
                }
                
                break;

                
                case 'rep':
                
                $query_stud = sprintf("SELECT st.matric_no, st.can_name, st.ordid, p.progname, ss.sesname, "
                                    . "st.auth_code, st.amt, st.level, st.percentPaid, st.date_time "
                                    . "FROM reparation_transactions st "
                                    . "JOIN student s ON st.matric_no = s.stdid  "
                                    . "JOIN session ss ON st.sesid = ss.sesid  "
                                    . "JOIN programme p ON s.progid = p.progid "
                                    . "WHERE st.sesid = %s "
                                    . "AND st.status = 'APPROVED'  %s  "
                                    . "ORDER BY st.ordid ASC ", 
                                    GetSQLValueString($formValue['ses'], 'text'),
                                    GetSQLValueString($filter, "defined", $filter) );
                $stud = mysql_query($query_stud, $tams) or die(mysql_error());
                $row_stud = mysql_fetch_assoc($stud);
                $totalRows_stud = mysql_num_rows($stud);
                
                $desc = "{$row_stud['sesname']} Reparation Fee Payment List ";
                
                if(isset($formValue['prog']) && ($formValue['prog'] != 'all')){
                   $desc .= "({$row_stud['progname']})";
                }
                if (isset($formValue['lvl']) && $formValue['lvl'] != '') {
                   $desc .=" ({$row_stud['level']}00Level)" ;
                }
                
                 if (isset($formValue['from']) && $formValue['to'] != '') {
                     
                    $desc .=" FROM - {$formValue['from']}  TO - {$formValue['to']}" ;
                }
                
                break;
                
            default:
                break;
        }
        
    }else{
        
        if(isset($formValue['prog']) && ($formValue['prog'] != 'all')) {
            $filter .= sprintf("AND s.progoffered = %s ", GetSQLValueString($formValue['prog'], 'text'));
        }
        switch ($formValue['payhead']) {

            case 'acc':
                $query_stud = sprintf("SELECT st.can_no AS matric_no, st.can_name, ss.sesname, st.ordid, p.progname, "
                                    . "st.auth_code, st.amt, st.percentPaid, st.date_time "
                                    . "FROM accfee_transactions st "
                                    . "JOIN prospective s ON st.can_no = s.jambregid  "
                                    . "JOIN session ss ON st.sesid = ss.sesid  "
                                    . "JOIN programme p ON s.progoffered = p.progid "
                                    . "WHERE st.sesid = %s "
                                    . "AND st.status = 'APPROVED'  %s  "
                                    . "ORDER BY st.ordid ASC ", GetSQLValueString($formValue['ses'], 'text'), 
                                    GetSQLValueString($filter, "defined", $filter));
                $stud = mysql_query($query_stud, $tams) or die(mysql_error());
                $row_stud = mysql_fetch_assoc($stud);
                $totalRows_stud = mysql_num_rows($stud);
                
                $desc = "{$row_stud['sesname']} Acceptance Fee Payment List ";
                
                if(isset($formValue['prog']) && ($formValue['prog'] != 'all')){
                   $desc .= "({$row_stud['progname']})";
                }
                if (isset($formValue['lvl']) && $formValue['lvl'] != '') {
                   $desc .=" ({$row_stud['level']}00Level)" ;
                }
                
                 if (isset($formValue['from']) && $formValue['to'] != '') {
                     
                    $desc .=" FROM - {$formValue['from']}  TO - {$formValue['to']}" ;
                }
                break;
                
            case 'app':

                $query_stud = sprintf("SELECT st.can_no AS matric_no, st.can_name, ss.sesname, st.ordid, p.progname, "
                        . "st.auth_code, st.amt, st.percentPaid, st.date_time "
                        . "FROM appfee_transactions st "
                        . "JOIN prospective s ON st.can_no = s.jambregid  "
                        . "JOIN session ss ON st.sesid = ss.sesid  "
                        . "JOIN programme p ON s.progoffered = p.progid "
                        . "WHERE st.sesid = %s "
                        . "AND st.status = 'APPROVED'  %s  "
                        . "ORDER BY st.ordid ASC ", GetSQLValueString($formValue['ses'], 'text'), GetSQLValueString($filter, "defined", $filter));
                $stud = mysql_query($query_stud, $tams) or die(mysql_error());
                $row_stud = mysql_fetch_assoc($stud);
                $totalRows_stud = mysql_num_rows($stud);

                $desc = "{$row_stud['sesname']} Application Fee Payment List ";

                if (isset($formValue['prog']) && ($formValue['prog'] != 'all')) {
                    $desc .= "({$row_stud['progname']})";
                }
                if (isset($formValue['lvl']) && $formValue['lvl'] != '') {
                    $desc .=" ({$row_stud['level']}00Level)";
                }

                if (isset($formValue['from']) && $formValue['to'] != '') {

                    $desc .=" FROM - {$formValue['from']}  TO - {$formValue['to']}";
                }

                break;

            default:
                break;
        }
    }
    
}



$name = 'Paid students';

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
    doLogout($site_root);
}

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
                                    <h3><i class="icon-money"></i>
                                        <?= $desc; ?>
                                    </h3>
                                    <ul class="tabs">
                                        <li class="active">
                                            <a data-toggle="tab" href="#t7">Total Paid : <?= (isset($totalRows_stud) && $totalRows_stud > 0)? $totalRows_stud : 0?></a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="box-content"> 
                                    <?php if($totalRows_stud > 0){?>
                                    <table width="670" class="table table-striped table-condensed">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Student ID</th>
                                                <th>Order ID</th>
                                                <th>Name</th>
                                                <th>Amount</th>
                                                <th>Percentage</th>
                                                <th>Date Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $total = 0; $i=1; do{ ?>
                                            <?php $total +=  str_replace(',', '', substr($row_stud['amt'], 3))?>
                                            <tr>
                                                <td><?= $i++?></td>
                                                <td><?= $row_stud['matric_no']?></td>
                                                <td><?= $row_stud['ordid']?></td>
                                                <td><?= $row_stud['can_name']?></td>
                                                <td><?= $row_stud['amt']?></td>
                                                <td><?= $row_stud['percentPaid']?></td>
                                                <td><?= $row_stud['date_time']?></td>
                                            </tr>
                                            <?php }while($row_stud = mysql_fetch_assoc($stud));?>
                                            <tr style="color: blue">
                                                <th colspan="4">Total </th>
                                                <th colspan="3"><?= 'NGN'.number_format($total, 2)?></th>
                                            </tr>
                                            <tr style="color: brown">
                                                <th colspan="2">Total Amount In Word </th>
                                                <th colspan="5"><?= ucwords(convert_number_to_words($total) ." Naira Only")?></th>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <?php }else{?>
                                    <div class="alert alert-error">
                                        No Record Found
                                    </div>
                                    <?php }?>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
        <?php include INCPATH."/footer.php" ?>
    </body>
</html>