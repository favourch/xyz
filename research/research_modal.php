<!--Submit research--> 
<div aria-hidden="false" 
     aria-labelledby="myModalLabel" 
     role="dialog" tabindex="-1" 
     class="modal hide fade" 
     id="add_reseach">

    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">Upload Your Published Research</h3>
    </div>
    <form class="form-vertical" method="post" action="profile.php" enctype="application/x-www-form-urlencoded">
        <div class="modal-body" style="min-height: 300px">
            <div class="control-group">
                <label class="control-label" for="r_title">Research Title</label>
                <div class="controls">
                    <input type="text" class="input-block-level"  id="r_title" name="r_title" required="" >
                </div>
            </div> 
            <div class="row-fluid">
                <div class="span3">
                    <div class="control-group">
                        <label class="control-label" for="pub_year">Publication Year</label>
                        <div class="controls">
                            <input type="date" class="input-small" required="" id="pub_year" name="pub_year" >
                        </div>
                    </div>
                </div>
                <div class="span3">
                    <div class="control-group">
                        <label class="control-label" for="r_area">Research Area</label>
                        <div class="controls" >
                            <select name="r_area"  id="r_area" class="input-small" required="" >
                                <option value="">--Choose--</option>
                                <option ng-repeat="res in research_area" value="{{res.id}}">{{res.area}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="span3">
                    <div class="control-group">
                        <label class="control-label" for="pub_type">Publication Type</label>
                        <div class="controls" >
                            <select id="pub_type" required="" name="pub_type" class="input-small" >
                                <option value="">--Choose--</option>
                                <option ng-repeat="pub in pub_type" value="{{pub.id}}">{{pub.pub_type}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="auth_name">Enter Author's  Name <small style="color: brown">Format : (Surname Intials .Initials e.g Adenibi A.O)</small></label>
                <div class="controls">
                    <input type="text" required="" name="auth_name" id="auth_name" class="tagsinput" value="">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="pub_at">Published At</label>
                <div class="controls">
                    <input type="text" class="input-block-level"  id="pub_at" name="pub_at" required="" >
                </div>
            </div> 
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="pub_volume">Volume </label>
                        <div class="controls" >
                            <input type="text" id="pub_volume" class="input-medium" name="volume">
                        </div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="page_num">Page No. <small style="color: brown">Format : (100 - 106)</small></label>
                        <div class="controls" >
                            <input type="text" id="page_num" class="input-medium" name="page_num">
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="abstract_des">Abstract</label>
                <div class="controls">
                    <textarea class="input-block-level" required="" rows="5" id="abstract_des" name="r_abstract"></textarea>
                </div>
            </div>
            <input type="hidden" name="lectid" value="<?= getSessionValue('uid') ?>">
            <input type="hidden" name="MM_Submit" value="form1">
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit">Submit</button>
            <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
        </div>
    </form>
</div>

<!--Edit Modal-->
<div aria-hidden="false" 
     aria-labelledby="myModalLabel" 
     role="dialog" tabindex="-1" 
     class="modal hide fade" 
     id="edit_research">

    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">Edit Research</h3>
    </div>
    <form class="form-vertical" method="POST" action="profile.php" enctype="application/x-www-form-urlencoded">
        <div class="modal-body" style="min-height: 350px">
            <div class="control-group">
                <label class="control-label" for="textfield">Research Title</label>
                <div class="controls">
                    <input type="text" class="input-block-level"  id="textfield" name="r_title" value="{{selectedItem.res_title}}" >
                </div>
            </div>
            <div class="row-fluid">
                <div class="span4">
                    <div class="control-group">
                        <label class="control-label" for="pub_year">Publication Year</label>
                        <div class="controls">
                            <input type="date" class="input-small"  id="pub_year" name="pub_year" value="{{selectedItem.pub_year}}">
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <div class="control-group">
                        <label class="control-label" for="r_area">Research Area</label>
                        <div class="controls">
                            <select name="r_area"  id="r_area" class="input-medium" required="" >
                                <option value="">--Choose--</option>
                                <option ng-repeat="res in research_area" value="{{res.id}}" ng-selected="selectedItem.res_area_id == res.id">{{res.area}}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <div class="control-group">
                        <label class="control-label" for="pub_type">Publication Type</label>
                        <div class="controls" >
                            <select id="pub_type" required="" name="pub_type" class="input-medium" >
                                <option value="">--Choose--</option>
                                <option ng-repeat="pub in pub_type" value="{{pub.id}}"  ng-selected="selectedItem.pub_type_id == pub.id">{{pub.pub_type}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="reference">Enter Author's  Name <small style="color: brown">Format : (Surname Intials .Initials e.g Adenibi A.O)</small></label>
                <div class="controls">
                    <input type="text" name="reference" class="tagsinput input-block-level"  id="reference2" value="{{selectedItem.reference}}">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="pub_at">Published At</label>
                <div class="controls">
                    <input type="text" class="input-block-level"  id="pub_at" name="pub_at" required="" value="{{selectedItem.pub_at}}" >
                </div>
            </div> 
            <div class="row-fluid">
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="pub_volume">Volume </label>
                        <div class="controls" >
                            <input type="text" id="pub_volume" class="input-medium" name="volume" value="{{selectedItem.volume}}">
                        </div>
                    </div>
                </div>
                <div class="span6">
                    <div class="control-group">
                        <label class="control-label" for="page_num">Page No. <small style="color: brown">Format : (100 - 106)</small></label>
                        <div class="controls" >
                            <input type="text" id="page_num" class="input-medium" name="page_num" value="{{selectedItem.page_num}}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="textarea">Abstract</label>
                <div class="controls">
                    <textarea  class="input-block-level" rows="5" id="textarea" name="r_abstract">{{selectedItem.res_abstract}}</textarea>
                </div>
            </div>
            <input type="hidden" name="lectid" value="<?= getSessionValue('uid') ?>">
            <input type="hidden" name="res_id" value="{{selectedItem.res_id}}">
            <input type="hidden" name="MM_Update" value="form2">
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit">Save changes</button>
            <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
        </div>
    </form>
</div>

<!--View Abstract Modal-->
<div aria-hidden="false" 
     aria-labelledby="myModalLabel" 
     role="dialog" tabindex="-1" 
     class="modal hide fade" 
     id="abstract">

    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h4 id="myModalLabel">{{selectedItem.res_title}}</h4>
    </div>
    <div class="modal-body" style="min-height: 300px">
        {{selectedItem.res_abstract}}
    </div>
    <div class="modal-footer">
        <a ng-show="selectedItem.upload == 'yes'" class="btn btn-primary" target="_blank"  href="../research/papers/{{selectedItem.res_id}}.pdf">Download Full Paper</a>
        <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
    </div>
</div>

<!--Upload Modal-->
<div aria-hidden="false" 
     aria-labelledby="myModalLabel" 
     role="dialog" tabindex="-1" 
     class="modal hide fade" 
     id="upload">

    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h4 id="myModalLabel">Upload Paper to {{selectedItem.res_title}}</h4>
    </div>
    <div class="modal-body" style="min-height: 300px">
        <form class="form-vertical" method="post" action="profile.php" enctype="multipart/form-data">
            <div class="modal-body" style="min-height: 300px">
                <div class="control-group">
                    <label class="control-label" for="filename">Full Paper <small>Paper Format : (PDF)</small></label>
                    <div class="controls">
                        <div data-provides="fileupload" class="fileupload fileupload-new">
                            <input type="hidden">
                            <div class="input-append">
                                <div class="uneditable-input span3">
                                    <i class="icon-file fileupload-exists"></i> 
                                    <span class="fileupload-preview"></span>
                                </div>
                                <span class="btn btn-file">
                                    <span class="fileupload-new">Select file</span>
                                    <span class="fileupload-exists">Change</span>
                                    <input type="file" name="filename">
                                </span>
                                <a data-dismiss="fileupload" class="btn fileupload-exists" href="#">Remove</a>
                            </div>
                        </div>
                    </div>
                </div>     
                <input type="hidden" name="lectid" value="<?= getSessionValue('uid') ?>">
                <input type="hidden" name="res_id" value="{{selectedItem.res_id}}">
                <input type="hidden" name="MM_Upload" value="upload">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Submit</button>
                <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
            </div>
        </form>
    </div>
</div>