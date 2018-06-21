<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}



require_once('../../path.php');

$auth_users = "1,20,21,22,23,24";
check_auth($auth_users, $site_root.'/admin');

$prog = "";
$level ='';
$ses = '';
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT sesname "
                . "FROM session "
                . "WHERE sesid= %s", 
                GetSQLValueString($_POST['sesid'], "text"));
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT progname "
                . "FROM programme "
                . "WHERE progid = %s", 
                GetSQLValueString($_POST['progid'], "text"));
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);

$title = "";

if(isset($_POST['MM_Search']) && $_POST['MM_Search'] == 'form1'){
    
//    if(isset($_POST['level']) && $_POST['level'] != NULL ){
//        $level = sprintf(" AND olv.level = %s", GetSQLValueString($_POST['level'], "text"));
//    }
    
    if(isset($_POST['progid']) && $_POST['progid']!= NULL){
        $prog = sprintf(" AND pr.progid = %s",GetSQLValueString($_POST['progid'], "text"));
    }
    
    if(isset($_POST['sesid']) && $_POST['sesid']!= NULL){
        $ses = sprintf(" AND v.sesid = %s",GetSQLValueString($_POST['sesid'], "text"));
    }
    
    switch ($_POST['utype']) {
        case 'pros':
            $title = "O'Level Verification Code Release Report for {$row_session['sesname']} Prospective Student ";
            
            $query = sprintf("SELECT * "
                            . "FROM verification v, prospective p, programme pr "
                            . "WHERE v.stdid = p.jambregid "
                            . "AND p.progoffered = pr.progid "
                            . "AND v.release_code = 'yes' "
                            . "%s "
                            . "%s "
                            . "%s ORDER BY p.jambregid, pr.progid ASC ", $level, $prog, $ses);

            $treated = mysql_query($query, $tams) or die(mysql_error());
            $row_treated = mysql_fetch_assoc($treated);
            $totalRows_treated = mysql_num_rows($treated);
            break;
        
        case 'stud':
            $title = "O'Level Verification Report for {$row_session['sesname']} Returning  Student in {$level} Level ";
            
            $query = sprintf("SELECT olv.stdid, olv.exam_year, olv.exam_type, olv.exam_no, s.fname, s.lname, pr.progname "
                            . "FROM olevel_veri_data olv, student s, programme pr "
                            . "WHERE olv.stdid = s.stdid "
                            . "AND olv.progid = pr.progid "
                            . "%s "
                            . "%s "
                            . "%s ORDER BY olv.progid ASC ", $level, $prog, $ses);

            $treated = mysql_query($query, $tams) or die(mysql_error());
            $row_treated = mysql_fetch_assoc($treated);
            $totalRows_treated = mysql_num_rows($treated);

            break;

        default:
            break;
    }
}
    $i = 1;
   
    
   




include("../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,65,15,10,5); 
$stylesheet = file_get_contents('../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
                <tr>
                    <td width="90%" align="center"><img src="../../img/logo/school-logo.png" width="90px" height="90px" /></td>
                </tr>
                <tr>
                    <td width="90%" align="center">
                        <div style="font-weight: bold;">
                            <h2 style="font-size: 25pt">'.$university.'</h2>
                            <h5 style="font-size: 9pt">'.$university_address.'</h5><br />
                        </div>
                    </td>
                </tr>
            </table>';
$mpdf->SetHTMLHeader($header);

//$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. '.$order_no.'!</p>';

//if($totalRows_history > 0) {
    $html = '<p style="text-align:center; font-size: 12pt; margin-bottom: 5px"><b>'.$title.'</b></p>';
    $html .='<div style="text-align:center; width:120%; font-size: 20pt">
        <table class="table table-bordered table-condensed table-striped table-hover">
                      <thead>
                          <tr>
                                <th>S/N</th>
                                <th>Reg No</th>
                                <th>Name</th> 
                                <th>Programme</th> 
                                <th>Verification Code</th>
                          </tr>
                      </thead>
                      <tbody >';
    do{
            $html .=    '<tr>  
                            <td>'.$i++.'</td>
                            <td>'.$row_treated['stdid'].'</td>
                            <td>'.$row_treated['fname'].' '.$row_treated['lname'].'</td> 
                            <td>'.$row_treated['progname'].'</td>    
                            <td>'.$row_treated['ver_code'].'</td>  
                        </tr>';
    }while($row_treated = mysql_fetch_assoc($treated)); 
    
                     $html .='</tbody>
                    </table>

    </div>';
////}

$mpdf->WriteHTML($html);
$mpdf->Output('Olevel verification report.pdf', 'I');

exit;
?>