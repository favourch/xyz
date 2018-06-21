<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}



require_once('../../../path.php');
$auth_users = "1,20,26,24";
check_auth($auth_users, $site_root.'/admin');

//die(var_dump($_POST));
function getUtype($param){
    $type = "";
    
    if($param == 'stud'){
        
        $type = 'RETURNING STUDENT ';
      
    }else{
       
        $type = 'PROSPECTIVE STUDENT ';
    }
    
    return $type;
}

    $i = 1;
    $utype = '';
   if(isset($_POST['MM_Search']) && ($_POST['MM_Search']== 'form2')){
        if(isset($_POST['utype']) && $_POST['utype'] != NULL ){
            $utype = sprintf(" AND olv.usertype = %s", GetSQLValueString($_POST['utype'], "text"));
        }
        
        
        switch ($_POST['utype']) 
        {
            case 'stud':
                $usertype = 'RETURNING STUDENT ';
                $query = sprintf("SELECT  olv.stdid , "
                                        . "olv.level, "
                                        . "c.colname, "
                                        . "olv.exam_type,"
                                        . "olv.exam_no, "
                                        . "p.progname, "
                                        . "olv.exam_year, "
                                        . "ps.fname, "
                                        . "ps.lname "
                                . "FROM   olevel_veri_data olv, "
                                        . "student ps, "
                                        . "programme p, "
                                        . "department d, "
                                        . "college c "
                                . "WHERE olv.stdid = ps.stdid "
                                . "AND olv.progid = p.progid "
                                . "AND p.deptid = d.deptid "
                                . "AND d.colid = c.colid "
                                . "AND c.colid = %s "
                                . "AND olv.treated = 'Yes' "
                                . "%s "
                                . "AND olv.approve = 'Yes' "
                                . "AND olv.date_treated >= %s "
                                . "AND olv.date_treated <= %s "
                                . "ORDER BY p.progname ASC ", 
                                GetSQLValueString($_POST['colid'], "text"), 
                                $utype, 
                                GetSQLValueString($_POST['from'], "text"),
                                GetSQLValueString($_POST['to'], "text"));
                break;
            case 'pros':
                $usertype = 'PROSPECTIVE STUDENT ';
                $query = sprintf("SELECT * "
                                . "FROM  verification v, "
                                        . "prospective ps, "
                                        . "programme p, "
                                        . "department d, "
                                        . "college c "
                                . "WHERE v.stdid = ps.jambregid "
                                . "AND ps.progoffered = p.progid "
                                . "AND p.deptid = d.deptid "
                                . "AND d.colid = c.colid "
                                . "AND c.colid = %s "
                                . "AND v.type = 'pros' "
                                . "AND v.status = 'release' "
                                . "AND v.date_treated >= %s "
                                . "AND v.date_treated <= %s "
                                . "ORDER BY v.stdid, p.progname ASC ", 
                                GetSQLValueString($_POST['colid'], "text"),
                                GetSQLValueString($_POST['from'], "text"),
                                GetSQLValueString($_POST['to'], "text"));
                    break;

            default:
                
                break;
        }
       
       
        $treated = mysql_query($query, $tams) or die(mysql_error());
        $row_treated = mysql_fetch_assoc($treated);
        $totalRows_treated = mysql_num_rows($treated);
        
   }



include("../../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,85,15,10,5); 
$stylesheet = file_get_contents('../../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
                <tr>
                    <td width="90%" align="center"><img src="../../../img/logo/school-logo.png" width="90px" height="90px" /></td>
                </tr>
                <tr>
                    <td width="90%" align="center">
                        <div style="font-weight: bold;">
                            <h2 style="font-size: 25pt">'.$university.'</h2>
                            <h5 style="font-size: 9pt">'.$university_address.'</h5><br />
                        </div>
                    </td>
                </tr>
            </table>
<div style="text-align:center; font-size: 12pt;">
<b>'.$usertype.' O`LEVEL VERIFICATION CODE RELEASE REPORT FOR '.$row_treated['colname'].'</b><br/>
<div style="text-align:center; font-size: 8pt;"> From :'.$_POST['from'].' To: '.$_POST['to'].'</div>
</div>';


$mpdf->SetHTMLHeader($header);
//if($totalRows_history > 0) {
    $html ='<table class="table table-bordered table-condensed table-striped table-hover">
                      <thead>
                          <tr>
                                <th>S/N</th>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>Programme</th>
                                <th>Level</th>
                                <th>Verification Code</th>
                          </tr>
                      </thead>
                      <tbody >';
    if($totalRows_treated > 0){
        do{ 

                $html .=    '<tr>
                                <td>'.$i++.'</td>
                                <td>'.$row_treated['stdid'].'</td>
                                <td>'.$row_treated['fname'].' '.$row_treated['lname'].'</td>
                                <td>'.$row_treated['progname'].'</td>
                                <td>'.$row_treated['level'].'</td>    
                                <td>'.$row_treated['ver_code'].'</td>   
                            </tr>';
        }while($row_treated = mysql_fetch_assoc($treated));  
    }else{
         $html .=    '<tr>
                        <td style="color : red" colspan="8"> No Record found </td>   
                     </tr>';
    }
        $html .='</tbody>
                    </table>';


$mpdf->WriteHTML($html);
$mpdf->Output('olevel verification From '.$_POST['from'].' To '.$_POST['to'].'.pdf', 'I');

exit;
?>