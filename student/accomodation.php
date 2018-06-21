<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$page_title = "Tasued";
?>
<!doctype html>
<html>
    <?php include INCPATH . "/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>                                                                                                               
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-list"></i> Campus Accomodation Information form</h3>
                                </div>                                
                                <div class="box-content nopadding">
                                    <p style="color: red; font-size: 18px; text-align: center; margin-bottom: 5px;">Note: All students are obliged to provide the University with accurate information of their campus address.</p>
                                    <form action="#" method="POST" class='form-horizontal form-column form-bordered'>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Landlord/Caretaker Details</label>
                                            <div class="controls controls-row">
                                                <input style="margin-right: 6%;" type="text" name="textfield" id="textfield" placeholder="Name" class="input-xlarge">
                                                <input type="text" name="textfield" id="textfield" placeholder="Mobile" class="input-xlarge mask_phone">                                                
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Building Information</label>
                                            <div class="controls controls-row">
                                                <input style="margin-right: 6%;" type="text" name="textfield" id="textfield" placeholder="Building Name" class="input-xlarge">                                                
                                                <select name="select" id="select" class='input-large'>
                                                    <option value="1">Building Type</option>
                                                    <option value="2">Storey</option>
                                                    <option value="3">Bongalour</option>
                                                    <option value="4">Self Contained</option>
                                                    <option value="5">Face-and-face-you</option>
                                                </select>                                                
                                            </div>
                                        </div>

                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Amount Paid / Mode</label>
                                            <div class="controls controls-row">                                                
                                                    <div style="margin-right: 6%;" class="input-append input-prepend">
                                                        <span class="add-on">#</span>
                                                        <input type="text" placeholder="XX" class='input-large'>
                                                        <span class="add-on">.00</span>
                                                    </div>                                                                      
                                                <select name="select" id="select" class='input-large'>
                                                    <option value="1">Mode</option>
                                                    <option value="2">Per Session</option>
                                                    <option value="3">Annual</option>                                                    
                                                </select>     
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="control-group">
                                                <label for="textfield" class="control-label">Hostel Type <small>(Gender)</small></label>
                                                <div class="controls">
                                                    <select name="select" id="select" class='input-large'>
                                                        <option value="1">Male Only</option>
                                                        <option value="2">Female only</option>
                                                        <option value="3">Mixed</option>
                                                    </select>     
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label for="password" class="control-label">No. of Room</label>
                                                <div class="controls">
                                                    <select name="select" id="select" class='input-large'>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="3">4</option>
                                                        <option value="3">5</option>
                                                    </select> 
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label">Location</label>
                                                <div class="controls">
                                                    <select name="select" id="select" class='input-large'>
                                                        <option value="1">Ijele</option>
                                                        <option value="2">Abampawa</option>
                                                        <option value="3">Imaweje</option>
                                                    </select> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="control-group">
                                                <label class="control-label">Faculty<small>Available for use</small></label>
                                                <div class="controls">
                                                    <div class="span6">
                                                        <label class='checkbox'>
                                                            <input type="checkbox" name="checkbox"> Toilet
                                                        </label>
                                                        <label class='checkbox'>
                                                            <input type="checkbox" name="checkbox"> Electricity
                                                        </label>
                                                        <label class='checkbox'>
                                                            <input type="checkbox" name="checkbox"> Water
                                                        </label>
                                                        <label class='checkbox'>
                                                            <input type="checkbox" name="checkbox"> Modern Building
                                                        </label>                                                        
                                                    </div>
                                                    <div class="span6">
                                                        <label class='checkbox'>
                                                            <input type="checkbox" name="checkbox"> Furnished
                                                        </label>
                                                        <label class='checkbox'>
                                                            <input type="checkbox" name="checkbox"> Fenced
                                                        </label>                                                            
                                                        <label class='checkbox'>
                                                            <br>
                                                        </label>
                                                        <label class='checkbox'>
                                                            <br>
                                                        </label> 
                                                        
                                                        <label class='checkbox'>
                                                            <br>
                                                        </label>
                                                        <label class='checkbox'>
                                                            <br>
                                                        </label> 
                                                    </div>  
                                                </div>
                                            </div>                                            
                                        </div>
                                        <div class="span12">
                                            <div class="form-actions" style="text-align: center">
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                                <button type="button" class="btn">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH . "/footer.php" ?>
    </body>
</html>
<?php ?>
