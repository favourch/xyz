<?php
/**
 * TAMS
 * Footer Template
 * 
 * @category   Views
 * @package    Template
 * @subpackage Footer
 * @author     Adedayo Sule-odu <suleodu.adedayo@gmail.com>
 * @copyright  Copyright © 2014 TAMS.
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */
?>
<div id="footer" class="navbar-fixed-bottom" >
    <p> &copy; 2012 - <?php echo date('Y'); ?> TAMS. All Rights Reserved. 
        <a href="#privacy_modal" data-toggle="modal">Privacy Statement</a> |  
        <a href="#policy_modal" data-toggle="modal">Policy</a> |  
        <a href="#contact_modal" data-toggle="modal">Contact</a></p>
</div>
<div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="policy_modal">
    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">REFUND/CLAIMS PROCEDURE</h3>
    </div>
    <div class="modal-body" style="min-height: 300px">
        <p>
            The following situations are those that the University is required to make a refund or attend 
            to claims regarding payments made on the portal:
        </p>
        <ul>
            <li>Payment was initiated and completed on the university portal - my.tasued.edu.ng.</li>
            <li>Deduction was made on payer&apos;s account but does not reflect on the University&apos;s portal.</li>
            <li>More than one deduction was made on the payer&apos;s account for the same payment schedule transaction.</li>
        </ul>
        <p>Please follow the steps listed below to make a claim or request a refund as the case may appl:</p>
        <ol>
            <li>Write a letter to the University Bursar through your department.</li>
            <li>Copy the ICT unit</li>
            <li>Indicate the ATM Card details (first-six and last four digits), date of transaction, unique transaction reference number, matric number, course of study and level.</li>
        </ol>
    </div>
    <div class="modal-footer">
        <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
    </div>
</div>
<div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="privacy_modal">
    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">PRIVACY STATEMENT</h3>
    </div>
    <div class="modal-body" style="min-height: 300px">
        <p>
            Payments that you make through the Site will be processed by our payment agents - Unified Payment Services Limited. 

        </p>
        <p>We do not use your transaction information stored during any particular transaction beyond 
            the purview of that transaction and to process claims or make refunds as the case may apply.</p>
        <p>If you are concerned about your data you have the right to request access to the personal data which we may hold 
            or process about you. You have the right to require us to correct any inaccuracies in your data free of charge. 
        </p>
    </div>
    <div class="modal-footer">
        <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
    </div>
</div>
<div aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="contact_modal">
    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">CONTACT DETAILS</h3>
    </div>
    <div class="modal-body" style="min-height: 300px">
        <p>
            You may contact the institution for help on any issue regarding the use of this portal 
            for payment purposes and otherwise via the following media: 
        </p>
        <p></p>
        <ul>
            <p>
            <i class="icon-envelope"></i> info@tasued.edu.ng
            </p>
            <p>
                <i class="icon-globe"></i> <a href="<?php echo $school_helpdesk?>"><?php echo $school_helpdesk?></a>
            </p>
        </ul>
        
        
            NOTE: All enquiries made through the media listed above would be responded to not later than 48 hours from time of receipt. <br/><br/><br/>
        </p>
        <p>
            For further enquiry regarding the TAMS Portal, contact the lead developer/consultant, Ademola Adenubi:
            <ul>
                <p>
                    <i class="icon-envelope"></i>   crownbirth@gmail.com
                </p>
                <p>
                    <i class="icon-phone"></i>   +234 (0) 805 868 4616
                </p>
            </ul>
        </p> 
    </div>
    <div class="modal-footer">
        <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
    </div>
</div>