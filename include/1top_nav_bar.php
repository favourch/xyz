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
                    <?php if (in_array(getAccess(), [1, 2, 3, 4, 5, 6, 20, 21, 22, 23])) : ?>
                        <li>
                            <a href="/<?= $site_root ?>/student/index.php">Student</a>
                        </li>
                        <li>
                            <a href="/<?= $site_root ?>/staff/index.php">Staff</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
            
            <?php if (in_array(getAccess(), [1])) : ?>
                <li>
                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                        <span>Admin menu</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
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
                </li>
            <?php endif; ?>
                
                
             <?php if (in_array(getAccess(), [1,2,3,4,5,6,10])) : ?>
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
                    <a href="/<?= $site_root ?>/olevel_verifier/index.php">O'level Verification</a>
                </li>
                    </ul>
                </li>
            <?php endif; ?>
                    
                    
            <?php if(in_array(getAccess(), [1,20,21,22,23,24])) :?>
                <li>
                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                        <span>Admission</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                         <?php if (in_array(getAccess(), [1, 20, 24])) : ?>
                <li>
                    <a href="/<?= $site_root ?>/admin/admission">Admission Overview</a>                
                </li>
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
                    <a href="/<?= $site_root ?>/admin/olevel_verifier_mgt/index.php">O'Level Management</a>
                </li>
                <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>    
             
                
            <?php if(in_array(getAccess(), [1,2,3,10])) :?>
                <li>
                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                        <span>Registration</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if(in_array(getAccess(), [10])) :?>
                        <li>
                            <a href="/<?= $site_root ?>/registration/registercourse.php">Register Courses</a>                     
                        </li>
                        
                        <li>
                            <a href="/<?= $site_root?>/registration/course_reg_form.php?stid=<?= getSessionValue('uid')?>">View Course Form</a>
                        </li>
                        <?php if(isset($_SESSION) && getSessionValue('epass') == 'TRUE') :?>
                        <li>
                            <a target="_blank" href="/<?= $site_root?>/registration/exampass.php">Exam Pass</a>
                        </li>
                        <?php endif;?>
                      <?php endif;?>
                        <?php if(in_array(getAccess(), [2,3])) :?>
                           <li>
                                <a href="/<?= $site_root ?>/course_admin/course_status.php">Course Status</a>
                           </li>

                           <li>
                                <a href="/<?= $site_root ?>/course_admin/adviser.php">Staff Adviser</a>
                           </li>

                           <li>
                                <a href="/<?= $site_root ?>/course_admin/index.php">Course Allocation</a>
                           </li>
                        <?php endif;?>
                    </ul>
                </li>
            <?php endif; ?>
                
                
            <?php if(in_array(getAccess(), [1,2,3,10,20])) :?>
                <li>
                    <a href="#" data-toggle="dropdown" class='dropdown-toggle'>
                        <span>Payments</span>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (in_array(getAccess(), [1,20])) : ?>
                            <li>
                                <a href="/<?= $site_root ?>/admin/payment/index.php">Payment Management</a>                
                            </li>
                        <?php endif; ?>
                        <?php if (in_array(getAccess(), [1,2,3])) : ?> 
                            <li>
                                <a href="/<?= $site_root ?>/payments/paylist.php">Payment List</a>                
                            </li>
                        <?php endif; ?>   
                        <?php if (in_array(getAccess(), [10])) : ?>     
                            <li>
                                <a href="/<?= $site_root ?>/payments/index.php">Pay School Fee</a>                
                            </li>
                            <li>
                                <a href="/<?= $site_root ?>/payments/pay_history.php">Pay History</a>                
                            </li>
                        <?php endif; ?>  
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
                                <a href="appform.php">Application Form</a>
                            </li>
                            <li>
                                <a href="payhistory.php">Print Receipts</a>
                            </li>
                        <?php endif; ?>

                        <?php if (in_array(getAccess(), [1,2,3,4,5,6,7,8,9])) : ?>
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