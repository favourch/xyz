<div class="page_header">
    <div class="pull-left">
        <img width="50" height="55" name="logo" src="/<?= $site_root?>/img/logo/school-logo.png" class="retina-ready" style="margin-top: 10px;" type="image" id="logo">
        <h2 style="font-family: Tahoma, Geneva, sans-serif; font-size: 30px; font-weight: lighter; margin-left: 20px; text-align: center;" class="pull-right"><?= $university ?></h2>
    </div>
    
    <div class="pull-right">
        <ul class="stats">
	<li class="red">
	
        <a href="<?= $school_helpdesk ?>" target="_blank" style="text-decoration: none;">
            <i class="glyphicon-headset" style="padding-top: 10px;"></i>
            <div class="details">
                <span class="big">Contact</span>
                <span>Helpdesk</span>
            </div>
        </a>
            </li>
            <li class='lightred'>
                <i class="icon-calendar"></i>
                <div class="details">
                    <span class="big">February 22, 2013</span>
                    <span>Wednesday, 13:56</span>
                </div>
            </li>
        </ul>
    </div>
    
</div>
<?php
$msg = $notification->get_notification();
if( $msg != "" && $msg['type'] !=''){ ?>
<div class=" 
     <?php if($msg['type'] == 'error'){
         echo ' alert alert-danger';
         
     }
     elseif($msg['type'] == 'success'){
         
         echo 'alert alert-success';    
     }else{
        echo 'alert'; 
     }?>
     ">
   <?= $msg['msg'] ?> 
</div>
<?php }?>