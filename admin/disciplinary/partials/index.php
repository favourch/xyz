<div aria-hidden="false" 
     aria-labelledby="myModalLabel" 
     role="dialog" tabindex="-1" 
     class="modal hide fade" 
     id="add_culprit">

    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">Add New Disciplinary Action</h3>
    </div>
    <form class="form-vertical" method="post" action="index.php" >
        <div class="modal-body" style="min-height: 300px">
            <div class="control-group">
                <label class="control-label" for="st_search">Search Student</label>
                <div class="controls">
                    <div class="input-append input-prepend">
                        <span class="add-on"><i class="icon-search"></i></span>
                        <input type="text" name="seed" ng-model="seed" class="input-block-level" placeholder="Matric here...">
                        <button type="button" class="btn" ng-click="getStudent(seed)">Search!</button>
                    </div>
                </div>
            </div> 
            <div ng-show="loading" class="center">
                <img src="giphy.gif" width="60px" height="60px">
            </div>
            <div class="alert alert-danger" ng-show="student && student.status == 'error'">
                {{student.msg}}
            </div>
            <div ng-show="student && student.status == 'success'" >
                <div class=" well well-large" ng-show="student">
                    <div ng-show="student.status == 'success'">
                        <b>Matric Number : </b> {{student.rs.stdid}} <br/>
                        <b>Full Name : </b> {{student.rs.fname}} {{student.rs.lname}} {{student.rs.mname}}<br/>
                        <b>Present Level : </b> {{student.rs.level}}00L                                                                           <br/>
                        <b>College : </b> {{student.rs.colname}}<br/>
                        <b>Department : </b> {{student.rs.deptname}}<br/>
                        <b>Programme : </b> {{student.rs.progname}}<br/>
                        <input type="hidden" name="post_stdid" value="{{student.rs.stdid}}">
                    </div>
                </div> 
                <div class="row-fluid">
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="de">Status</label>
                            <div class="controls" class="input-block-level">
                                <select name="status" ng-model="status"  required="">
                                    <option value="suspended">Suspended</option>
                                    <option value="withdrawn">Withdrawn</option>
                                    <option value="leave">Leave of Absence</option>
                                    <option value="rustication">Rustication</option>
                                    <option value="expulsion">Expulsion</option>
                                    <option value="probation">Probation</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="de">Senate Sitting No.</label>
                            <div class="controls" class="input-block-level">
                                <input type="number" name="ssn" class="input input-medium" required="">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row-fluid">
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="pub_volume">Start Session / Semester</label>
                            <div class="controls" >
                                <select class="input-medium" name="start_ses" ng-model="start_ses" required="">
                                    <option ng-repeat="ses in session" value="{{ses.sesid}}">{{ses.sesname}}</option>
                                </select>
                                <select class="input-medium" name="start_sem" ng-model="start_sem" required="">
                                    <option  value="first">First</option>
                                    <option  value="second">Second</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="pub_volume">End Session / Semester </label>
                            <div class="controls" >
                                <select class="input-medium" name="end_ses" ng-model="end_ses" required="">
                                    <option ng-repeat="ses in session" value="{{ses.sesid}}">{{ses.sesname}}</option>
                                </select>
                                <select class="input-medium" name="end_sem" ng-model="end_sem" required="">
                                    <option  value="first">First</option>
                                    <option  value="second">Second</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="abstract_des">Terms</label>
                    <div class="controls">
                        <textarea class="input-block-level" ng-model="terms" required="" rows="5" id="abstract_des" name="terms"></textarea>
                    </div>
                </div>
                <input type="hidden" name="MM_Submit" value="add_culprit">
            </div>
            
        </div>
        <div class="modal-footer" ng-show="student && student.status == 'success'">
            <button class="btn btn-primary" type="submit" >Submit</button>
            <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
        </div>
    </form>
</div>


<!--Edit culprit-->
<div aria-hidden="false" 
     aria-labelledby="myModalLabel" 
     role="dialog" tabindex="-1" 
     class="modal hide fade" 
     id="edit_culprit">

    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">Update Disciplinary Action</h3>
    </div>
    <form class="form-vertical" method="post" action="index.php" >
        
        <div class="modal-body" style="min-height: 300px">
            
            <div >
                <div class=" well well-large" ng-show="selecetedItem">
                    
                    <div>
                        <b>Matric Number : </b> {{selecetedItem.stdid}} <br/>
                        <b>Full Name : </b> {{selecetedItem.fname}} {{selecetedItem.lname}} {{selecetedItem.mname}}<br/>
                        <input type="hidden" name="post_stdid" value="{{selecetedItem.stdid}}">
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="de">Status {{selecetedItem.status}}</label>
                            <div class="controls" class="input-block-level">
                                <select name="status" ng-model="status" required="">
                                    <option value="suspended" ng-selected="'Suspended' == selecetedItem.status">Suspended</option>
                                    <option value="withdrawn" ng-selected="'Withdrawn' == selecetedItem.status">Withdrawn</option>
                                    <option value="leave" ng-selected="'leave' == selecetedItem.status" >Leave of Absence</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="de">Senate Sitting No.</label>
                            <div class="controls" class="input-block-level">
                                <input type="number" name="ssn" class="input input-medium" required="">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row-fluid">
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="pub_volume">Start Session / Semester</label>
                            <div class="controls" >
                                <select class="input-medium" name="start_ses" ng-model="start_ses" required="">
                                    <option ng-repeat="ses in session" value="{{ses.sesid}}" ng-selected="ses.sesid == selecetedItem.start_sesid">{{ses.sesname}}</option>
                                </select>
                                <select class="input-medium" name="start_sem" ng-model="start_sem" required="">
                                    <option  value="first" ng-selected="'first' == selecetedItem.start_sem">First</option>
                                    <option  value="second" ng-selected="'second' == selecetedItem.start_sem">Second</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="span6">
                        <div class="control-group">
                            <label class="control-label" for="pub_volume">End Session / Semester </label>
                            <div class="controls" >
                                <select class="input-medium" name="end_ses" ng-model="end_ses" required="">
                                    <option ng-repeat="ses in session" value="{{ses.sesid}}" ng-selected="ses.sesid == selecetedItem.end_sesid">{{ses.sesname}}</option>
                                </select>
                                <select class="input-medium" name="end_sem" ng-model="end_sem" required="">
                                    <option  value="first" ng-selected="'first' == selecetedItem.end_sem">First</option>
                                    <option  value="second" ng-selected="'second' == selecetedItem.end_sem">Second</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" >Terms</label>
                    <div class="controls">
                        <textarea name="terms2" class="input-block-level"  required="" rows="5">{{selecetedItem.terms}} </textarea>
                    </div>
                </div>
                <input type="hidden" name="MM_Submit" value="edit_culprit">
                <input type="hidden" name="edit_id" value="{{selecetedItem.disid}}">
                <input type="hidden" name="prev_record" value="{{selecetedItem}}">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" type="submit" >Update</button>
            <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
        </div>
    </form>
</div>



<!--Release culprit-->
<div aria-hidden="false" 
     aria-labelledby="myModalLabel" 
     role="dialog" tabindex="-1" 
     class="modal hide fade" 
     id="release_culprit">

    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h3 id="myModalLabel">Release {{selecetedItem.stdid}}</h3>
    </div>
    <form class="form-vertical" method="post" action="index.php" >
        
        <div class="modal-body" style="min-height: 300px">
            <div class="well well-small">
                <b>Matric Number : </b> {{selecetedItem.stdid}} <br/>
                <b>Full Name : </b> {{selecetedItem.fname}} {{selecetedItem.lname}} {{selecetedItem.mname}}<br/>
                <input type="hidden" name="stdid" value="{{selecetedItem.stdid}}">
            </div>
            <div class="control-group">
                <label class="control-label" for="de">Get Session to Pay For</label>
                <div class="controls" class="input-block-level">
                    <button type="button" ng-click="getToPaySession(selecetedItem.start_sesid, selecetedItem.end_sesid)">Get Session</button>
                </div>
            </div>
            <div ng-show="loading" class="center">
                <img src="giphy.gif" width="60px" height="60px">
            </div>
            <div class="alert alert-danger" ng-show="sessions.status == 'error'" >
                {{sessions.msg}}
            </div>
            <div class='well well-large' ng-show="sessions.status == 'success'">
                <h4>Select session to pay for</h4>
                <table class="table table-bordered table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Session Name</th>
                            <th>Selection</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="session in sessions.rs">
                            <td>{{$index + 1}}</td>
                            <td>{{session.sesname}}</td>
                            <td>
                                <input type="checkbox" value="{{session.sesid}}" name="sessions[]">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="control-group">
                    <label class="control-label" for="de">Senate Sitting No.</label>
                    <div class="controls" class="input-block-level">
                        <input type="number" name="ssn" class="input input-medium" required="">
                    </div>
                </div>
            </div>
            
        </div>
        <input type="hidden" name="MM_Submit" value="release_culprit">
        <input type="hidden" name="edit_id" value="{{selecetedItem.disid}}">
        <input type="hidden" name="level" value="{{selecetedItem.level}}">
        <div class="modal-footer" ng-show="sessions.status == 'success'">
            <button class="btn btn-primary" type="submit" >Release</button>
            <button aria-hidden="true" data-dismiss="modal" class="btn">Close</button>
        </div>
    </form>
</div>