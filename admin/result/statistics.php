<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,4,20,21,22";
check_auth($auth_users, $site_root);

$insert_row = 0;
$insert_error = array();
$uploadstat = "";


$filter = "";
$colname_rslt = "-1";
if (isset($_GET['csid'])) {
  $colname_rslt = $_GET['csid'];
}


if (isset($_GET['crs'])) {
  $colname_rslt = $_GET['crs'];
}

$colname1_rslt = getSessionValue('sesid');
if (isset($sesid)) {
  $colname1_rslt = $sesid;
}

if (isset($_GET['sid'])) {
  $colname1_rslt = $_GET['sid'];
}

if (isset($_GET['ssid'])) {
  $colname1_rslt = $_GET['ssid'];
}

$colname2_rslt = "-1";
if (isset($_GET['did'])) {
	$colname2_rslt = $_GET['did'];
	$filter = "AND p.deptid =".$colname2_rslt;
}
 $query_status = sprintf("SELECT d.colid, t.approve, t.upload, t.accepted, t.released, p.progname, c.type "
                        . "FROM course c, teaching t, programme p, department d "
                        . "WHERE d.deptid = p.deptid AND c.csid = t.csid AND t.deptid = p.deptid "
                        . "AND t.deptid = %s AND sesid = %s AND t.csid = %s", 
                        GetSQLValueString($colname2_rslt, "int"), 
                        GetSQLValueString($colname1_rslt, "int"), 
                        GetSQLValueString($colname_rslt, "text")); 
$status = mysql_query($query_status, $tams) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status); 

$approved = ( strtolower($row_status['approve']) == "yes" ) ? true: false;
$uploaded = ( strtolower($row_status['upload']) == "yes" ) ? true: false;
$accepted = ( strtolower($row_status['accepted']) == "yes" ) ? true: false;
$released = ( strtolower($row_status['released']) == "yes" ) ? true: false;
$name = $row_status['progname'];
$name .= ( isset($_GET['csid']) ) ? " (".$_GET['csid'].")": "";

$query_rslt = sprintf("SELECT r.resultid, r.edited, r.csid, r.stdid, tscore, escore, fname, lname, mname "
                        . "FROM result r, student s, programme p, teaching t "
                        . "WHERE r.stdid = s.stdid "
                        . "AND r.csid = t.csid "
                        . "AND r.sesid = t.sesid "
                        . "AND t.upload = 'yes' "
                        . "AND r.csid = %s "
                        . "AND r.sesid = %s "
                        . "AND s.progid = p.progid %s "
                        . "ORDER BY r.stdid ASC", 
                        GetSQLValueString($colname_rslt, "text"), 
                        GetSQLValueString($colname1_rslt, "int"), 
                        GetSQLValueString($filter, "undefined", $filter));
$rslt = mysql_query($query_rslt, $tams) or die(mysql_error());
$row_rslt = mysql_fetch_assoc($rslt);
$totalRows_rslt = mysql_num_rows($rslt);

//$query_dept = sprintf("SELECT d1.deptid, d1.deptname "
//                        . "FROM department d1 "
//                        . "INNER JOIN department d2 ON d1.colid = d2.colid "
//                        . "WHERE d2.deptid = %s", 
//                        GetSQLValueString($colname2_rslt, "int"));

$query_dept = sprintf("SELECT deptid, deptname "
                        . "FROM department", 
                        GetSQLValueString($colname2_rslt, "int"));                        
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

 $query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = %s AND g.colid = %s",
                        GetSQLValueString($colname1_rslt, "int"),
                        GetSQLValueString($row_status['colid'], "int")); 
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$tot_pass           = 0;
$tot_fail           = 0;
$pcent1             = 0;
$pcent2             = 0;
$heighest_scr       = '-';
$lowest_scr         = '-';
$scores             = [];
$results            = [];
$data               = [];

$a = 0;
$b = 0;
$c = 0;
$d = 0;
$e = 0;
$f = 0;
$g = 0;
$h = 0;
$i = 0;
$j = 0;


for (; $row_rslt; $row_rslt = mysql_fetch_assoc($rslt)) {

    $row_rslt['edit'] = false;
    $tot_scr = $row_rslt['tscore'] + $row_rslt['escore'];
    if ($tot_scr >= $row_grad['passmark']) {
        $tot_pass++;        
    }else {
        $tot_fail++;
    }
    
    
    if ($tot_scr >= 0 && $tot_scr <= 10) {
        
        $a++;
    } elseif ($tot_scr >= 10 && $tot_scr <= 20) {
        
        $b++;
    } elseif ($tot_scr >= 20 && $tot_scr <= 30) {
        
        $c++;
    } elseif ($tot_scr >= 30 && $tot_scr <= 40) {
        
        $d++;
    } elseif ($tot_scr >= 40 && $tot_scr <= 50) {
       
        $e++;
    } elseif ($tot_scr >= 50 && $tot_scr <= 60) {
        
        $f++;
    } elseif ($tot_scr >= 60 && $tot_scr <= 70) {
        
        $g++;
    } elseif ($tot_scr >= 70 && $tot_scr <= 80) {
        
        $h++;
        
    } elseif ($tot_scr >= 80 && $tot_scr <= 90) {
        
        $i++;
        
    } elseif ($tot_scr >= 90 && $tot_scr <= 100) {
        
        $j++;
    }
    
    array_push($scores, $tot_scr);
    array_push($results, $row_rslt);
}

$totalRows_rslt = $totalRows_rslt < 1? 1: $totalRows_rslt;
$pcent1 = $tot_pass * 100 / $totalRows_rslt;
$pcent2 = $tot_fail * 100 / $totalRows_rslt;

$scores = empty($scores)? [0]: $scores;
$heighest_scr = max($scores);
$lowest_scr = min($scores);

$data = array(
            array('score' => '0-10', 'value' => $a),
            array('score' => '10-20', 'value' => $b),
            array('score' => '20-30', 'value' => $c),
            array('score' => '30-40', 'value' => $d),
            array('score' => '40-50', 'value' => $e),
            array('score' => '50-60', 'value' => $f),
            array('score' => '60-70', 'value' => $g),
            array('score' => '70-80', 'value' => $h),
            array('score' => '80-90', 'value' => $i),
            array('score' => '90-100', 'value' => $j),
        );

        

        //var_dump($colname1_rslt); die();

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
                    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
                    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
                    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
                    <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>

                    <div class="row-fluid">                        
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                         Result Statistics for <?php echo $colname_rslt . ' '. $row_grad['sesname']. 'Session'?>
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span6">
                                            <div class="well">
                                                <h3>Bar Chart</h3>
                                                <div id="myfirstchart" style="height: 250px;"></div>
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="well">
                                               <h3>Summary</h3> 
                                               <table class="table table-bordered table-condensed table-striped">
                                                   <thead>
                                                       <tr>
                                                           <th>Score Range</th>
                                                           <th>No. of Students</th>
                                                       </tr>
                                                   </thead>
                                                   <tbody>
                                                       <?php for ($idx = 0; $idx < count($data); $idx++) { ?>
                                                        <tr>
                                                            <th><?= $data[$idx]['score']; ?></th>
                                                            <td><?= $data[$idx]['value']; ?></td>
                                                        </tr>
                                                      <?php  }?>   
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

        </div>
        <script type="text/javascript">
            var dt  = <?= json_encode($data)?>;
            console.log(dt);
            new Morris.Bar({
                // ID of the element in which to draw the chart.
                element: 'myfirstchart',
                // Chart data records -- each entry in this array corresponds to a point on
                // the chart.
                data: dt,
                // The name of the data record attribute that contains x-values.
                xkey: 'score',
                labels: ['Score Range'],
                resize: true,
                axes:true,
                grid:true,
                // A list of names of data record attributes that contain y-values.
                ykeys: ['value'],
                // Labels for the ykeys -- will be displayed when you hover over the
                // chart.
                labels: ['Total Student']
            });
        </script>
    </body>
</html>