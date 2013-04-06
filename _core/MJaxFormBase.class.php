<?php
/*
 * This class will serve as the base form for the MJax Package
 */
class MJaxFormBase{
	protected static $arrExtensions = array();
    const FILEINFO_BASENAME = 'basename';
    //------Static Vars------
    protected static $strCurrAction = null;
    public static $objForm = null;
    public static $EncryptionKey = null;
    public static $FormStateHandler = 'MJaxFormStateHandler';    
	public static $strTemplateDirOverride = null;
	public static $strTemplateRemove = null;

    //------Instance------
    protected $strTitle = null;
    protected $arrControls = array();
    protected $intNextControlId = 1;
    protected $strFormId = null;
    protected static $arrAsset = array();
    protected $strCallType = null;
    protected $strTemplate = null;
    protected $objActiveEvent = null;
    protected $pxyMainWindow = null;
    protected static $arrMiscJSCalls = array();
    protected $strTransition = null;
    protected $blnUseHeader = true;
    protected $blnForceRenderFormState = false;
	protected $strAssetMode = null;
	protected $strAsyncRenderMode = MJaxResponseFormat::XML;

    protected $blnSkipMainWindowRender = false;
    /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "Title": return $this->strTitle;
            case "Transition": return $this->strTransition;
            case "FormId": return $this->strFormId;
            case "CallType": return $this->strCallType;
            case "ActiveEvent": return $this->objActiveEvent;
            case "MainWindow": return $this->pxyMainWindow;
            case "UseHeader" : return $this->blnUseHeader;
            case "Controls" : return $this->arrControls;
            case "SkipMainWindowRender": return $this->blnSkipMainWindowRender;
            case "ForceRenderFormState": return $this->ForceRenderFormState;
			case "AsssetMode": return $this->strAssetMode;
			case "AsyncRenderMode": return $this->strAsyncRenderMode;
			
            default:
                //return parent::__get($strName);
                throw new Exception("No property with name '" . $strName . "' in class " . __CLASS__);
                               
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        switch ($strName) {
          case "Title":
                return $this->strTitle = $mixValue;
        	case "Transition":
                return $this->strTransition = $mixValue;
            case "ActiveEvent":
				return ($this->objActiveEvent = $mixValue);
            case "SkipMainWindowRender":
                return ($this->blnSkipMainWindowRender = $mixValue);
        	case "UseHeader":
                return ($this->blnUseHeader = $mixValue);
        	case "ForceRenderFormState":
                return ($this->blnForceRenderFormState = $mixValue);                
            case "AsssetMode":
                return $this->strAssetMode = $mixValue;
			case "AsyncRenderMode":
				return $this->strAsyncRenderMode = $mixValue;
            default:
                throw new Exception("Class '" . __CLASS__ . "' has no property '" . $strName . "'");
                
        }
    }


    /////////////////////////
    // Helpers for ControlId Generation
    /////////////////////////
    public function GenerateControlId() {
        $strToReturn = sprintf('c%s', $this->intNextControlId);
        $this->intNextControlId++;
        return $strToReturn;
    }


    public static function Run($strFormId, $strAlternateHtmlFile = null) {
        // Ensure strFormId is a class
        $objClass = new $strFormId();
        $objClass->strFormId = $strFormId;
        $objClass->strTransition = MJaxTransition::NONE;
        
        // Ensure strFormId is a subclass of QForm
        if (!($objClass instanceof MJaxForm)){
            throw new Exception('Object is not a subclass of MJaxForm : ' . $strFormId);
        }
        //Check to see if the form has posted data
        if(is_null($objClass->strAssetMode)){
			/*if(
				(MJaxForm::IsMobileWeb()) ||
				(array_key_exists('mobile', $_GET))
			){
				$objClass->strAssetMode = MJaxAssetMode::MOBILE;
				$objClass->strAsyncRenderMode = MJaxResponseFormat::HTML;
			}else{*/
				$objClass->strAssetMode = MJaxAssetMode::WWW;
			//}
		}
        if (key_exists(MJaxFormPostData::MJaxForm__FormState, $_POST)){
                //Tell the object and the application what the call type is
                
				$strPostDataState = $_POST[MJaxFormPostData::MJaxForm__FormState];

				if($strPostDataState){					
					// We might have a valid form state -- let's see by unserializing this object
					$objClass = MJaxForm::Unserialize($strPostDataState);
                }
                $objClass->ParsePostData();

                $objClass->strCallType = MJaxCallType::Ajax;
               // QApplication::$RequestMode = QRequestMode::Ajax;

        }elseif(
        	(key_exists(MJaxFormPostData::ACTION, $_POST)) && 
        	($_POST[MJaxFormPostData::ACTION] == MJaxFormAction::CHANGE_PAGE)
		){
             $objClass->strCallType = MJaxCallType::Ajax;
             //Create a proxy for the main content panel
             $objClass->pxyMainWindow = new MJaxControlProxy($objClass, 'mainWindow_inner');
             $objClass->Form_Create();
			 
        }else{
            $objClass->strCallType = MJaxCallType::None;
			 //Create a proxy for the main content panel
            $objClass->pxyMainWindow = new MJaxControlProxy($objClass, 'mainWindow_inner');
            $objClass->Form_Create();           
        }
		try{
			MJaxForm::DefineAssetDir($objClass->strAssetMode);
		}catch(Exception $e){
			error_log($e->getMessage());
		}

        if($objClass->strCallType == MJaxCallType::None){
            foreach(self::$arrExtensions as $intIndex => $objExtension){
                $objExtension->InitControl($objClass);
            }
        }
        if(key_exists(MJaxFormPostData::ACTION, $_POST)){
        	self::$objForm = $objClass;
            self::$strCurrAction = $_POST[MJaxFormPostData::ACTION];
            switch(self::$strCurrAction){
                case(MJaxFormAction::CONTROL_EVENT):
                    $objClass->__call(
                    	'TriggerControlEvent',
                    	array($_POST[MJaxFormPostData::CONTROL_ID], $_POST[MJaxFormPostData::EVENT])
					);                  
					//$objClass->TriggerControlEvent($_POST[MJaxFormPostData::CONTROL_ID], $_POST[MJaxFormPostData::EVENT]);
                break;
            }
        }

        if(is_null($strAlternateHtmlFile)){
        	if(is_null($objClass->strTemplate)){
	            $objClass->strTemplate = MJaxForm::LocateTemplate($_SERVER[MLCServer::SCRIPT_FILENAME], $objClass->strAssetMode);
				if(!file_exists($objClass->strTemplate)){
					if($objClass->strAssetMode == MJaxAssetMode::MOBILE){
						$objClass->strTemplate  = __MJAX_CORE_VIEW__ . '/' . $objClass->strAssetMode . '/form.tpl.php';
					}
				}
			}
        }else{
            $objClass->strTemplate = $strAlternateHtmlFile;
        }
        foreach(self::$arrExtensions as $intIndex => $objExtension){
				$objExtension->RunPreRender($objClass);
		}
        $objClass->Form_PreRender();
		if($objClass->strCallType == MJaxCallType::Ajax){
            $objClass->RenderAjax();
        }else{
            $objClass->Render();
        }
        $objClass->__call('Form_Exit',array());
		
    }
    public static function LocateTemplate($strFileLoc, $strAssetMode){
    	if(is_null(self::$strTemplateDirOverride)){
    		$strTemplateDir = __VIEW_ACTIVE_APP_DIR__ . '/' . $strAssetMode;//__VIEW_MAIN_APP_DIR__;
    	}else{
    		$strTemplateDir = MJaxForm::$strTemplateDirOverride;
    	}
		
		/*if(!is_null(MJaxForm::$strTemplateRemove)){
			$strFileLoc = str_replace(MJaxForm::$strTemplateRemove, '', $strFileLoc);
		}*/
		
        $strFileName = str_replace('.class.php', '', $strFileLoc);
        $strFileName = str_replace('.php', '', $strFileName);		  
        $strFileName = str_replace(__INSTALL_ROOT_DIR__, $strTemplateDir, $strFileName);
      
        return $strFileName . '.tpl.php';
    }
	public function RenderTemplate($strFileLoc){
		if(is_null(self::$strTemplateDirOverride)){
    		$strTemplateDir = __VIEW_ACTIVE_APP_DIR__  . '/' . $this->strAssetMode;//__VIEW_MAIN_APP_DIR__;
    	}else{
    		$strTemplateDir = MJaxForm::$strTemplateDirOverride;
    	}
		
		$strRealFileLoc = $strTemplateDir . '/' . $strFileLoc. '.tpl.php';
		
		
		require($strRealFileLoc);
	}
    

    protected function Render(){
    	if($this->blnUseHeader){
	        $strHeader = $this->EvaluateTemplate(__MJAX_CORE_VIEW__ . '/' . $this->strAssetMode . '/_header.inc.php');
	        echo($strHeader);
    	}
    	
        require($this->strTemplate);
        
        if($this->blnUseHeader){
	        $strFooter = $this->EvaluateTemplate(__MJAX_CORE_VIEW__ . '/' . $this->strAssetMode . '/_footer.inc.php');
	        $strFormState = sprintf('<input type="hidden" name="%s" id="%s" value="%s" />', MJaxFormPostData::MJaxForm__FormState, MJaxFormPostData::MJaxForm__FormState, MJaxForm::Serialize($this));
	        echo($strFormState);
	        echo($strFooter);
        }
        
        
    }
    protected function RenderAjax(){
        ob_clean();
        $blnModified = $this->FormIsModified();
        
        $arrControlData = array();
		
        if($blnModified){
        	foreach($this->arrControls as $objControl){
                if($objControl->Modified){                	
                    $arrControlData[$objControl->ControlId] = $objControl;
                }
            }
        }
		if(!$this->blnSkipMainWindowRender){
           $strControl = $this->RenderHeaderAssets(false);
           $strControl .= $this->EvaluateTemplate($this->strTemplate);               
           $arrControlData['mainWindow'] = trim($strControl);              
        }
		switch($this->strAsyncRenderMode){
			case(MJaxResponseFormat::XML):
				$this->RenderAjaxXML($arrControlData);
			break;
			case(MJaxResponseFormat::JSON):
				$this->RenderAjaxJSON($arrControlData);
			break;
			case(MJaxResponseFormat::HTML):
				$this->Render();
			break;
		}
    }
	
	protected function RenderAjaxHtml($arrControlData){
		require($this->strTemplate);
		return;
		$arrControlResponse = array();
		
		foreach($arrControlData as $strControlId => $mixControl){
			
			if($mixControl instanceof MJaxControl){
				$arrControlResponse[$strControlId] = $mixControl->Render(false, true) . "\n";
			}elseif($strControlId == 'mainWindow'){
				$arrControlResponse[$strControlId] = $mixControl;
			}elseif(is_string($mixControl)){
				$arrControlResponse[$strControlId] = $mixControl;
			}
		}
		$arrControlResponse[MJaxFormPostData::MJaxForm__FormState] = MJaxForm::Serialize($this);
       	
		$strHtml .= $this->RenderHeaderAssetsAsJs(false);
		$strHtml .= $this->RenderControlJSCalls(false, true);
		$strHtml .= $this->RenderControlRegisterJS(false, true);
		$strHtml .= $this->RenderMiscJSCalls(false, true);
		
		$arrResponse = array(
			'controls'=>$arrControlResponse,
			'actions'=> $arrActions
		);
        echo(json_encode($arrResponse));
        header('Content-Type: application/json');
        
    }
	protected function RenderAjaxJSON($arrControlData){
		$arrControlResponse = array();
		
		foreach($arrControlData as $strControlId => $mixControl){
			
			if($mixControl instanceof MJaxControl){
				$arrControlResponse[$strControlId] = $mixControl->Render(false, true) . "\n";
			}elseif($strControlId == 'mainWindow'){
				$arrControlResponse[$strControlId] = $mixControl;
			}elseif(is_string($mixControl)){
				$arrControlResponse[$strControlId] = $mixControl;
			}
		}
		$arrControlResponse[MJaxFormPostData::MJaxForm__FormState] = MJaxForm::Serialize($this);
       	$arrActions = array();
		$arrActions[] = $this->RenderHeaderAssetsAsJs(false);
		$arrActions[] = $this->RenderControlJSCalls(false, true);
		$arrActions[] = $this->RenderControlRegisterJS(false, true);
		$arrActions[] = $this->RenderMiscJSCalls(false, true);
		
		$arrResponse = array(
			'controls'=>$arrControlResponse,
			'actions'=> $arrActions
		);
        echo(json_encode($arrResponse));
        header('Content-Type: application/json');
        
    }
	protected function RenderAjaxXML($arrControlData){
		$strControlFull = '';
		
		foreach($arrControlData as $strControlId => $mixControl){
			
			if($mixControl instanceof MJaxControl){
				$strControlFull .= $mixControl->Render(false, true) . "\n";
			}elseif($strControlId == 'mainWindow'){
				$strControlFull .= sprintf(
				 	"<control id='mainWindow' transition='%s'>%s</control>",
				 	$this->strTransition,
				 	self::XmlEscape($mixControl)
				);
			}elseif(is_string($mixControl)){
				$strControlFull .= self::XmlEscape($mixControl);
			}
		}
		$strControlFull .= sprintf(
			"<control id='%s' transition='%s'>%s</control>",
			 MJaxFormPostData::MJaxForm__FormState,
			 MJaxTransition::SET_VALUE,
			 MJaxForm::Serialize($this)
		);
		
        echo("<?xml version=\"1.0\"?>\r\n");
		$strReturn = sprintf(
			'<mjax>
	    		<controls>
					%s
			    </controls>
			    <actions>
			    	<action>
			    		%s
			    	</action>
			        <action>
			            %s
			            %s
			        </action>
			        <action>
			            %s
			        </action>
			    </actions>
			</mjax>',
			$strControlFull,
			$this->RenderHeaderAssetsAsJs(false),
			$this->RenderControlJSCalls(false, true),
			$this->RenderControlRegisterJS(false, true),
			$this->RenderMiscJSCalls(false, true)
		);
        echo($strReturn);
        header('Content-Type: text/xml');
        
    }
    public function RenderClassJSCalls($blnPrint = true, $blnAjaxFormating = false){
        //Cycle through all controls and get their JS calls for this time
        $strRendered = '';
		
        foreach(MJaxApplication::$arrCssClasses as $cssClass){
            $strRendered .= $cssClass->RenderJSCalls($blnAjaxFormating);
        }
        if($blnAjaxFormating){
            $strRendered = self::XmlEscape(trim($strRendered));
        }        
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function RenderControlJSCalls($blnPrint = true, $blnAjaxFormating = false){
        //Cycle through all controls and get their JS calls for this time
        $strRendered = '';
        
        foreach($this->arrControls as $objControl){
            $strRendered .= $objControl->RenderJSCalls($blnAjaxFormating) ."\n\n";
        }
        if($blnAjaxFormating){
            $strRendered = self::XmlEscape(trim($strRendered));
        }
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function RenderMiscJSCalls($blnPrint = true, $blnAjaxFormating = false){
        //Cycle through all controls and get their JS calls for this time
        $strRendered = ''; 
    	if(!$blnAjaxFormating){
            $strRendered .= "<script language='javascript'>\n\t";
        } 
		 
        foreach(self::$arrMiscJSCalls as $strJS){
            $strRendered .= $strJS;
        }        
        if($blnAjaxFormating){
            $strRendered = self::XmlEscape(trim($strRendered));
        }
    	if(!$blnAjaxFormating){
            $strRendered .= "</script>\n\t";
       }
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function AddJSCall($strJS){
        self::$arrMiscJSCalls[] = $strJS;
    }
    public function RenderCssClasses($blnPrint = true){
        $strRendered = '';        
        foreach(MJaxApplication::$arrCssClasses as $objCssClass){
            $strRendered .= $objCssClass->RenderCss(false);
        }
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function TriggerControlEvent($strControlId, $strEvent){
        if(!key_exists($strControlId, $this->arrControls)){
            throw new Exception("Control '" . $strControlId . "' does not exist");
        }
        $this->arrControls[$strControlId]->__call('TriggerEvent',array($strEvent));
        
    }
    public function RegisterControl($objControl){
        $strControlId = $objControl->ControlId;
        if(key_exists($strControlId, $this->arrControls)){
                throw new Exception("A control with the Id '" . $strControlId . "' already exists");
        }
        $this->arrControls[$strControlId] = $objControl;        
    }
    public function RemoveControl($mixControl){
    	if(is_string($mixControl)){
    		$strControlId = $mixControl;
    	}else{
    		$strControlId = $mixControl->ControlId;
    	}
    	$arrChildControls = $this->arrControls[$strControlId]->ChildControls;
    	foreach($arrChildControls as $objChildControl){
    		$this->RemoveControl($objChildControl);
    	}		
    	unset($this->arrControls[$strControlId]);
    }
    public function AddHeaderAsset($objNewAsset){    	
        foreach(self::$arrAsset as $objAsset){

            if($objAsset->Src == $objNewAsset->Src){
                //Were already including this so return
                return;
            }
        }
        self::$arrAsset[] = $objNewAsset;
    }
    public function RenderHeaderAssets($blnPrint = true){        
        $strRender = '';
        foreach(self::$arrAsset as $objAsset){        	
            if(!$objAsset->AlreadyRendered){
            	$strRender .= $objAsset->__toString();
                $objAsset->AlreadyRendered = true;
            }
        }
        if($blnPrint){
            echo($strRender);
        }else{
            return $strRender;
        }
        
    }
    
	public function RenderHeaderAssetsAsJs($blnPrint = true){        
        $strRender = '';
        foreach(self::$arrAsset as $objAsset){        	
            if(!$objAsset->AlreadyRendered){
            	$strRender .= $objAsset->RenderAsJSCall(false);
                $objAsset->AlreadyRendered = true;
            }
        }
        $strRender = self::XmlEscape(trim($strRender));
        if($blnPrint){
            echo($strRender);
        }else{
            return $strRender;
        }
        
    }
    public function RenderControlRegisterJS($blnPrint = true, $blnAjaxFormating = false){
       $strRendered = '';
       if(!$blnAjaxFormating){
            $strRendered .= "<script language='javascript'>\n\t";
       }
       $strRendered .= "$(document).ready(function(){\n\t";
       $strRendered .= "MJax.ClearRegisteredControls();\n\t";
       foreach($this->arrControls as $objControl){
            $strRendered .= sprintf("MJax.RegisterControl('%s');\n\t", $objControl->ControlId);
       }
       $strRendered .= "});\n\t";
       if(!$blnAjaxFormating){
            $strRendered .= "</script>";
       }
       if($blnAjaxFormating){
            $strRendered = self::XmlEscape(trim($strRendered));
        }
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function ParsePostData(){
        foreach($this->arrControls as $objControl){
            $objControl->ParsePostData();
        }
    }
	public function FormIsModified(){
		$blnModified = false;
        foreach($this->arrControls as $objControl){        	
            if($objControl->Modified){      
            	$blnModified = true;
            }   
        }
		return $blnModified;
	}
    protected function Form_Run() {}
    protected function Form_Load() {}
    protected function Form_Create() {}
    protected function Form_PreRender() {}
    protected function Form_Validate() {return true;}
    protected function Form_Exit() { }
    
    /**
     * @param Form $objForm
     * @return string the Serialized Form
     */
    public static function Serialize(MJaxForm $objForm) {
         // Create a Clone of the Form to Serialize
        $objForm = clone($objForm);

        // Cleanup Reverse Control->Form links
        if($objForm->arrControls){
            foreach ($objForm->arrControls as $objControl){
                $objControl->SetForm(null);
            }
        }
        // Use PHP "serialize" to serialize the form
        $strSerializedForm = serialize($objForm);

        // Setup and Call the FormStateHandler to retrieve the PostDataState to return
        $strSaveCommand = array(MJaxForm::$FormStateHandler, 'Save');
        $strPostDataState = call_user_func_array($strSaveCommand, array($strSerializedForm, false));

        // Return the PostDataState
        return $strPostDataState;
    }

    /**
     * @param string $strSerializedForm
     * @return Form the Form object
     */
    public static function Unserialize($strPostDataState) {
        // Setup and Call the FormStateHandler to retrieve the Serialized Form
        $strLoadCommand = array(MJaxForm::$FormStateHandler, 'Load');
        $strSerializedForm = call_user_func($strLoadCommand, $strPostDataState);		
        if ($strSerializedForm) {
            // Unserialize and Cast the Form
            $objForm = unserialize($strSerializedForm);
        	// Reset the links from Control->Form
            if ($objForm->arrControls){
            	
                foreach($objForm->arrControls as $objControl){
                    $objControl->SetForm($objForm);
                }
				
            }
            // Return the Form
            return $objForm;
        } else
            return null;
    }
	public static function SetTemplateDirOverride($strTemplateDir, $strTemplateRemove = null){
		MJaxForm::$strTemplateDirOverride = $strTemplateDir;
		MJaxForm::$strTemplateRemove = $strTemplateRemove;
	}
    public function EvaluateTemplate($strTemplate) {
        global $_ITEM;
        global $_FORM;
        global $_CONTROL;

        if ($strTemplate) {
           
            // Store the Output Buffer locally
            $strAlreadyRendered = ob_get_contents();
            ob_clean();

            // Evaluate the new template
            ob_start('__MJaxForm_EvaluateTemplate_ObHandler');
                require($strTemplate);
                $strTemplateEvaluated = ob_get_contents();
            ob_end_clean();
            

            // Restore the output buffer and return evaluated template
            print($strAlreadyRendered);
           
            return $strTemplateEvaluated;
        } else
            return null;
    }
	public static function IsMobileWeb(){
		$strUseragent = $_SERVER['HTTP_USER_AGENT'];
		return(preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$strUseragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($strUseragent,0,4)));
	}
	public static function DefineAssetDir($strAssetMode){
		
		if(!defined('__ASSETS__')){
			define('__ASSETS__', __ASSETS_URL__ . '/' . $strAssetMode);
			define('__ASSETS_JS__', __ASSETS__ . '/js');
			define('__ASSETS_CSS__', __ASSETS__ . '/css');
			define('__ASSETS_IMG__', __ASSETS__ . '/img');
		}else{
			throw new Exception("ASSET DIRECTORYS are alredy defined");
		}
	}
	public static function XmlEscape($strString) {
		if ((strpos($strString, '<') !== false) ||
			(strpos($strString, '&') !== false)) {
			$strString = str_replace(']]>', ']]]]><![CDATA[>', $strString);
			$strString = sprintf('<![CDATA[%s]]>', $strString);
		}

		return $strString;
	}
	public function OpenNewTab($strUrl){
		$this->AddJSCall(
			sprintf(
				'MJax.OpenNewTab("%s");',
				$strUrl
			)
		);		
	}
	public function __call($strName, $arrArguments){
		
		foreach(self::$arrExtensions as $intIndex => $objExtension){
			
			if(method_exists($objExtension, $strName)){
				
				$objExtension->SetControl($this);
				return  call_user_func_array(array($objExtension, $strName), $arrArguments); 
				//return $objExtension->__call($strName, $arrArguments);
			}
		}
	    if(method_exists($this, $strName)) { 
          return call_user_func_array(array($this, $strName), $arrArguments); 
        }else{ 
          throw new Exception(sprintf('The required method "%s" does not exist for %s', $strName, get_class($this))); 
        } 
		
	}
	public static function AddExtension($objExtension){
		self::$arrExtensions[] = $objExtension;
	}
	
}
function __MJaxForm_EvaluateTemplate_ObHandler($strBuffer) {
	return $strBuffer;
}
?>