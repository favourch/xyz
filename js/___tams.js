// JavaScript Document
$( function(){
    
    rem = $('#rem');
    reg = $('#reg');
    var maxU = $('#max');
    var minU = $('#min');
    
    if( parseInt(reg.text()) >= parseInt(minU.text()) ){
        $('#submit').removeAttr('disabled');
    }
    
    $('input[type=checkbox]').change(function(e) {
        
        cur = $(this);
        cUnit = parseInt(cur.parent().prev('p').children('span').text());
		
       if( cur.is(':checked') ){
            if( parseInt(reg.text()) + cUnit > parseInt(maxU.text()) ){
               $(this).removeAttr('checked');
               alert("You cannot register the selected course. Maximum units exceeded!");
            }
            else{
                add();
                if( parseInt(reg.text()) >= parseInt(minU.text()) ){
                        $('#submit').removeAttr('disabled');
                }
            }

        }else{
            subtract();
            if( parseInt(reg.text()) < parseInt(minU.text()) ){
                $('#submit').attr('disabled','disabled');
            }
        }
    });
	
    function add(){
            reg.text( parseInt(reg.text()) + cUnit);
            rem.text( parseInt(rem.text()) - cUnit);
    }
    
    function subtract(){
            reg.text( parseInt(reg.text()) - cUnit);
            rem.text( parseInt(rem.text()) + cUnit);
    }
	
    $('#ctable tr:even').css('background-color','#CCC');
});

function attach(){
	
	var total = parseInt($('#total').text());
	var low = null;
	var high = 0;
	var pass = 0;
	var fail = 0;
	var scores = $('.totscore');
        
        var lowObj;
        var highObj;
    
    if(scores.size() > 0) {
        if(low == null) {
            low = 100;
        }
    }
    
    scores.each(function() {       
        
        var value = parseInt($(this).text());
        if( value <= failValue )
            fail++;
        else
            pass++;

        if( value < low ) {
            low = value;
            lowObj = this;
        }
        if( value > high ) {
            high = value;
            highObj = this;
        }
    });
	
    console.log(lowObj);
    low = ( low == null) ? 0: low;
    var highV = $('#high');
    var lowV = $('#low');
    var passV = $('#pass');
    var failV = $('#fail');
    var pPercent = Math.round((pass/total)*100);
    var fPercent = Math.round((fail/total)*100);
    pPercent = ( isNaN(pPercent) ) ? 0: pPercent;
    fPercent = ( isNaN(fPercent) ) ? 0: fPercent;

    highV.text( high );
    lowV.text( low );
    failV.text( fail + " ("+fPercent+"%)" );
    passV.text( pass + " ("+pPercent+"%)" );

    $('#editbutton').click(function(e) {

        if( $(this).val() == "Edit" ){
            $(this).val("Cancel");
            $('input[name=save]').removeAttr('disabled');
            $('.editdata').each(function() {

                //function for individual edit button
                $('<input type="button" value="Edit" class="etext">').click(function(e) {

                    var base = $(this).parent().parent();
                    if( $(this).val() == "Edit" ){
                        $(this).val("Cancel");					
                        var subbase = base.find('.tscore,.escore').children('span');
                        subbase.css('display','none');
                        subbase.next().css('display','inline').removeAttr('disabled').focus();

                        /*subbase = base.find('.escore').children('span');
                        subbase.css('display','none');
                        $('<input type="text" name="eedit[]" value="'+subbase.text()+'" size="1" maxlength="2"/>').appendTo(subbase.parent()).focus();*/

                        subbase = base.find('.matric').children('input');
                        subbase.removeAttr('disabled');

                    }else{
                        $(this).val("Edit");
                        base.find('.tscore,.escore').children('input[type=text]').css('display','none').attr('disabled','disabled');
                        base.find('.matric').children('input').attr('disabled','disabled');
                        base.find('.tscore,.escore').children('span').css('display','inline');
                    }									
                }).appendTo($(this));				
            });

        }else{
            $(this).val("Edit");
            $('.editdata').children('*').remove();
            $('input[name=save]').attr('disabled','disabled');			
        }
			
    });
}

function courseassign(){
    $('input.cbox').each(function() {
        var cur = $(this);
        if( !cur.is(':checked') ){
            cur.parent().parent().find('select').attr('disabled','disabled');
        }

        cur.change(function(e) {
            if( cur.is(':checked') ){
                cur.parent().siblings().find('select').removeAttr('disabled');
            }else{
                cur.parent().siblings().find('select').attr('disabled','disabled');			
            }
        });
    });
}

function appointment(){
	
    $('#admincheck,#centrecheck,.hodcheck,.deancheck').each(function() {
		
        var cur = $(this);		
        cur.change(function(e) {
            if( cur.is(':checked') ){
                cur.parent().siblings().find('select').removeAttr('disabled');
                cur.parent().next('input').removeAttr('disabled');
            }else{
                cur.parent().siblings().find('select').attr('disabled','disabled');	
                cur.parent().next('input').attr('disabled','disabled');
            }
            
        });
    });
}

function courseaallocate(){	
    $('input.cbox').each(function() {
		
        var cur = $(this);
        if( !cur.is(':checked') ){
            cur.parent().parent().find('select').attr('disabled','disabled');
        }
		
        cur.change(function(e) {

            if( cur.is(':checked') ){								
                cur.parent().siblings().find('select').removeAttr('disabled');			
                cur.parent().parent().find('input[name=dept[]]').removeAttr('disabled'); 
                cur.parent().parent().find('input[name=state[]]').attr('disabled','disabled'); 
                cur.parent().parent().find('input[name=upld[]]').removeAttr('disabled'); 
                cur.parent().parent().find('input[name=appr[]]').removeAttr('disabled'); 
            }else{
                cur.parent().siblings().find('select').attr('disabled','disabled');				
                cur.parent().parent().find('input[name=dept[]]').attr('disabled','disabled');
                cur.parent().parent().find('input[name=state[]]').removeAttr('disabled'); 
                cur.parent().parent().find('input[name=upld[]]').attr('disabled','disabled');
                cur.parent().parent().find('input[name=appr[]]').attr('disabled','disabled');
            }

        });
    });
}

function lvlFilter( elem ){
	doFilter("lvl", elem);
}

function colFilter( elem ){
	doFilter("col", elem);
}

function progFilter( elem ){
	doFilter("prog", elem);
}

function catFilter( elem, dpd ){
	if( dpd == "dept")
		doFilter("catd", elem);
	else if( dpd == "col")
		doFilter("catc", elem);
	else
		doFilter("cat", elem);
}

function deptFilter( elem ){
	doFilter("dept", elem);
}

function doFilter( action, elem ){
	var params = getQueryStringArgs();
	var selectedOption = elem.options[elem.selectedIndex];
	if( action == "dept"){
		location = "?filter="+action+"&did="+selectedOption.value;
		if( params['cid'] )
			location += "&cid="+params['cid'];
		window.location = location;
	}
	else if( action == "col"){
		
		var location = "?filter="+action+"&cid="+selectedOption.value;
		if( params['catid'] )
			location += "&catid="+params['catid'];
		
		window.location = location;
	}	
	/*else if( action == "catd"){
		var params = getQueryStringArgs();
		if( params['did'] )
			window.location = "?filter="+action+"&catid="+selectedOption.value+"&did="+params['did'];
	}
	else if( action == "catc"){
		var params = getQueryStringArgs();
		window.location = "?filter="+action+"&catid="+selectedOption.value+"&cid="+params['cid'];
	}*/
	else if( action == "lvl" ){
		var location = "?filter="+action+"&lvl="+selectedOption.value;
		if( params['did'] )
			location += "&did="+params['did'];
		if( params['pid'] )
			location += "&pid="+params['pid'];
		if( params['cid'] )
			location += "&cid="+params['cid'];
		
		window.location = location;
	}
	else if( action == "prog" ){
		var location = "?filter="+action+"&pid="+selectedOption.value;
		if( params['did'] )
			location += "&did="+params['did'];
		if( params['cid'] )
			location += "&cid="+params['cid'];
		
		window.location = location;
	}
	else{
		var location = "?filter="+action+"&catid="+selectedOption.value;
		if( params['did'] )
			location += "&did="+params['did'];
		if( params['cid'] )
			location += "&cid="+params['cid'];
		
		window.location = location;
	}
}

function ssesfilt( elem ){
	filt( "ssid", elem)	
}

function sesfilt( elem ){
	filt( "ses", elem)
}

function studfilt( elem ){
	filt( "stud", elem)	
}

function catfilt( elem ){
	filt( "cat", elem)	
}

function crsfilt( elem ){
	if( $(this).attr('id') == "crss" )
		filt( "crss", elem)
	else
		filt( "crs", elem)
}

function lvlfilt( elem ){
	filt( "lvl", elem)
}

function progfilt( elem ){
	filt( "prog", elem)
}

function deptfilt( elem ){	
	filt( "dept", elem)
}

function colfilt( elem ){
	filt( "col", elem)
}

function deptnamefilt( elem, num){	
	if( num == 1)
		filt( "deptadmin", elem);
	else if( num == 2)
		filt( "deptcentre", elem);
	else if( num == "COSIT")
		filt( "deptcosit", elem);
	else if( num == "COSMAS")
		filt( "deptcosmas", elem);
	else if( num == "COAEVOT")
		filt( "deptcoaevot", elem);
	else if( num == "COHUM")
		filt( "deptcohum", elem);
	else if( num == "CLINICAL")
		filt( "deptclinical", elem);
	else
		filt( "deptmedical", elem);
}


function filt( action, elem ){
	
	var params = getQueryStringArgs();
	var selectedOption = elem.options[elem.selectedIndex];
			loc += "&ssid="+$('#ssid').val();
	
	if( action == "ssid"){
		var loc = "?ssid="+selectedOption.value;
		window.location = loc;
	}
	
	if( action == "crs"){
		var loc = "?crs="+selectedOption.value;
		if( !params['ssid'] ){
		}else
			loc += "&ssid="+params['ssid'];
		window.location = loc;
	}
	
	if( action == "crss"){
		var loc = "?csid="+selectedOption.value;
		if( params['lvl'] )
			loc += "&lvl="+params['lvl'];	
		if( params['pid'] )
			loc += "&pid="+params['pid'];
		if( params['did'] )
			loc += "&did="+params['did'];
		if( params['cid'] )
			loc += "&cid="+params['cid'];
		window.location = loc;
	}
	
	if( action == "cat"){
		var loc = "?catid="+selectedOption.value;
		if( params['sid'] )
			loc += "&sid="+params['sid'];	
		/*if( params['pid'] )
			loc += "&pid="+params['pid'];
		if( params['did'] )
			loc += "&did="+params['did'];
		if( params['cid'] )
			loc += "&cid="+params['cid'];*/
		window.location = loc;
	}
	
	if( action == "stud"){
		var loc = "?stid="+selectedOption.value;
		if( params['pid'] )
			loc += "&pid="+params['pid'];
		if( params['did'] )
			loc += "&did="+params['did'];
		if( params['cid'] )
			loc += "&cid="+params['cid'];
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['csid'] )
			loc += "&csid="+params['csid'];
		window.location = loc;
	}
	
	if( action == "ses"){
		var loc = "?sid="+selectedOption.value;
		if( params['pid'] )
			loc += "&pid="+params['pid'];
		if( params['did'] )
			loc += "&did="+params['did'];
		if( params['cid'] )
			loc += "&cid="+params['cid'];
		if( params['catid'] )
			loc += "&catid="+params['catid'];
		if( params['stid'] )
			loc += "&stid="+params['stid'];
		if( params['csid'] )
			loc += "&csid="+params['csid'];
		if( params['lvl'] )
			loc += "&lvl="+params['lvl'];
                if( params['dida'] )
                    loc += "&dida="+params['dida'];
		if( params['didc'] )
			loc += "&didc="+params['didc'];
		window.location = loc;
	}
		
	if( action == "lvl"){
		var loc = "?lvl="+selectedOption.value;
		if( params['pid'] )
			loc += "&pid="+params['pid'];
		if( params['did'] )
			loc += "&did="+params['did'];
		if( params['cid'] )
			loc += "&cid="+params['cid'];
                if( params['sid'] )
			loc += "&sid="+params['sid'];
		window.location = loc;
	}
	
	if( action == "prog"){
		var loc = "?pid="+selectedOption.value;
		if( params['did'] )
			loc += "&did="+params['did'];
		if( params['cid'] )
			loc += "&cid="+params['cid'];
		window.location = loc;
	}
	
	if( action == "dept"){
		var loc = "?did="+selectedOption.value;
		if( params['cid'] )
			loc += "&cid="+params['cid'];
		if( params['csid'] )
			loc += "&csid="+params['csid'];
		if( params['sid'] )
			loc += "&sid="+params['sid'];
			
		window.location = loc;
	}
	
	if( action == "deptadmin"){
		var loc = "?dida="+selectedOption.value;		
		if( params['didc'] )
			loc += "&didc="+params['didc'];
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dct'] )
			loc += "&dct="+params['dct'];
		if( params['dcs'] )
			loc += "&dcs="+params['dcs'];
		if( params['dcv'] )
			loc += "&dcv="+params['dcv'];
		if( params['dch'] )
			loc += "&dch="+params['dch'];
	
		window.location = loc;
	}
	
	if( action == "deptcentre"){
		var loc = "?didc="+selectedOption.value;
		if( params['dida'] )
			loc += "&dida="+params['dida'];		
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dct'] )
			loc += "&dct="+params['dct'];
		if( params['dcs'] )
			loc += "&dcs="+params['dcs'];
		if( params['dcv'] )
			loc += "&dcv="+params['dcv'];
		if( params['dch'] )
			loc += "&dch="+params['dch'];	
		window.location = loc;
	}
	
	if( action == "deptcosit"){
		var loc = "?dct="+selectedOption.value;
		if( params['dida'] )
			loc += "&dida="+params['dida'];
		if( params['didc'] )
			loc += "&didc="+params['didc'];			
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dcs'] )
			loc += "&dcs="+params['dcs'];
		if( params['dcv'] )
			loc += "&dcv="+params['dcv'];
		if( params['dch'] )
			loc += "&dch="+params['dch'];
                if( params['dcm'] )
			loc += "&dcm="+params['dcm'];
                if( params['dcl'] )
			loc += "&dcl="+params['dcl'];
		window.location = loc;
	}
	
	if( action == "deptcosmas"){
		var loc = "?dcs="+selectedOption.value;
		if( params['dida'] )
			loc += "&dida="+params['dida'];
		if( params['didc'] )
			loc += "&didc="+params['didc'];			
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dct'] )
			loc += "&dct="+params['dct'];
		if( params['dcv'] )
			loc += "&dcv="+params['dcv'];
		if( params['dch'] )
			loc += "&dch="+params['dch'];
                if( params['dcm'] )
			loc += "&dcm="+params['dcm'];
                if( params['dcl'] )
			loc += "&dcl="+params['dcl'];
		window.location = loc;
	}
	
	if( action == "deptcoaevot"){
		var loc = "?dcv="+selectedOption.value;
		if( params['dida'] )
			loc += "&dida="+params['dida'];
		if( params['didc'] )
			loc += "&didc="+params['didc'];			
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dcs'] )
			loc += "&dcs="+params['dcs'];
		if( params['dct'] )
			loc += "&dct="+params['dct'];
		if( params['dch'] )
			loc += "&dch="+params['dch'];
                if( params['dcm'] )
			loc += "&dcm="+params['dcm'];
                if( params['dcl'] )
			loc += "&dcl="+params['dcl'];
		window.location = loc;
	}
	
	if( action == "deptcohum"){
		var loc = "?dch="+selectedOption.value;
		if( params['dida'] )
			loc += "&dida="+params['dida'];
		if( params['didc'] )
			loc += "&didc="+params['didc'];			
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dcs'] )
			loc += "&dcs="+params['dcs'];
		if( params['dcv'] )
			loc += "&dcv="+params['dcv'];
		if( params['dct'] )
			loc += "&dct="+params['dct'];	
                if( params['dcm'] )
			loc += "&dcm="+params['dcm'];
                if( params['dcl'] )
			loc += "&dcl="+params['dcl'];
		window.location = loc;
	}
	
        if( action == "deptclinical"){
		var loc = "?dcl="+selectedOption.value;
		if( params['dida'] )
			loc += "&dida="+params['dida'];
		if( params['didc'] )
			loc += "&didc="+params['didc'];			
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dcs'] )
			loc += "&dcs="+params['dcs'];
		if( params['dcv'] )
			loc += "&dcv="+params['dcv'];
		if( params['dct'] )
			loc += "&dct="+params['dct'];	
                if( params['dcm'] )
			loc += "&dcm="+params['dcm'];
                if( params['dch'] )
			loc += "&dch="+params['dch'];
		window.location = loc;
	}
        
        if( action == "deptmedical"){
		var loc = "?dcm="+selectedOption.value;
		if( params['dida'] )
			loc += "&dida="+params['dida'];
		if( params['didc'] )
			loc += "&didc="+params['didc'];			
		if( params['sid'] )
			loc += "&sid="+params['sid'];
		if( params['dcs'] )
			loc += "&dcs="+params['dcs'];
		if( params['dcv'] )
			loc += "&dcv="+params['dcv'];
		if( params['dct'] )
			loc += "&dct="+params['dct'];	
                if( params['dcl'] )
			loc += "&dcl="+params['dcl'];
                if( params['dch'] )
			loc += "&dch="+params['dch'];
		window.location = loc;
	}
        
	if( action == "col"){
		var loc = "?cid="+selectedOption.value;
		window.location = loc;
	}
}

function getQueryStringArgs(){
	//get query string without the initial ?
	var qs = location.search.length > 0 ? location.search.substring(1) : "",
	//object to hold data
	args = {},
	//get individual items
	items = qs.length ? qs.split("&") : [],
	item = null,
	name = null,
	value = null,
	//used in for loop
	i = 0,
	len = items.length;
	//assign each item onto the args object
	for (i=0; i < len; i++){
		item = items[i].split("=");		
		name = decodeURIComponent(item[0]);
		value = item[1];//decodeURIComponent(item[1]);
		if (name.length) {
		args[name] = value;
		}
	}
	return args;
}