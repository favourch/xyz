<div id="left">
    <div class="subnav">
        <div class="subnav-title">
            <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">General Information</span></a>
        </div>
        <ul class="subnav-menu">
            <li>
                <a href="/<?= $site_root ?>/college/index.php"><?= $college_name ?></a>                
            </li>
            <li>
                <a href="/<?= $site_root ?>/department/index.php"><?= $department_name ?></a>
            </li>
            <li>
                <a href="/<?= $site_root ?>/course/index.php">Courses</a>
            </li>
            <?php if (in_array(getAccess(), [1, 2, 3, 4, 5, 6, 20, 21, 22, 23,24,25,26, 27,28])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/student/index.php">Student</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/staff/index.php">Staff</a>
                </li>
            <?php endif; ?>

        </ul>
    </div>

    <?php if (in_array(getAccess(), [20,28])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Helpdesk</span></a>
            </div>
            <ul class="subnav-menu">            
                <li>
                    <a href="/<?= $site_root ?>/admin/student/">Student</a>                
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/staff">Staff</a>                
                </li> 
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/searchapp.php">Prospective</a>                
                </li>   
            </ul>                                   
        </div>
    <?php endif; ?>
    <?php if (in_array(getAccess(), [20,31])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Clinc</span></a>
            </div>
            <ul class="subnav-menu">            
                <li>
                    <a href="/<?= $site_root ?>/clinic/admin/">Clear Student</a>                
                </li>
            </ul>                                   
        </div>
    <?php endif; ?>
   
    <?php if (in_array(getAccess(), [20, 33])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Exams And Record</span></a>
            </div>
            <ul class="subnav-menu">            
                <!---<li>
                    <a href="/<?= $site_root ?>/admin/result/broadsheet.php">Broadsheet</a>                
                </li>
                <li>
                   <a href="/<?= $site_root ?>/admin/result/summarysheet.php">Summary Sheet</a>              
                </li> -->
                <li>
                    <a href="/<?= $site_root ?>/exam_and-rec">Transcript</a>                
                </li>   
               
            </ul>                                   
        </div>
    <?php endif; ?>
    
    <?php if (in_array(getAccess(), [20, 32])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">NYCS MOB.</span></a>
            </div>
            <ul class="subnav-menu">            
                
                <li>
                    <a href="/<?= $site_root ?>/admin/nysc/">Search Student</a>                
                </li>   
               
            </ul>                                   
        </div>
    <?php endif; ?>
    
    <?php if (in_array(getAccess(), [1, 20, 21, 23,27])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Admin Menu</span></a>
            </div>
            <ul class="subnav-menu">
                <li>
                    <a href="/<?= $site_root ?>/admin/academic/planning.php">Academic Planning</a>                
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/academic/fte.php">FTE Calculations</a>                
                </li>
                <?php if (in_array(getAccess(), [1,20])) : ?>
                 <li>
                    <a href="/<?= $site_root ?>/admin/dashboard.php">School Setup</a>                
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/payment/index.php">Payment Management</a>                
                </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [1,20,27])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/disciplinary/index.php">Disciplinary Action</a>                
                </li>
                <?php endif; ?>
            </ul>                
        </div>
    <?php endif; ?>



    <?php if (in_array(getAccess(), [11])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Prospective Students</span></a>
            </div>
            <ul class="subnav-menu ">
                <li>
                    <a href="/<?= $site_root ?>/admission/termsandcon.php">Instructions</a>                
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admission/progress.php">Application Guide</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/olevel_service/index.php">O&apos;level Verification</a>
                </li>
               <!-- <li>
                    <a href="/<?= $site_root ?>/admission/viewform.php">View Form</a>
                </li> -->
               <!-- <li>
                    <a href="/<?= $site_root ?>/admission/status.php">Admission Status</a>
                </li> -->
                <li>
                    <a href="/<?= $site_root ?>/admission/payhistory.php">Payment History</a>
                </li>
                <?php if (isset($_SESSION) && getSessionValue('admtype') == 1) : ?>
                <li>
                <!-- <a href="/<?= $site_root ?>/admission/screenslip.php" target='_new'>Print Screening Slip</a> -->
                </li>
               <?php endif; ?>
                
            </ul>                
        </div>
    <?php endif; ?>

    <?php if (in_array(getAccess(), [1, 3, 20, 21, 24,26, 23,27,28])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Admission</span></a>
            </div>
            <ul class="subnav-menu">
            
            <?php if (in_array(getAccess(), [1, 20, 21,24,23,27,28])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission">Admission Overview</a>                
                </li>
           <?php endif; ?> 
           
           <?php if (in_array(getAccess(), [1,3])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/dept_applist.php">Admitted List</a>                
                </li>
           <?php endif; ?> 
  
                <?php if (in_array(getAccess(), [1, 20, 24])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/searchapp.php">Search Applicants</a>
                </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [1, 20])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/manage_admission.php">Manage Admissions</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/veri_code.php">Verification Codes</a>
                </li>
              <?php endif; ?>
                
                <?php if (in_array(getAccess(), [1, 20, 21, 22, 26, 24, 28])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/olevel_service/index.php">O&apos;Level Verification</a>
                </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [1, 20, 22])) : ?>
                <li>
                    <!--<a href="/<?= $site_root ?>/admin/olevel_verifier_mgt/index.php"> O&apos;Level Management</a>-->
                </li>
                <?php endif; ?>
            </ul>                
        </div>
    <?php endif; ?>

    <?php if (in_array(getAccess(), [1, 2, 3, 20, 28])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down">

                    </i><span style="font-size: 15px; color: brown">Course Administration</span></a>
            </div>
            <ul class="subnav-menu">
                <li>
                    <a href="/<?= $site_root ?>/course_admin/course_status.php">Course Status</a> 
                </li>

                <li>
                    <a href="/<?= $site_root ?>/course_admin/adviser.php">Staff Adviser</a>
                </li>

                <li>
                    <a href="/<?= $site_root ?>/course_admin/index.php">Course Allocation</a>
                </li>
            </ul>                
        </div>
    <?php endif; ?>
    

    <?php if (in_array(getAccess(), [1, 2, 3, 6, 10])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Course Registration</span></a>
            </div>
            <ul class="subnav-menu">
                 <?php if (in_array(getAccess(), [3, 6])) : ?>
                    <li>
                        <a href="/<?= $site_root ?>/registration/processform.php">Clear Course Form</a>                     
                    </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [10])) : ?>
                    <li>
                        <a href="/<?= $site_root ?>/registration/registercourse.php">Register Courses</a>                     
                    </li>
                    <?php if ($_SESSION['level'] == 4) : ?>
                    <li>
                        <a href="/<?= $site_root ?>/tp/index.php">Teaching Practice</a>                    
                    </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION) && getSessionValue('epass') == 'TRUE') : ?>
                    <li>
                        <a target="_blank" href="/<?= $site_root ?>/registration/exampass.php">Exam Pass</a>
                    </li>
                    <?php endif; ?>
                    
                <?php endif; ?>
                    <li>
                        <a href="/<?= $site_root ?>/registration/course_reg_form.php?stid=<?= $_SESSION['MM_Username'] ?>">View Course Form</a>
                    </li>
                
            </ul>                
        </div>
    <?php endif; ?>


    <?php if (in_array(getAccess(), [10])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Accommodation</span></a>
            </div>
            <ul class="subnav-menu">
                    <li>
                        <a href="/<?= $site_root ?>/accommodation">Accommodation</a>
                    </li>                
            </ul>                
        </div>
    <?php endif; ?>
    
    <?php if (in_array(getAccess(), [3, 20])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Dept. Clearance</span></a>
            </div>
            <ul class="subnav-menu">
                    <li>
                        <a href="/<?= $site_root ?>/dept_clear/index.php">Clear Prospective</a>
                    </li>                
            </ul>                
        </div>
    <?php endif; ?>
    

    <?php if (in_array(getAccess(), [1, 2, 3, 10])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Payments</span></a>
            </div>
            <ul class="subnav-menu">
                <?php if (in_array(getAccess(), [1, 20])) : ?>
                    <li>
                        <a href="/<?= $site_root ?>/admin/payment/index.php">Payment Management</a>                
                    </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [1, 20, 2, 3])) : ?> 
                    <li>
                        <a href="/<?= $site_root ?>/payments/paylist.php">Payment List</a>                
                    </li>
                <?php endif; ?>   
                <?php if (in_array(getAccess(), [10])) : ?>     
                    <li>
                        <a href="/<?= $site_root ?>/payments/index.php">Pay School Fee</a>                
                    </li>
                    <?php if (in_array(getAccess(), [10]) && ($_SESSION['level'] >3)) : ?>
                        <li>
                            <a href="/<?= $site_root ?>/clearance_fee/index.php">Pay Clearance Fee</a>                
                        </li>
                        <li>
                            <a href="/<?= $site_root ?>/clearance_fee/pay_verify.php" target="_blank">Print Clearance Cert</a>                
                        </li>
                        <?php if (isset($_SESSION) && ($_SESSION['stdstatus'] == 'Graduating' || $_SESSION['stdstatus'] == 'Graduated')) : ?>
                       <li>
                           <a target="_blank" href="/<?= $site_root ?>/gradfee/index.php">Graduation Fee</a>
                       </li>
                       <?php endif; ?>
                    <?php endif; ?>  
                    <?php if (in_array(getAccess(), [10]) && ($_SESSION['level'] > 3)) : ?>
                        <li>
                            <a href="/<?= $site_root ?>/reparation_fee/index.php">Pay Reparation Fee</a>                
                        </li>
                        <li>
                         <!--  <a href="/<?= $site_root ?>/tp_penalty/index.php">Pay TP Penalty Fee</a>       -->       
                        </li>
                        <li>
                            <a href="/<?= $site_root ?>/olevel_service/index.php">O&apos;Level Verification</a>
                        </li>
                    <?php endif; ?> 
                    <?php if (in_array(getAccess(), [10]) && ($_SESSION['level'] > 3) && (strlen($_SESSION['$degree'])>0)) : ?>
                    <li>
                            <a href="/<?= $site_root ?>/olevel_service/index.php">O&apos;Level Verification</a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="/<?= $site_root ?>/payments/pay_history.php">Pay History</a>                
                    </li>
                <?php endif; ?>      
            </ul>               
        </div>
    <?php endif; ?>

    <?php if (in_array(getAccess(), [1, 2, 3, 4, 5, 6, 10,20,21,28])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Results</span></a>
            </div>
            <ul class="subnav-menu">
                <?php if (in_array(getAccess(), [1, 2, 3, 4, 5, 6])) : ?>
                    <li>
                        <a href="/<?= $site_root ?>/result/uploadresult.php">Upload Result</a>                  
                    </li>
                <?php endif; ?>

                <?php if (in_array(getAccess(), [1, 2, 3, 4, 5, 6, 10])) : ?>
                    <li>
                        <a href="/<?= $site_root ?>/result/resultstatement.php">Statement of Result</a>                     
                    </li>
                <?php endif; ?>
                
                <?php if (in_array(getAccess(), [1,2,3])) : ?>
             	     <li>
                        <a href="/<?= $site_root ?>/result/">Consider Result</a>                  
                    </li>
                    <li>
                        <a href="/<?= $site_root ?>/result/summarysheet.php">Print SummarySheet</a>                     
                    </li>
                    <li>
                        <a href="/<?= $site_root ?>/result/broadsheet.php">Print BroadSheet</a>                     
                    </li>
                <?php endif; ?>
                <?php if(in_array(getAccess(), [20])):?>                
                <li>
                    <a href="/<?= $site_root ?>/admin/result/considerresult.php">Consider Results</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/result/broadsheet.php">Print Broadsheet</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/result/summarysheet.php">Print Summary Sheet</a>
                </li>
                <?php endif;?>
                <?php if (in_array(getAccess(), [4, 20, 21,28])) : ?>
                 <li>
                    <a href="/<?= $site_root ?>/admin/result/index.php">Print CENVOS Results</a>
                </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [4, 20])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/result/resultlist.php">Consider CENVOS Results</a>
                </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [20])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/result/uploadresult.php">Upload Results</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/result/general/index.php">Upload Test Scores</a>
                </li>
                <?php endif; ?>
                
            </ul>                                   
        </div>
    <?php endif; ?>
    
    <?php if (in_array(getAccess(), [1, 20, 21, 23])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Bursary</span></a>
            </div>
            <ul class="subnav-menu">  
                    <?php if (in_array(getAccess(), [1, 20, 23])) : ?>            
                    <li>
                        <a href="/<?= $site_root ?>/admin/bursary/acct_mgt/index.php">Account Management</a>                
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="/<?= $site_root ?>/admin/bursary/paylist/index.php">Payment Lists</a>                
                    </li> 
                    <li>
                        <a href="/<?= $site_root ?>/admin/bursary/charges.php">IMBIL Charges</a>                
                    </li> 
                    <li>
                        <a href="/<?= $site_root ?>/admin/bursary/payment_history/index.php">Payments History</a>                
                    </li> 
                    <li>
                        <a href="/<?= $site_root ?>/admin/bursary/reg_fee/index.php">TMFB Registration Fee</a>                
                    </li> 
                    <li>
                        <a href="/<?= $site_root ?>/admin/bursary/clear_admin/index.php">TMFB Clearance Fee</a>                
                    </li>
                    <li>
                        <a href="/<?= $site_root ?>/admin/bursary/gradfee/index.php">TMFB Graduation Fee</a>                
                    </li>
                            
            </ul>                                   
        </div>
    <?php endif; ?>
    
    <?php if (in_array(getAccess(), [1,2,3,4,5,6,10])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Campus Market</span></a>
            </div>
            <ul class="subnav-menu">  
          
               <li>
                 <a href="/<?= $site_root ?>/market/">Buy from Store</a>                  
               </li>
               <li>
                 <a href="/<?= $site_root ?>/market/sell.php">Sell Your Product</a>                  
               </li>
               <li>
                 <a href="/<?= $site_root ?>/market/mypost.php">View My Stock</a>                  
               </li>
             
             </ul>                                   
        </div>
    <?php endif; ?>
    
</div>