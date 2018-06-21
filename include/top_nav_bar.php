<?php
$dir = IMGPATH;
switch(getSessionValue('accttype')) {
    case 'stud':
        $dir .= "/user/student";
        break;
    
    case 'pros':
        $year = explode('/', getSessionValue('admname'));
        $dir .= "/user/prospective/{$year[0]}";
        break;
    
    case 'staff':
        $dir .= "/user/staff";
        break;
}
?>

<div id="navigation" class="navbar-fixed-top">
    <div class="container-fluid">
        <a href="/<?= $site_root?>/index.php" id="brand">TAMS</a>
        <a href="#" class="toggle-nav" rel="tooltip" data-placement="bottom" title="Toggle navigation"><i class="icon-reorder"></i></a>
        <ul class='main-nav'>
            <li class="pull-left">
                <a href="/<?= $site_root?>/index.php">
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                    <span>General Information</span>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
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
            </li>

             <?php if (in_array(getAccess(), [1, 20, 21, 23, 27])) : ?>
                <li>
                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                        <span>Admin Menu</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/<?= $site_root ?>/admin/academic/planning.php">Academic Planning</a>                
                        </li>
                        <li>
                            <a href="/<?= $site_root ?>/admin/academic/fte.php">FTE Calculations</a>                
                        </li>
                        <?php if (in_array(getAccess(), [1, 20])) : ?>
                            <li>
                                <a href="/<?= $site_root ?>/admin/dashboard.php">School Setup</a>                
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/admin/disciplinary/index.php">Disciplinary Action</a>                
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/admin/payment/index.php">Payment Management</a>                
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/course_admin/course_status.php">Course Status</a> 
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            
            <?php if (in_array(getAccess(), [20,28])) : ?>
                <li>
                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                        <span>Helpdesk</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">            
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
                </li>
            <?php endif; ?>
            
            <?php if (in_array(getAccess(), [20,30])) : ?>
                <!--<li>
                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                        <span>Exams and Record</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">            
                <li>
                    <a href="/<?= $site_root ?>/admin/result/broadsheet.php">Broadsheet</a>                
                </li>
                <li>
                    <a href="/<?= $site_root ?>/admin/result/summarysheet.php">Summary Sheet</a>                
                </li> 
                <li>
                    <a href="/<?= $site_root ?>/admin/admission/searchapp.php">Transcript</a>                
                </li>
            </ul>     
                </li> -->
            <?php endif; ?>
            
                <?php if (in_array(getAccess(), [1, 20, 21,28, 2, 3, 4, 5, 6, 10])) : ?>
                    <li>
                        <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                            <span>Results</span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
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

                            <?php if (in_array(getAccess(), [2, 3])) : ?>
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
                            
                             <?php if(in_array(getAccess(), [1,20])):?>                
                            <li>
                                <a href="/<?= $site_root ?>/admin/result/considerresult.php">Consider Results</a>
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/admin/result/broadsheet.php">Print Broadsheet</a>
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/admin/result/summarysheet.php">Print Summary Sheet</a>
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/admin/result/uploadresult.php">Upload Results</a>
                            </li>
                            <?php endif;?>
                            
                            <?php if (in_array(getAccess(), [20, 21,28, 4])) : ?>
                                <li>
                                    <a href="/<?= $site_root ?>/admin/result/index.php">Print CENVOS Results</a>
                                </li>
                                <?php if (in_array(getAccess(), [20, 4])) : ?>
                                    <li>
                                        <a href="/<?= $site_root ?>/admin/result/resultlist.php">Consider CENVOS Results</a>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                        </ul>
                    </li>
                <?php endif; ?>   
             
                    
                    <?php if (getAccess() == 11) : ?>
                        <li>
                            <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                                <span>Prospective Student</span>
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <!--<a href="/<?= $site_root ?>/admission/progress.php">Instructions</a> -->               
                                </li>
                                <li>
                                    <a href="/<?= $site_root ?>/admission/progress.php">Application Guide</a>
                                </li>
                                
                                <li>
                                    <a href="/<?= $site_root ?>/olevel_service/index.php">O&apos;level Verification</a>
                                </li>
                                
                                <!--<li>
                                    <a href="/<?= $site_root ?>/admission/viewform.php">View Form</a>
                                </li>-->
                               <!-- <li>
                                    <a href="/<?= $site_root ?>/admission/status.php">Admission Status</a>
                                </li> -->
                                <li>
                                    <a href="/<?= $site_root ?>/admission/payhistory.php">Payment History</a>
                                </li>
                                <?php if (isset($_SESSION) && getSessionValue('admtype') == 1) : ?>    
                                  <!--  <li>
                                        <a href="/<?= $site_root ?>/admission/screenslip.php" target='_new'>Print Screening Slip</a> 
                                    </li> -->
                                <?php endif; ?>
                                
                            </ul>
                        </li>
                    <?php endif; ?>

                        <?php if (in_array(getAccess(), [1, 20, 21, 22, 23, 24,28])) : ?>
                            <li>
                                <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                                    <span>Admission</span>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if (in_array(getAccess(), [1, 20, 21, 24, 23, 27,28])) : ?>
                                        <li>
                                            <a href="/<?= $site_root ?>/admin/admission">Admission Overview</a>                
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
                                            <a href="/<?= $site_root ?>/admin/admission/veri_code.php">View Verification Codes</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (in_array(getAccess(), [1, 20, 21, 22, 26, 24, 28])) : ?>
                                        <li>
                                            <a href="/<?= $site_root ?>/olevel_service/index.php">O&apos;Level Verification</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if (in_array(getAccess(), [1, 20, 22])) : ?>
                                        <li>
                                            <!--<a href="/<?= $site_root ?>/admin/olevel_verifier_mgt/index.php"> O`Level Management</a> -->
                                        </li>
                                    <?php endif; ?>

                                </ul>
                            </li>
                        <?php endif; ?>    
                            
                        <?php if (in_array(getAccess(), [1, 2, 3, 10])) : ?>
                                <li>
                                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                                        <span>Registration</span>
                                        <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
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
                                             <?php if (isset($_SESSION) && getSessionValue('status') == 'Graduating') : ?>
                    				<li>
                        				<a target="_blank" href="/<?= $site_root ?>/gradfee/index.php">Graduation Fee</a>
                    				</li>
                    			     <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (in_array(getAccess(), [10, 1, 2, 3, 6])) : ?>
                                            <li>
                                                <a href="/<?= $site_root ?>/registration/course_reg_form.php?stid=<?= getSessionValue('uid') ?>">View Course Form</a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (in_array(getAccess(), [2, 3])) : ?>
                                            <li>
                                                <a href="/<?= $site_root ?>/course_admin/course_status.php">Course Status</a> 
                                            </li>

                                            <li>
                                                <a href="/<?= $site_root ?>/course_admin/adviser.php">Staff Adviser</a>
                                            </li>

                                            <li>
                                                <a href="/<?= $site_root ?>/course_admin/index.php">Course Allocation</a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (in_array(getAccess(), [3, 6])) : ?>
                                            <li>
                                                <a href="/<?= $site_root ?>/registration/processform.php">Clear Course Form</a>                     
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            
                                <?php if (in_array(getAccess(), [1, 20, 21, 23])) : ?>
                                    <li>
                                        <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                                            <span>Bursary</span>
                                            <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu">
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
                                    </li>
                                <?php endif; ?> 
                                    <?php if (in_array(getAccess(), [2, 3, 10])) : ?>
                                        <li>
                                            <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                                                <span>Payments</span>
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu">

                                                <?php if (in_array(getAccess(), [1, 2, 3])) : ?> 
                                                    <li>
                                                        <a href="/<?= $site_root ?>/payments/paylist.php">Payment List</a>                
                                                    </li>
                                                <?php endif; ?>   
                                                <?php if (in_array(getAccess(), [10])) : ?>     
                                                    <li>
                                                        <a href="/<?= $site_root ?>/payments/index.php">Pay School Fee</a>                
                                                    </li>
                                                    <?php if (in_array(getAccess(), [10]) && ($_SESSION['level'] > 3)) : ?>
                                                        <li>
                                                           <a href="/<?= $site_root ?>/clearance_fee/index.php">Pay Clearance Fee</a>                
                                                        </li> 
                                                        <li>
                                                            <a href="/<?= $site_root ?>/clearance_fee/pay_verify.php">Print Clearance Cert</a>                
                                                        </li>

                                                        <li>
                                                            <a href="/<?= $site_root ?>/reparation_fee/index.php">Pay Reparation Fee</a>                
                                                        </li>
                                                        <!--<li>
                            				                <a href="/<?= $site_root ?>/tp_penalty/index.php">Pay TP Penalty Fee</a>               
                       					                </li>-->
                                                    <?php endif; ?> 
                                                    <?php if (in_array(getAccess(), [10]) && ($_SESSION['level'] > 3) && ($_SESSION['$degree'] != 'NULL')) : ?>
                                                        <li>
                                                            <a href="/<?= $site_root ?>/olevel_service/index.php">O&apos;Level Verification</a>
                                                        </li>
                                                    <?php endif; ?>

                                                    <li>
                                                        <a href="/<?= $site_root ?>/payments/pay_history.php">Pay History</a>                
                                                    </li>
                                                <?php endif; ?>  
                                            </ul>
                                        </li>
                                    <?php endif; ?>  
            
                                        <?php if (in_array(getAccess(), [1, 2,3,4,5,6,10])) : ?>
                                        <li>
                                            <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                                                <span>Market</span>
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu">
                                                        
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
                                        </li>
                                        <?php endif; ?>
                                        
            </ul>
                                        
                         <div class="user">
            <?php if (getLogin()) : ?>
                <div class="dropdown">
                    <a href="#" class='dropdown-toggle' data-toggle="dropdown">
                        <img src="<?= get_pics(getSessionValue('uid'), $dir)?>" alt="" width="27" height="27">
                        <?php echo getSessionValue('lname') ?>
                        <span class="caret" 
                            style="border-top-color: #fff;">
                        </span>
                    </a>
               
                    <ul class="dropdown-menu pull-right">
                        <?php if (getAccess() == 10) : ?>
                            <li>
                                <a href="/<?= $site_root?>/student/profile.php">Profile</a>
                            </li>
                            <li>
                                <a href="/<?= $site_root?>/student/editprofile.php">Edit profile</a>
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/result/resultstatement.php">Statement of Result</a>                     
                            </li>
                        <?php endif; ?>

                        <?php if (getAccess() == 11) : ?>
                            <li>
                                <a href="/<?= $site_root?>/admission/progress.php">Application Form</a>
                            </li>
                            <li>
                                <a href="/<?= $site_root?>/admission/payhistory.php">Print Receipts</a>
                            </li>
                        <?php endif; ?>

                        <?php if (in_array(getAccess(), [1,20,2,3,4,5,6,7,8,9])) : ?>
                            <li>
                                <a href="/<?= $site_root;?>/staff/profile.php">Profile Info</a>                
                            </li>
                            <li>
                                <a href="/<?= $site_root;?>/staff/editprofile.php?lid=<?php echo getSessionValue('uid');?>">Edit profile</a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="/<?= $site_root?>/change_password.php">Change Password</a>
                        </li>
                        <li>
                            <a href="/<?= $site_root?>/logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            <?php else : ?>

                <div class="dropdown" style="margin-top: 2px;">
                    <a href="/<?= $site_root?>/login.php"><i class="icon-lock"></i>  Login</a>               
                </div>

            <?php endif; ?>
            
        
</div>
    </div>
</div>
                         
