<?php





if (!isset($_SESSION)) {

    session_start();

}



require_once('../path.php');







$auth_users = "11";

check_auth($auth_users, $site_root);



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

                                    <h3><i class="icon-reorder"></i>

                                        <?php echo $_SESSION['admname']; ?> Prospective Application Instructions 

                                    </h3>

                                </div>

                                <div class="box-content">                                   

                                    <div class="row-fluid">

                                     <p class="p1"><span class="s1">This is to inform the general public that the POST-UTME screening exercise of Tai Solarin University of Education (TASUED) for the 2017/2018 session will take place as indicated below:</span></p>
                                        <p style="text-align: center;"><button type="button"><a href="https://my.tasued.edu.ng/admission" target="_blank">APPLY NOW</a></button></p>
                                        <p class="p3"><span class="s1"><strong>1. </strong></span><span class="s2"><strong>THOSE WHO MAY APPLY</strong></span></p>
                                        <p class="p4"><span class="s1">i. UTME Candidates who chose TASUED as first choice institution or seeking a change of institution to TASUED through JAMB, having scored a minimum of <strong>180 </strong>in the 2017 UTME.</span></p>
                                        <p class="p4"><span class="s1">ii. Direct Entry candidates with A level/OND/HND/NCE/JUPEB qualification who chose TASUED as first choice institution or seeking a change of institution to TASUED through JAMB and have obtained 2017 Direct Entry JAMB form.</span></p>
                                        <p class="p3"><span class="s1"><strong>2. </strong></span><span class="s2"><strong>METHOD OF APPLICATION (LOG IN PROCEDURE)</strong>&nbsp;</span></p>
                                        <p class="p4"><span class="s1">i. Candidates should apply online through the TASUED Admission portal on <a href="https://my.tasued.edu.ng/admission"><span class="s3">https://my.tasued.edu.ng/admission</span></a></span></p>
                                        <p class="p6"><span class="s1"><strong>NB: The www.tasued.edu.ng is the ONLY legitimate and authentic website of TASUED through which the Admission portal can be accessed. </strong></span></p>
                                        <p class="p4"><span class="s1">ii. Each candidate is required to make the payment of N2,500 for the screening exercise and N5,000 for the portal access fee, payable online with either Master card or VISA ATM card.</span></p>
                                        <p class="p6"><span class="s1"><strong>(Please print out your receipt after payment)</strong></span></p>
                                        <p class="p4"><span class="s1">iii. Candidates are required to fill and complete the online Application Form by providing all the required information.</span></p>
                                        <p class="p6"><span class="s1"><strong>(Please download and read the University Degree Brochure and carefully study the O&rsquo;Level requirements for the courses applied for, before filling the online Application Form)</strong></span></p>
                                        <p class="p4"><span class="s1">iv. Print the Screening Slip containing your colour passport photograph and screening information. The printed slip will serve as candidate&rsquo;s identification/admission card for the screening exercise.</span></p>
                                        <p class="p4"><span class="s1">v. The sale of the online Screening Form/Registration commences on <strong>Monday, 17</strong></span><span class="s4"><strong><sup>th</sup></strong></span><span class="s1"><strong> July 2017 </strong>and closes on <strong>Friday, 11</strong></span><span class="s4"><strong><sup>th</sup></strong></span><span class="s1"><strong> August 2017.</strong></span></p>
                                        <p class="p8">&nbsp;</p>
                                        <p class="p6"><span class="s1">Note that wrong/false information provided by any candidate shall render the application</span> <span class="s1">invalid and such candidates shall be disqualified.</span></p>
                                        <p class="p3"><span class="s1"><strong>3. </strong></span><span class="s2"><strong>SCREENING DATES</strong></span></p>
                                        <p class="p10"><span class="s1">Screening exercise shall be conducted for all UTME candidates at the Main Campus of the University on these dates<strong>:</strong></span></p>
                                        <p class="p11"><span class="s1">i. College of Science &amp; Information Technology</span><span class="s6"> Monday, 21</span><span class="s4"><sup>st</sup></span><span class="s6"> August 2017</span></p>
                                        <p class="p11"><span class="s1">ii. College of Specialised and Professional Education</span><span class="s6"> Tuesday, 22</span><span class="s4"><sup>nd</sup></span><span class="s6"> August 2017</span></p>
                                        <p class="p12"><span class="s7">iii. College of Humanities</span><span class="s1"> Wednesday, 23</span><span class="s4"><sup>rd</sup></span><span class="s1"> August 2017</span></p>
                                        <p class="p11"><span class="s1">iv. College of Social and Management Sciences</span><span class="s6"> Thursday, 24</span><span class="s4"><sup>th</sup></span><span class="s6"> August 2017</span></p>
                                        <p class="p11"><span class="s1">v. College of Vocational and Technology Education</span><span class="s6"> Friday, 25</span><span class="s4"><sup>th</sup></span><span class="s6"> August 2017</span></p>
                                        <p class="p13">&nbsp;</p>
                                        <p class="p14"><span class="s1">The screening date for Direct Entry (200 Level) applicants shall be announced at a later date.</span></p>
                                        <p class="p14"><span class="s1"><strong>Candidates&rsquo; participation in the screening exercise is a mandatory requirement for entry into Tai Solarin University of Education.</strong></span></p>
                                        <p class="p3"><span class="s1"><strong>4. SCREENING DETAILS</strong></span></p>
                                        <p class="p10"><span class="s1">Candidates should prepare for the screening exercise and bring along the following:</span></p>
                                        <p class="p16"><span class="s1">Colour print-out of the online Application Form </span></p>
                                        <p class="p17"><span class="s1">(i) Original JAMB Examination Notification of Result slip or Direct Entry Registration Slip.</span></p>
                                        <p class="p14"><span class="s1">(iii) Screening Slip and Receipt</span></p>
                                        <p class="p3"><span class="s1"><strong>5. WARNING:</strong></span></p>
                                        <p class="p17"><span class="s1">(i) Cell phones (GSM, CDMA, etc.) and other electronic devices are not allowed at the venue of the screening exercise </span></p>
                                        <p class="p17"><span class="s1">(ii) Candidates are to arrive at the screening venue an hour before the commencement of the exercise.</span></p>
                                        <p class="p19"><span class="s1"><strong>The results of the screening exercise should be checked on the Admission portal after the screening exercise. </strong></span></p>
                                        <p class="p8">&nbsp;</p>
                                        <p class="p20"><span class="s1">All correspondence on the screening exercise should be directed to <strong>admissions@tasued.edu.ng</strong> or addressed to the Admissions Officer at the University Main Campus, Ijagun, P.M.B. 2118, Ijebu-Ode, Ogun State. </span></p>
                                        <p class="p21"><strong>Telephone Nos: 08037195133, 08053026808</strong></p>
                                        <p class="p24">&nbsp;</p>
                                        <p class="p25"><span class="s1"><strong>Signed</strong></span></p>
                                        <p class="p25">FRANCIS O. ODUTUGA, MNIM, ACIPM</p>
                                        <p class="p25"><span class="s1"><strong>Ag.Registrar</strong></span>
                                    </p>
                                     
                                    </div>
                                    <form method="post" action="form.php">
                                        <div>
                                            <p>I have read, Understand and agree to the terms of this Admission  <input type="checkbox"  required=""></p>
                                            <a href="progress.php"></a><button type="submit" class="btn btn-primary">Proceed</button></a>
                                        </div>
                                    </form>
                                    
                                </div>

                            </div>

                        </div>

                    </div>

                </div>          

            </div>

            <?php include INCPATH."/footer.php" ?>

    </body>

</html>