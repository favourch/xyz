<?php 
require_once('../../path.php');
include("../../mpdf/mpdf.php");

$mpdf = new mPDF('c', 'Legal-L', '', '', 10, 10, 10, 10, 5, 5); 
$stylesheet = file_get_contents('../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

// Define the maximum containing box width & height for each text box as it will appear on the final page (no padding or margin here)
$pw = 347;
$ph = 210;
$minK = 0.6;	// Maximum scaling factor 0.7 = 70%
$inc = 0.01;	// Increment to change scaling factor 0.05 = 5%

$auth_users = "10";
//check_auth($auth_users, $site_root);

function gradepoint($unit, $score, $level, $ses, $sesGrad, $tf) {
    $gp = 0;
    $grades = $sesGrad[$ses][$level];

    // Checks for technical failure
    if ($tf)
        return $gp;

    if ($score < $grades['gradeF'])
            $gp = 0;
    else if ($score < $grades['gradeE'])
            $gp = 0;
    else if ($score < $grades['gradeD'])
            $gp = 2;
    else if ($score < $grades['gradeC'])
            $gp = 3;
    else if ($score < $grades['gradeB'])
            $gp = 4;
    else if ($score < $grades['gradeA'])
            $gp = 5;

    return $gp * $unit;
}

function getPassmark($sesid, $status, $level, $sesGrad) {
    if (in_array($status, ['Elective', 'Required'])) {
        return 0;
    }
    
    return $sesGrad[$sesid][$level]['passmark'];
}

function getTranscriptRow($semesterArr, $pos, $padding = []) {
    $emptySes = array_fill(0, 6, null);
    $transcriptRow = [];
    $foundRow = false;
    
    foreach($semesterArr as $result) {
        $sesRow = $emptySes;
        
        if(isset($result[$pos])) {
            $sesRow = $result[$pos];
            $foundRow = true;
        }
        
        $transcriptRow = array_merge($transcriptRow, $sesRow);
    } 
    
    return $foundRow? array_merge($transcriptRow, $padding): false;
}

function calculateCumulativeGPA($semStats) {
    $cumTut = 0;
    $cumTgp = 0;
    $cumTup = 0;
    
    foreach ($semStats as $ses => $value) {
        $cumTut += isset($value['total'][0])? $value['total'][0]: 0;
        $cumTgp += isset($value['total'][1])? $value['total'][1]: 0;
        $cumTup += isset($value['total'][2])? $value['total'][2]: 0;
        
        $semStats[$ses]['total'][] = $cumTut > 0? round($cumTgp / $cumTut, 2): 0;
    }
    
    return $semStats;
}

function getStatsRow($statsArr, $semester, $padding = []) {
    $emptySes = array_fill(0, 6, null);
    $stats = [];
    $foundRow = false;
    
    foreach($statsArr as $result) {
        $statsRow = $emptySes;
        
        if(isset($result[$semester])) {
            $statsRow = $result[$semester];
            $foundRow = true;
        }
        
        $stats = array_merge($stats, $statsRow);
    }    
    
    return $foundRow? array_merge($stats, $padding): false;
}

function getCumulativeRow($statsArr) {
    $emptySes = array_fill(0, 5, null);
    $stats = [];
    $foundRow = false;
    
    foreach($statsArr as $result) {
        $statsRow = $emptySes;
        
        if(isset($result['total'])) {
            $statsRow = $result['total'];
            $foundRow = true;
        }
        
        $stats = array_merge($stats, [$result['level']], $statsRow);
    }    
    
    return $foundRow? $stats: false;
}

function calculateSemesterPadding($semArr, $colCount) {
    $deficit = $colCount - count($semArr);
    return $deficit > 0 ? array_fill(0, $deficit * 6, null): [];
}

function padSessionInfo($sesInfo, $colCount) {
    $deficit = $colCount - count($sesInfo);
    return $deficit > 0 ? array_merge($sesInfo, array_fill(0, $deficit, null)): $sesInfo;
}

function SinglePage($html, $pw, $ph, $minK = 1, $inc = 0.1) {
// returns height of page
    global $mpdf;
    $mpdf->AddPage('', '', '', '', '', '', ($mpdf->w - $pw), '', ($mpdf->h - $ph), 0, 0);
    $k = 1;

    $currpage = $mpdf->page;
    $mpdf->WriteHTML($html);

    $newpage = $mpdf->page;
    while ($currpage != $newpage) {
        for ($u = 0; $u <= ($newpage - $currpage); $u++) {
            // DELETE PAGE - the added page
            unset($mpdf->pages[$mpdf->page]);
            if (isset($mpdf->ktAnnots[$mpdf->page])) {
                unset($mpdf->ktAnnots[$mpdf->page]);
            }
            if (isset($mpdf->tbrot_Annots[$mpdf->page])) {
                unset($mpdf->tbrot_Annots[$mpdf->page]);
            }
            if (isset($mpdf->kwt_Annots[$mpdf->page])) {
                unset($mpdf->kwt_Annots[$mpdf->page]);
            }
            if (isset($mpdf->PageAnnots[$mpdf->page])) {
                unset($mpdf->PageAnnots[$mpdf->page]);
            }
            if (isset($mpdf->ktBlock[$mpdf->page])) {
                unset($mpdf->ktBlock[$mpdf->page]);
            }
            if (isset($mpdf->PageLinks[$mpdf->page])) {
                unset($mpdf->PageLinks[$mpdf->page]);
            }
            if (isset($mpdf->pageoutput[$mpdf->page])) {
                unset($mpdf->pageoutput[$mpdf->page]);
            }
            // Go to page before  - so can addpage
            $mpdf->page--;
        }
        // mPDF 2.4 Float Images
        if (count($mpdf->floatbuffer)) {
            $mpdf->objectbuffer[] = $mpdf->floatbuffer['objattr'];
            $mpdf->printobjectbuffer(false);
            $mpdf->objectbuffer = [];
            $mpdf->floatbuffer = [];
            $mpdf->float = false;
        }


        $k += $inc;
        if ((1 / $k) < $minK) {
            die("Page no. " . $mpdf->page . " is too large to fit");
        }
        $w = $pw * $k;
        $h = $ph * $k;
        $mpdf->_beginpage('', '', ($mpdf->w - $w), '', ($mpdf->h - $h));
        $currpage = $mpdf->page;

        $mpdf->_out('2 J');
        $mpdf->_out(sprintf('%.2f w', 0.1 * $mpdf->k));
        $mpdf->SetFont($mpdf->default_font, '', $mpdf->default_font_size, true, true); // forces write
        $mpdf->SetDrawColor(0);
        $mpdf->SetFillColor(255);
        $mpdf->SetTextColor(0);
        $mpdf->ColorFlag = false;

        // Start Transformation
        $mpdf->StartTransform();
        $mpdf->transformScale((100 / $k), (100 / $k), 0, 0);

        $mpdf->WriteHTML($html);

        $newpage = $mpdf->page;

        //Stop Transformation
        $mpdf->StopTransform();
    }
    return ($mpdf->y / $k);
}

$stdid = '20130206037';
if(isset($_GET['stdid']))
    $stdid = $_GET['stdid'];

$query_stud = sprintf("SELECT * "
                    . "FROM student s "
                    . "JOIN programme p ON s.progid = p.progid "
                    . "JOIN department d ON p.deptid = d.deptid "
                    . "JOIN college c ON d.colid = c.colid "
                    . "JOIN session sn ON s.sesid = sn.sesid "
                    . "WHERE s.stdid = %s ", 
                    GetSQLValueString($stdid, 'text'));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = s.sesid AND g.colid = %s",
                GetSQLValueString($row_stud['colid'], "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$sesGrad = [];
for($idx =0; $idx < $totalRows_grad; $idx++, $row_grad = mysql_fetch_assoc($grad)) {
    $sesGrad[$row_grad['sesid']][$row_grad['level']] = [
        'gradeA' => $row_grad['gradeA'],
        'gradeB' => $row_grad['gradeB'],
        'gradeC' => $row_grad['gradeC'],
        'gradeD' => $row_grad['gradeD'],
        'gradeE' => $row_grad['gradeE'],
        'gradeF' => $row_grad['gradeF'],
        'passmark' => $row_grad['passmark']
    ];
}

$query_result = sprintf("SELECT r.escore + r.tscore as score, rg.sesid, rg.level, c.semester, c.csid, cr.sesid, "
                    . "sn.sesname, dc.status, dc.unit, c.status as cstatus, c.unit as cunit "
                    . "FROM result r "
                    . "JOIN course_reg cr ON r.csid = cr.csid AND r.stdid = cr.stdid AND r.sesid = cr.sesid "
                    . "JOIN registration rg ON cr.sesid = rg.sesid AND cr.stdid = rg.stdid "
                    . "JOIN course c ON cr.csid = c.csid "
                    . "JOIN session sn ON cr.sesid = sn.sesid "
                    . "LEFT JOIN department_course dc ON rg.level = dc.level AND rg.progid = dc.progid AND cr.csid = dc.csid "
                    . "WHERE cr.stdid = %s AND rg.status = 'Registered' "
                   // . "AND rg.result = 'TRUE' "
                    . "ORDER BY cr.sesid ASC, c.semester",
                    GetSQLValueString($stdid, 'text'));
$result = mysql_query($query_result, $tams) or die(mysql_error());
$totalRows_result = mysql_num_rows($result);

$firstSem = [];
$secondSem = [];
$semStats = [];
$totalStats = [];
$sesInfo = [];

for(;$row_result = mysql_fetch_assoc($result);) {
    $sesid = $row_result['sesid'];
    $level = $row_result['level'];
    $csid = $row_result['csid'];
    $score = $row_result['score'];
    $unit = (isset($row_result['unit']) && $row_result['unit'] != "")? $row_result['unit']: $row_result['cunit'];
    $status = (isset($row_result['status']) && $row_result['status'] != "")? $row_result['status']: $row_result['cstatus'];
    $statusChar = substr($status, 0, 1);
    $gp = gradepoint($unit, $row_result['score'], $level, $sesid, $sesGrad, false);
    $nup = $score >= getPassmark($sesid, $status, $level, $sesGrad)? $unit: 0;
    
    if(strtolower($row_result['semester']) == 'f') {
        $firstSem[$row_result['sesid']][] = [$csid, $score, $unit, $statusChar, $gp, $nup];
        $semStats[$row_result['sesid']]['first'][0] = 'SEMESTER';
        $semStats[$row_result['sesid']]['first'][1] = isset($semStats[$row_result['sesid']]['first'][1])? $semStats[$row_result['sesid']]['first'][1] + $unit: $unit;
        $semStats[$row_result['sesid']]['first'][2] = isset($semStats[$row_result['sesid']]['first'][2])? $semStats[$row_result['sesid']]['first'][2] + $gp: $gp;
        $semStats[$row_result['sesid']]['first'][3] = isset($semStats[$row_result['sesid']]['first'][3])? $semStats[$row_result['sesid']]['first'][3] + $nup: $nup;  
        $semStats[$row_result['sesid']]['first'][4] = $semStats[$row_result['sesid']]['first'][1] > 0? round($semStats[$row_result['sesid']]['first'][2] / $semStats[$row_result['sesid']]['first'][1], 2): 0; 
        $semStats[$row_result['sesid']]['first'][5] = null;
    } else {
        $secondSem[$row_result['sesid']][] = [$csid, $score, $unit, $statusChar, $gp, $nup];
        $semStats[$row_result['sesid']]['second'][0] = 'SEMESTER';
        $semStats[$row_result['sesid']]['second'][1] = isset($semStats[$row_result['sesid']]['second'][1])? $semStats[$row_result['sesid']]['second'][1] + $unit: $unit;
        $semStats[$row_result['sesid']]['second'][2] = isset($semStats[$row_result['sesid']]['second'][2])? $semStats[$row_result['sesid']]['second'][2] + $gp: $gp;
        $semStats[$row_result['sesid']]['second'][3] = isset($semStats[$row_result['sesid']]['second'][3])? $semStats[$row_result['sesid']]['second'][3] + $nup: $nup;  
        $semStats[$row_result['sesid']]['second'][4] = $semStats[$row_result['sesid']]['second'][1] > 0? round($semStats[$row_result['sesid']]['second'][2] / $semStats[$row_result['sesid']]['second'][1], 2): 0; 
        $semStats[$row_result['sesid']]['second'][5] = null;
    }
    
    $sesInfo[$row_result['sesname']] = sprintf("%s (%s)", $row_result['level'], $row_result['sesname']);
    $semStats[$row_result['sesid']]['total'][0] = isset($semStats[$row_result['sesid']]['total'][0])? $semStats[$row_result['sesid']]['total'][0] + $unit: $unit;
    $semStats[$row_result['sesid']]['total'][1] = isset($semStats[$row_result['sesid']]['total'][1])? $semStats[$row_result['sesid']]['total'][1] + $gp: $gp;
    $semStats[$row_result['sesid']]['total'][2] = isset($semStats[$row_result['sesid']]['total'][2])? $semStats[$row_result['sesid']]['total'][2] + $nup: $nup;  
    $semStats[$row_result['sesid']]['total'][3] = $semStats[$row_result['sesid']]['total'][0] > 0? round($semStats[$row_result['sesid']]['total'][1] / $semStats[$row_result['sesid']]['total'][0], 2): 0; 
    $semStats[$row_result['sesid']]['level'] = $row_result['level'];
            
    $totalStats['tut'] = isset($totalStats['tut'])? $totalStats['tut'] + $unit: $unit;
    $totalStats['tgp'] = isset($totalStats['tgp'])? $totalStats['tgp'] + $gp: $gp;
    $totalStats['tup'] = isset($totalStats['tup'])? $totalStats['tup'] + $nup: $nup; 
    $totalStats['cgpa'] = $totalStats['tut'] > 0? round($totalStats['tgp'] / $totalStats['tut'], 2): 0; 
}

$colCount = count(array_keys($sesInfo)) <= 4? 4: 6;
$semStats = calculateCumulativeGPA($semStats);
$firstSemPadding = calculateSemesterPadding($firstSem, $colCount);
$secondSemPadding = calculateSemesterPadding($secondSem, $colCount);
$sesInfo = padSessionInfo($sesInfo, $colCount);

$html = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../../img/logo/school-logo.png" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h3 style="font-size: 17pt">'.$row_stud['colname'].'<br></h3>
<h4 style="font-size: 13pt">'.$row_stud['deptname'].'</h4>
</td>
</tr>
</table>
<div style="text-align:center; width:100%; font-size: 16pt">TRANSCRIPT</div>';

$html .= '
        <table width="100%" style="border: 1px solid #999999;" class="table table-bordered table-condensed">
            <tr>
                <td colspan="'.($colCount * 6 + 1).'">
                    NAME: &nbsp;&nbsp;&nbsp;&nbsp;'.sprintf("%s, %s %s", strtoupper($row_stud["lname"]), ucfirst($row_stud["fname"]), ucfirst($row_stud["mname"])).'&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
                    MATRIC NO.: &nbsp;&nbsp;&nbsp;&nbsp;'.$row_stud["stdid"].'&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
                    SUB. COMB: &nbsp;&nbsp;&nbsp;&nbsp;'.$row_stud["progname"].'&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
                    YEAR OF ENTRY: &nbsp;&nbsp;&nbsp;&nbsp;'.$row_stud["sesname"].'&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
                    D.O.B: &nbsp;&nbsp;&nbsp;&nbsp;'.$row_stud["dob"].'&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
                    SEX: &nbsp;&nbsp;&nbsp;&nbsp;'.$row_stud["sex"].'
                </td>
            </tr><tr>';
            
         
foreach($sesInfo as $ses => $r) {
    $html .= '    <td colspan="6">'.$r.'</td>';
}

$html .= '</tr>            
            <tr>';

for($idx = 0; $idx < $colCount; $idx++) {
    $html .= '<td>Code</td>
                <td>Score</td>
                <td>Unit</td>
                <td>ST</td>
                <td>GP</td>
                <td>NUP</td> ';
                    
}         
           
$html .= '</tr>';
            
for($idx = 0; $row = getTranscriptRow($firstSem, $idx, $firstSemPadding); $idx++) {
            
    $html .= '<tr>';
    
    foreach($row as $r) {
        $html .= '<td>'.$r.'</td>';
    }
    
    $html .= '</tr>';
}
            
$html .= '<tr>';

for($idx = 0; $idx < $colCount; $idx++) {
    $html .= '<td>1st</td>
                <td>TUT</td>
                <td>TGP</td>
                <td>TUP</td>
                <td>SGPA</td>
                <td></td> ';
}          
 
$html .= '</tr><tr>';

$row = getStatsRow($semStats, 'first');

foreach($row as $r) {
    $html .= '<td>'.$r.'</td>';
}

$html .= '<td></td>          
            </tr>
            <tr>';

for($idx = 0; $idx < $colCount; $idx++) {
    $html .= '<td>Code</td>
                <td>Score</td>
                <td>Unit</td>
                <td>ST</td>
                <td>GP</td>
                <td>NUP</td> ';
                    
}        

$html .= '</tr>';
            

for($idx = 0; $row = getTranscriptRow($secondSem, $idx, $secondSemPadding); $idx++) {
    $html .= '<tr>';
    
    foreach($row as $r) {
        $html .= '<td>'.$r.'</td>';
    }
    
    $html .= '</tr>';
}
    
$html .= '<tr>';                

for($idx = 0; $idx < $colCount; $idx++) {
    $html .= '<td>2nd</td>
                <td>TUT</td>
                <td>TGP</td>
                <td>TUP</td>
                <td>SGPA</td>
                <td></td> ';
}

$html .= '</tr>            
            <tr>';

$row = getStatsRow($semStats, 'second');

foreach($row as $r) {
    $html .= '<td>'.$r.'</td>';
}

$html .= '<td></td>          
            </tr>
            
            <tr>';

$row = getCumulativeRow($semStats);

for($idx = 0; $idx < $colCount; $idx++) {
    $pos = 6 * $idx;
    $level = $row[$pos++];
    $tut = $row[$pos++];
    $tgp = $row[$pos++];
    $tup = $row[$pos++];
    $wgpa = $row[$pos++];
    $cgpa = $row[$pos];
                    
    $html .= '<td rowspan="2">'.$level.'</td>
                <td rowspan="2" colspan="5">
                    TUT: '.$tut.' &nbsp; TUP: '.$tup.'<br/>
                    WGPA: '.$wgpa.' &nbsp; TGP: '.$tgp.'<br/>
                    CGPA: '.$cgpa.'<br/>
                </td>';
}        

$html .= '</tr>
        </table>';

$html .= '<table width="15%" class="table table-bordered"><tr><td>';
$html .= '
            TUT: ' . $totalStats['tut'] . '<br/>
            TUP: ' . $totalStats['tup'] . '<br/>
            TGP: ' . $totalStats['tgp'] . '<br/>
            CGPA: ' . $totalStats['cgpa'] . '<br/>
        ';
$html .= '</td></tr>'
        . '</table>';

SinglePage($html, $pw, $ph, $minK);
$mpdf->Output('Transcript.pdf', 'I');
exit;
