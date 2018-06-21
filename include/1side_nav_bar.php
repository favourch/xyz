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
            <?php if (in_array(getAccess(), [1, 2, 3, 4, 5, 6, 20, 21, 22, 23,24,25,26, 27])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/student/index.php">Student</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/staff/index.php">Staff</a>
                </li>
            <?php endif; ?>

        </ul>
    </div>

    <?php if (in_array(getAccess(), [1, 20, 21, 23,27])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span span style="font-size: 15px; color: brown">Admin Menu</span></a>
            </div>
            <ul class="subnav-menu">
                <li>
                    <a href="/<?= $site_root ?>/admin/academic/planning.php">Academic Planning</a>                
                </li>
                <?php if (in_array(getAccess(), [1,20])) : ?>
                 <li>
                    <a href="/<?= $site_root ?>/admin/dashboard.php">School Setup</a>                
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/payment/index.php">Payment Management</a>                
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
                    <a href="/<?= $site_root ?>/admission/appform.php">Application Form</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admission/viewform.php">View Form</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admission/status.php">Admission Status</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admission/payhistory.php">Payment History</a>
                </li>
                <li>
                   <!-- <a href="/<?= $site_root ?>/admission/examslip.php" target='_new'>Exam Slip</a> -->
                </li>
                <li>
                    <a href="/<?= $site_root ?>/olevel_verifier/index.php">O'level Verification</a>
                </li>
            </ul>                
        </div>
    <?php endif; ?>

    <?php if (in_array(getAccess(), [1, 20, 21, 24,26, 23,27])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down"></i><span style="font-size: 15px; color: brown">Admission</span></a>
            </div>
            <ul class="subnav-menu">
            
            
                <li>
                    <a href="/<?= $site_root ?>/admin/admission">Admission Overview</a>                
                </li>
                
                <?php if (in_array(getAccess(), [1, 20, 24])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/searchapp.php">Search Applicants</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/manage_admission.php">Manage Admissions</a>
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/veri_code.php">View Verification Codes</a>
                </li>
              <?php endif; ?>
                
                <?php if (in_array(getAccess(), [1, 20, 26, 24])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/olevel_veri_code/index.php">Release Verification Codes</a>
                </li>
                <?php endif; ?>
                <?php if (in_array(getAccess(), [1, 20, 22, 24])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/olevel_verifier_mgt/index.php"> O`Level Management</a>
                </li>
                <?php endif; ?>
            </ul>                
        </div>
    <?php endif; ?>

    <?php if (in_array(getAccess(), [1, 2, 3])) : ?>
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
    
    <?php if (in_array(getAccess(), [1, 20, 4])) : ?>
        <div class="subnav">
            <div class="subnav-title">
                <a href="#" class='toggle-subnav'><i class="icon-angle-down">

                    </i><span style="font-size: 15px; color: brown">Cenvos Results</span></a>
            </div>
            <ul class="subnav-menu">
                <li>
                    <a href="/<?= $site_root ?>/admin/result/index.php">Print Results</a>
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
                 
                <?php if (in_array(getAccess(), [10])) : ?>
                    <li>
                        <a href="/<?= $site_root ?>/registration/registercourse.php">Register Courses</a>                     
                    </li>
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



    <?php if (in_array(getAccess(), [1, 2, 3, 10, 20])) : ?>
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
                            <a href="/<?= $site_root ?>/clearance_fee/pay_verify.php">Print Clearance Cert</a>                
                        </li>
                    <?php endif; ?>  
                    <?php if (in_array(getAccess(), [10]) && ($_SESSION['level'] > 2)) : ?>
                        <li>
                            <a href="/<?= $site_root ?>/reparation_fee/index.php">Pay Reparation Fee</a>                
                        </li>
                        <li>
                            <a href="/<?= $site_root ?>/olevel_verifier/index.php">O`level Verification</a>
                        </li>
                    <?php endif; ?> 
                    <li>
                        <a href="/<?= $site_root ?>/payments/pay_history.php">Pay History</a>                
                    </li>
                <?php endif; ?>      
            </ul>               
        </div>
    <?php endif; ?>

    <?php if (in_array(getAccess(), [1, 2, 3, 4, 5, 6, 10])) : ?>
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
                
                <?php if (in_array(getAccess(), [2,3])) : ?>
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
                        <a href="/<?= $site_root ?>/admin/bursary/payment_history/index.php">Payments History</a>                
                    </li> 
                            
            </ul>                                   
        </div>
    <?php endif; ?>
    
</div>