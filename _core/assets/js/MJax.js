var MJax = {
    ControlType:new Object(),
    /*-------*/
    strCurrPageUrl:null,
    objCurrPostData:{},
    arrControls:new Array(),
    blnFadeOnLoad:false,
    objTransitions:new Object(),
    objExtensions:new Object(),
    Init:function(){        
        var arrLocationParts = document.location.toString().split("/");
        this.strCurrPageUrl = MJax.GetDocRealLocation();
        /*
        if(this.strCurrPageUrl != MJax.GetDocRealLocation()){
        	//Removed for hypespark
            //document.location = this.strCurrPageUrl;
            return;
        }*/
        
        $('.MJaxLink').click(function(e){
            var strHref = $(this).attr('href');
            MJax.LoadMainPage(strHref, {action:'change_page'}, true);
            e.preventDefault();
        });
        //Call back setup
        this.funMainPageLoad = function(){};


        //Table Stuff
        $('body').on('click', '.mjax-table[data-edit-mode=inline] .mjax-td',function(objEvent){
            $(this).closest('.mjax-table').trigger('mjax-table-select');

            var jThis = $(this);
            var jTable = jThis.closest('table');
            var jRow = jThis.closest('tr');
            var objData = {};
            objData[jTable.attr('id')+ '_data'] = {
                'row':jRow.attr('id'),
                'column':jThis.attr('id'),
                'column_key':jThis.attr('data-key')
            };
            MJax.TriggerControlEvent(
                objEvent,
                '#'+ jTable.attr('id'),
                'mjax-table-select',
                objData
            );
        });
        $('body').on('keydown', '.mjax-td-selected input',function(objEvent){
            var jThis = $(this);
            var jTable = jThis.closest('.mjax-table');
            console.log(objEvent);
            console.log(objEvent.keyCode);
            jNext = null;
            switch(objEvent.keyCode){
                case(9)://tab
                    if(!objEvent.shiftKey){
                        var jNext = $(this).closest('td').nextAll('td[data-editable=true]');
                        if(jNext.length == 0){
                            //Jump to next row
                            var jNextRow = $(this).closest('tr').next();
                            jNext = jNextRow.find('td[data-editable=true]');
                        }
                    }else{
                        var jNext = $(this).closest('td').prevAll('td[data-editable=true]');
                        if(jNext.length == 0){
                            var jNextRow = $(this).closest('tr').prev();
                            jNext = jNextRow.find('td[data-editable=true]').last();
                        }
                    }

            }

            if(jNext != null){
                if(jNext.length >= 1){
                    $(jNext[0]).trigger('click');
                }else{
                    var objData = {};
                    //Figure out a way to trigger the blur event
                    objData[jTable.attr('id')+ '_data'] = {
                        'row':-1,
                        'column':-1,
                        'column_key':-1
                    };
                    MJax.TriggerControlEvent(
                        objEvent,
                        '#'+ jTable.attr('id'),
                        'mjax-table-select',
                        objData
                    );
                }
                objEvent.preventDefault();
            }

        });

        //ControlSpecific
        /*$('.MJaxLinkButton').each(function(){
            var jThis = $(this);
            jThis.data('href', jThis.attr('href'));
            jThis.attr('href', 'javascript:');
        });*/
     
        //Set Up AjaxTransitions
        
        this.objTransitions.None = function(strControlId, strHtml){
        	/*if(strControlId == 'mainWindow'){
        		var strSelector = "body";
        	}else{*/
        		var strSelector = "#" + strControlId;
        	//}
        	var jEle = $(strSelector);
        	
        	if((jEle.length > 0)){
	        	if(
	        		(jEle[0].nodeName == 'DIV') ||
                    (jEle[0].nodeName == 'A') ||
                    (jEle[0].nodeName == 'TABLE')
	        	){
	            	jEle.html(strHtml);
		        }else{
		           	jEle.val(strHtml);
	           	}

            }else{
            	MLog("Missing DOM element '#" + strControlId + "'");
            }
            MJax.EndTransition();
        }
        this.objTransitions.Slide = function(strControlId, strHtml){
            $("#" + strControlId).html(strHtml);
            MJax.EndTransition();
        }
        this.objTransitions.Fade = function(strControlId, strHtml){
            $("#" + strControlId).fadeOut(1000, function(){$(this).html(strHtml).fadeIn(1000, MJax.EndTransition);});
        }
        this.objTransitions.SetValue = function(strControlId, strHtml){
            $("#" + strControlId).val(strHtml);
        }

        //Set up extensions
        this.objExtensions.MJaxUploadPanel = {
            Read:function(strControlId){
                var jPnlUpload = $('#' . strControlId);
                if(jPnlUpload.attr('initCall') == 'true'){
                    jPnlUpload.attr('initCall', 'false');
                    return false;
                }else{

                    var s = jQuery.extend({}, jQuery.ajaxSettings, s);
                    var xml = {}
                    var pnlUpload = document.getElementById(strControlId);
                    try
                    {
                        if(pnlUpload.contentWindow)
                        {
                             xml.responseText = pnlUpload.contentWindow.document.body?pnlUpload.contentWindow.document.body.innerHTML:null;
                             xml.responseXML = pnlUpload.contentWindow.document.XMLDocument?pnlUpload.contentWindow.document.XMLDocument:pnlUpload.contentWindow.document;

                        }else if(pnlUpload.contentDocument)
                        {
                            xml.responseText = pnlUpload.contentDocument.document.body?pnlUpload.contentDocument.document.body.innerHTML:null;
                            xml.responseXML = pnlUpload.contentDocument.document.XMLDocument?pnlUpload.contentDocument.document.XMLDocument:pnlUpload.contentDocument.document;
                        }
                    }catch(e)
                    {
                        alert(e.toString());
                        jQuery.handleError(s, xml, null, e);
                    }

                    var objResponse = JSON.parse(xml.responseText);
                    if(objResponse.success == 'true'){
                        var objEvent = new Object();
                        objEvent.target = pnlUpload;
                        MJax.TriggerControlEvent(objEvent, strControlId, 'uploadsuccess', objResponse);
                    }
                    return objResponse;
                }
            }
        }
    },
    funAjaxLoadStart:function(strData){
        if(typeof(MJax.Alert) != 'undefined'){
            MJax.Alert(
                "<h1><i class='icon-refresh icon-spin icon-large'></i> &nbsp;&nbsp;"+ strData + "</h1>"
            );
            $(document).one('mjax-ajax-response', function(){
                MJax.BS.HideAlert();
            });
        }
    },
    GetDocAncorLocation:function(){
        var arrLocationParts = document.location.toString().split("/");

        var arrCurrPageParts = document.location.toString().split("#");
        if(arrCurrPageParts.length == 1){
           if((arrLocationParts[3] == undefined) || (arrLocationParts[3].length == 0)){
                return 'index.php';
           }else{
        	   var strLocation = "/";
	           for(i = 3; i < arrLocationParts.length; i++){
	           		strLocation += arrLocationParts[i];
	           		if(i < arrLocationParts.length - 1){
	        			strLocation += "/";
	        		}
	           }
               return strLocation;
           }
        }else{
           return arrCurrPageParts[1]; 
        }
    },
    GetDocRealLocation:function(){
        var arrLocationParts = document.location.toString().split("/");
        if(arrLocationParts[3] == undefined){
            return 'index.php';
        }else{
        	var strLocation = "/";
        	for(i = 3; i < arrLocationParts.length; i++){
        		strLocation += arrLocationParts[i];
        		if(i < arrLocationParts.length - 1){
        			strLocation += "/";
        		}
        	}
            var arrCurrPageParts = strLocation.split("#");
            if(arrCurrPageParts.length == 1){
               return strLocation;
            }else{
               return arrCurrPageParts[0];
            }
        }
    },
    UpdateDocLocation:function(){
        document.location = this.GetDocRealLocation() + "#" + this.strCurrPageUrl;

    },
    LoadMainPage:function(strUrl, objData, blnFade){
        if(blnFade == undefined){
            blnFade = false;
        }
        $(document).trigger('mjax-load-start');
        MJax.blnFadeOnLoad = blnFade;
        this.strCurrPageUrl = strUrl;
        if(objData == undefined){
            this.objCurrPostData = {};
        }else{
            this.objCurrPostData = objData;
        }
        
        for(var i = 0; i < this.arrControls.length; i++){
        	jControl = $('#' + this.arrControls[i]);
        	if(jControl.is('div')){
        		//DO nothing
        	}else if(
                (jControl.attr('type') == 'checkbox') ||
                (jControl.attr('type') == 'radio')
            ){
        		this.objCurrPostData[this.arrControls[i]] = jControl.is(':checked');
        	}else{
           		this.objCurrPostData[this.arrControls[i]] = jControl.val();
           	}
        }
        
        var jXhr = $.ajax({
              url: MJax.strCurrPageUrl,
              success: MJax.LoadMainPageLoadCallback,
              data:MJax.objCurrPostData,
              dataType:'xml',
              error: MJax.LoadMainPageLoadFail,
              type:'POST'
        });

        //$("#mainWindow").load(MJax.strCurrPageUrl + " #mainWindow", MJax.objCurrPostData, MJax.LoadMainPageLoadCallback);
        MJax.objCurrPostData = {};
        return jXhr;
        
    },
    LoadMainPageLoadCallback:function(strData, strTextStatus, xmlResponse){
        $(document).trigger('mjax-ajax-response');
        var jData = $(strData);
        
             var jCollControls = jData.find('controls').children();
             for(var i = 0; i < jCollControls.length; i++){
                 var jControl = $(jCollControls[i]);
                 //MLog(jData);
                 var objCurrTransition = MJax.objTransitions[jControl.attr('transition')];
                 if(typeof objCurrTransition == 'undefined'){
                     objCurrTransition = MJax.objTransitions['None'];
                 }
                 $("#" + jControl.attr('id')).attr('style', jControl.attr('style'));
                 var strValue = jControl.attr('value');
                 if(typeof strValue == 'undefined'){
                 	try{
                 		strValue = jControl.html();
                 	}catch(e){
                        console.log('HTML FAIL: ' + jControl.attr('id'));
                 		strValue = jControl.text();
                 	}
                 }
                var strSelector = "#" +  jControl.attr('id');
	        	var jEle = $(strSelector);

                // $('body').off('change', strSelector);

	        	if((jEle.length > 0)){
                    console.log(strSelector + ' - ' + jEle[0].nodeName + ' - ' + typeof strValue);


		        	if(
                            (jEle[0].nodeName == 'DIV') ||
                            (jEle[0].nodeName == 'A') ||
                            (jEle[0].nodeName == 'TABLE')
		        	){
                        /*jEle.empty();
                        jEle.append(jControl.children());*/
		            	jEle.html(strValue);
			        }else if(
                        (jEle[0].nodeName == 'INPUT') &&
                        (
                            (jEle.attr('type') == 'checkbox')
                        )
                    ){
                        var strChecked = jControl.attr('checked');
                        if(typeof(strChecked) == 'undefined'){
                            strChecked = false;
                        }
                        jEle.attr('checked', strChecked);
                        //$.uniform.update(jEle);
                    } else if(jEle[0].nodeName == 'SELECT'){
                        jEle.children().remove();

                        var jOptions = $(strValue);
                        jEle.append(jOptions);

                    }else{
			           	jEle.val(strValue);
		           	}
                    if(typeof jControl.attr('style') != 'undefined'){
                        jEle.attr('style',jControl.attr('style'));
                    }
	            }else{
	            	if(strSelector == '#MJaxForm__FormState'){
	            		var jEle = $('<input id="MJaxForm__FormState" type="hidden"></input>');
	            		jEle.val(strValue);
	            		$('body').append(jEle);
	            	}else{
	            		//MLog("Missing DOM element '" + strSelector + "'");
	            	}
	            }
	            MJax.EndTransition();
                 
             }
               
             jCollControls = jData.find('action');
             for(i = 0; i < jCollControls.length; i++){
                 jControl = $(jCollControls[i]);
                 var strText = jControl.text();
                 eval(strText);
             }
        $(document).trigger('mjax-page-load');

        if(typeof  MJax.funMainPageLoad != 'undefined'){
        	MJax.funMainPageLoad();
        }

    },
    LoadMainPageLoadFail:function(jXhr, strTextStaus, strErrorThrown){
    	$(document).trigger('mjax-load-error');
        console.log(strErrorThrown);
    	if(typeof(MJax.Alert) != 'undefined'){
    		MJax.Alert(
    			
    			jXhr.responseText
    		);
    	}
    },
    TriggerControlEvent:function(objEvent, strSelector, strEvent, objData){
        var jTarget = $(strSelector);//$(objEvent.target);
        if(typeof objEvent == 'undefined'){
            var objEvent = {};
        }

        if(
            (typeof objEvent.preventDefault != 'undefined')
        ){
        	objEvent.preventDefault();
        }
        var jFormState = $("#MJaxForm__FormState");
        var strFormState = jFormState.attr('value');
        if(typeof objData == 'undefined'){
            objData = new Object();
        }
        var strId = jTarget.attr('id');
        if((typeof strId == 'undefined')|| (strId.length == 0)){
        	//var jTarget = $(strSelector);
        	strId = strSelector.substr(1, strSelector.length - 1);
        }
        var strOnLoad = jTarget.attr('data-mlc-on-ajax-text');
        if(typeof strOnLoad != 'undefined'){
            MJax.funAjaxLoadStart(strOnLoad);
        }
        objData.action = 'control_event';
        objData.control_id = strId;
        objData.event = strEvent;
        objData.MJaxForm__FormState = strFormState;
        if(objEvent.type == 'keypress'){
            objData.keyCode = objEvent.keyCode;
        }
        
        return MJax.LoadMainPage(MJax.strCurrPageUrl, objData);
        
    },
    ClearRegisteredControls:function(){
        this.arrControls = new Array();
    },
    RegisterControl:function(strControlId){
        this.arrControls[this.arrControls.length] = strControlId;
    },
    EndTransition:function(){
        $('body').trigger('transitionend');
    },
    OpenNewTab:function(strUrl){
		  window.open(strUrl, '_blank');
		  window.focus();
    }
};
/*
MJax.ControlType.ScrollBar = {
    Init:function(){
        var jParent = $('scrollBar_holder').parent();
        jParent.css('width', jParent.css('width'));
        var intScale = jParent.css('height');
        $('scrollBar_slider').css('height', )
    }
};
*/
function MLog(mixObject){
	if(typeof console != 'undefined'){
		console.log(mixObject);
	}
}
