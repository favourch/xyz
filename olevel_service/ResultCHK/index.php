<?php 
require_once __DIR__.'/vendor/autoload.php';

use Repositories\ResultChecker\ResultChecker;
 

//$rc = new ResultChecker;
//
///**
// * @var ResultProviderInterface Description
// */
////$rpi = $rc->getProvider('waec');
////$rpi->set_exam_year(2010);
////$rpi->set_exam_type('01');
////$rpi->set_exam_number('14018003');
////$rpi->set_card_pin('135621612973');
////$rpi->set_card_sn('NBRC14081457');
// 
//$rpi = $rc->getProvider('nabteb');
//$rpi->set_exam_year(2009);
////$rpi->set_exam_type('NOV/DEC');
//$rpi->set_exam_type('01');
//$rpi->set_exam_number('25015078');
//$rpi->set_card_pin('129925228534');
//$rpi->set_card_sn('NBRC14231527');
//
//$result = $rpi->fetch_result();
//
////echo  $result->get_result_html();
//header('Content-Type: application/json');
//echo $result->get_json_result();


class Result{
    
    function getResult($exam, $type, $year,$number, $pin, $sn){
        $rc = new ResultChecker;
        $rpi = $rc->getProvider($exam);
        $rpi->set_exam_year($year);
        //$rpi->set_exam_type('NOV/DEC');
        $rpi->set_exam_type($type);
        $rpi->set_exam_number($number);
        $rpi->set_card_pin($pin);
        $rpi->set_card_sn($sn);
        
        return $result = $rpi->fetch_result();
    }
}