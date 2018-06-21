
<div class="page_header">

    <div class="pull-left">

        <img width="50" height="55" name="logo" src="/<?= $site_root?>/img/logo/school-logo.png" class="retina-ready" style="margin-top: 10px;" type="image" id="logo">

        <h2 style="font-family: Tahoma, Geneva, sans-serif; font-size: 30px; font-weight: lighter; margin-left: 20px; text-align: center;" class="pull-right"><?= $university ?></h2>

    </div>

    

    <div class="pull-right">

        

                   <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <script>
                      (adsbygoogle = window.adsbygoogle || []).push({
                        google_ad_client: "ca-pub-3373902981609251",
                        enable_page_level_ads: true
                      });
                    </script>

               

    </div>

    

</div>




<!--<div class="page_header">
    <div class="row-fluid">
        <div class="span6">
            <div class="pull-left">
                <img width="50" height="55" name="logo" src="/<?= $site_root ?>/img/logo/school-logo.png" class="retina-ready" style="margin-top: 10px;" type="image" id="logo">
                <h2 style="font-family: Tahoma, Geneva, sans-serif; font-size: 30px; font-weight: lighter; margin-left: 20px; text-align: center;" class="pull-right"><?= $university ?></h2>
            </div>
        </div>
        <div class="span6">
            <div class="">
                <div class="well">
                    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <script>
                      (adsbygoogle = window.adsbygoogle || []).push({
                        google_ad_client: "ca-pub-3373902981609251",
                        enable_page_level_ads: true
                      });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>-->


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