<?php
/* 
 * This control will have be the base for all MJax controls
 */
abstract class MJaxControlBase{
	protected static $arrExtensions = array();
    protected $strControlId = null;
    protected $arrEvents = array();
    protected $objForm = null;
    protected $objParentControl = null;
    protected $strActionParameter = null;
    protected $strText = '';
    protected $strName = null;
    protected $blnAutoRenderChildren = false;
    protected $arrChildControls = array();
    protected $arrPlugins = array();
    protected $objStyle = null;
    protected $arrCssClasses = array();
    protected $strTemplate = null;
    protected $arrAttr = array();
    protected $blnModified = false;
    protected $strTransition = null;
    protected $blnRenderClearActions = false;
    public function  __construct($objParentControl, $strControlId = null, $arrAttrs = null) {
        if(is_null($objParentControl)){
        	throw new Exception("This is a null parent control");
        }
       $this->blnModified = true;
		
        if($objParentControl instanceof MJaxForm){
            $this->objForm = $objParentControl;  
			if(!is_null($strControlId)){
	            $this->strControlId = $strControlId;
	        }else{
	            $this->strControlId = $this->objForm->GenerateControlId();
	        }
			          
        }else{
            $this->objParentControl = $objParentControl;
            $this->objForm = $this->objParentControl->Form;
			
			if(!is_null($strControlId)){
	            $this->strControlId = $strControlId;
	        }else{
	        	if(
	        		(!is_null($arrAttrs)) &&
	        		(array_key_exists('id', $arrAttrs))
				){
					$this->strControlId = $arrAttrs['id'];					
				}else{
	            	$this->strControlId = $this->objForm->GenerateControlId();
				}
	        }
			
            $this->objParentControl->AddChildControl($this);
        }
    	
        $this->strTransition = MJaxTransition::NONE;
        $this->objForm->RegisterControl($this);
        $this->objStyle = new MJaxControlStyle();
        //$this->Create_Controls();
        if(!is_null($arrAttrs)){
        	if(array_key_exists('id', $arrAttrs)){
        		unset($arrAttrs['id']);
        	}
			if(array_key_exists('type', $arrAttrs)){
        		unset($arrAttrs['type']);
        	}
			if(array_key_exists('class', $arrAttrs)){
        		unset($arrAttrs['class']);
        	}
			if(array_key_exists('name', $arrAttrs)){
        		unset($arrAttrs['name']);
        	}
        	$this->arrAttr = $arrAttrs;
		}
		
		foreach(self::$arrExtensions as $objExtension){
			$objExtension->InitControl($this);
		}
    }

    public function SetForm($objForm) {
        $this->objForm = $objForm;
        if(is_null($this->objForm)){
        	$objControl = null;
        }else{
        	$objControl = $this;	
        }        
		
        foreach($this->arrEvents as $arrEvent){
        	foreach($arrEvent as $objEvent){
        		$objEvent->SetControl($objControl);
        	}
        }
    }
    public function AddAction($objEvent, $objAction){
    	if(
    		(
				($objEvent instanceof MJaxControlBase) ||
				($objEvent instanceof MJaxFormBase)
			) &&
			(is_string($objAction))
		){
			$ctlAction = $objEvent;
			$strFunction = $objAction;
			$objAction = new MJaxServerControlAction($ctlAction, $strFunction);
			$objEvent = new MJaxClickEvent();
			
		}
        $objEvent->Init($this, $objAction);
        if(!array_key_exists($objEvent->EventName, $this->arrEvents)){
            $this->arrEvents[$objEvent->EventName] = array();
        }
        $this->arrEvents[$objEvent->EventName][] = $objEvent;
    }
    public function RemoveAllActions($mixEvent){
        if($mixEvent instanceof MJaxEventBase){
            $strEvent= $mixEvent->EventName;
        }elseif(is_string($mixEvent)){
            $strEvent = $mixEvent;
        }else{
            throw new Exception("RemoveAllActions method must take either a string or a MJaxEvent for the 1st parameter");
        }
        $this->blnModified = true;
        $this->arrEvents[$strEvent] = array();
        $this->blnRenderClearActions = true;
    }
    public function TriggerEvent($strEvent){
        foreach($this->arrEvents as $arrSubEvents){
            foreach($arrSubEvents as $objEvent){
                if($objEvent->GetEventName() == $strEvent){
                    $objEvent->Exicute();
                }
            }
        }
    }
    public function AddChildControl($objControl){
        $this->arrChildControls[$objControl->ControlId] = $objControl;
    }
    public function ApplyPlugin($objPlugin){
        $objPlugin->SetTargetControl($this);
        $this->arrPlugins[] = $objPlugin;
    }
    public function Animate($mixProperties){
        $this->ApplyPlugin(new MJaxAnimatePlugin($this->objForm, $mixProperties));
    }
    public function Render(){
        $this->blnModified = false;
       	if(is_null($this->objForm)){
       		throw new Exception($this->ControlId);
       	}
        if($this->objForm->CallType == MJaxCallType::None){
            return '';//$this->RenderJSCalls();
        }else{
            return '';
        }
    }
    public function RenderWithName($blnPrint = true){
    	$strRendered = sprintf('<label for="%s">%s</label>', $this->strControlId, $this->strName);
    	$strRendered .= $this->Render(false);
    	if($blnPrint){
    		_p($strRendered, false);
    	}else{
    		return $strRendered;
    	}
    }
    public function RenderJSCalls($blnAjaxFormating = false){
        $strDocumentReady = '';
        
        foreach($this->arrEvents as $arrSubEvents){
            
            foreach($arrSubEvents as $objEvent){
                $strDocumentReady .= $objEvent->Render();
            }
        }
        foreach($this->arrPlugins as $objPlugin){
           
        }
        $strRendered = '';
        if((strlen($strDocumentReady) > 0) || ($this->blnRenderClearActions)){
        	$strEventJS = '';
        	foreach($this->arrEvents as $intIndex => $arrEvents){
        		foreach($arrEvents as $intIndex => $objEvent){
            		$strEventJS = sprintf("$('body').off('%s', '#%s'); ", $objEvent->EventName, $this->strControlId) ;
				}
			}
			$strDocumentReady = $strEventJS . $strDocumentReady;
            if(!$blnAjaxFormating){
                $strRendered .= "<script language='javascript'>";
            }
            $strRendered .= sprintf("$(document).ready(function(){%s});\n", $strDocumentReady);
            if(!$blnAjaxFormating){
                $strRendered .= "</script>";
            }
            $this->blnRenderClearActions = false;
        }
        return $strRendered;

    }
    public function ParsePostData(){ }
    /*-----Events -----*/
    public function Create_Controls(){ }
    /*-----Events End------*/
    public function AddCssClass($mixCssClass){
        if($mixCssClass instanceof MJaxCssClass){
            $this->blnModified = true;
            $this->arrCssClasses[$mixCssClass->ClassName] = $mixCssClass;
        }elseif(is_string($mixCssClass)){
            $this->blnModified = true;
            $this->arrCssClasses[$mixCssClass] = MJaxApplication::CssClass($mixCssClass);
        }else{
            throw new Exception("AddCssClass must have either a string or a MJaxCssClass passed in as the first argument");
        }
    }
    public function RemoveCssClass($mixCssClass){
        if($mixCssClass instanceof MJaxCssClass){
            $strCssClassName = $mixCssClass->ClassName;
        }elseif(is_string($mixCssClass)){
            $strCssClassName = $mixCssClass;
        }else{
            throw new Exception("RemoveCssClass must have either a string or a MJaxCssClass passed in as the first argument");
        }
        if(key_exists($strCssClassName, $this->arrCssClasses)){
            unset($this->arrCssClasses[$strCssClassName]);
            $this->blnModified = true;
            return true;
        }else{
            return false;
        }
    }

    public function Attr($strName, $strValue = false){
        if($strValue === false){//This is false for a reason
            if(array_key_exists($strName, $this->arrAttr)){
                return $this->arrAttr[$strName];
            }else{
                return null;
            }
        }else{
        	if(!is_null($strValue)){
            	$this->arrAttr[$strName] = $strValue;
			}else{
				unset($this->arrAttr[$strName]);
			}
            $this->blnModified = true;
			
            return true;
        }
    }
    protected function GetAttrString(){
        $strAttr = '';
        $strAttr .= $this->objStyle->__toAttr();
        $strAttr .= ' ';
        foreach($this->arrAttr as $strName=>$strValue){
            $strAttr .= sprintf("%s='%s' ", $strName, $strValue);
        }
        if(count($this->arrCssClasses) > 0){
            $strAttr .= "class='";
            foreach($this->arrCssClasses as $cssClass){
                $strAttr .= $cssClass->ClassName . " ";
            }
            $strAttr .= "' ";
        }
        return $strAttr;
    }
    public function Remove(){


        if(!is_null($this->objParentControl)){
            unset($this->objParentControl->arrChildControls[$this->strControlId]);
        }
    	$this->objForm->RemoveControl($this->strControlId);
    }
    public function RemoveAllChildControls(){
        foreach($this->arrChildControls as $strControlId => $objChildControl){
            $objChildControl->Remove();
        }
    }
    /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
    	
		
        switch ($strName) {
            case "ControlId": return $this->strControlId;
            case "ParentControl": return $this->objParentControl;
            case "Form": return $this->objForm;
            case "ActionParameter": return $this->strActionParameter;
            case "Text": return $this->strText;
            case "AutoRenderChildren": return $this->blnAutoRenderChildren;
            case "Style": return $this->objStyle;
            case "Template": return $this->strTemplate;
            case "Modified": return $this->blnModified;
            case "ChildControls": return $this->arrChildControls;
            case "Name": return $this->strName;
            case "Events": return $this->arrEvents;
            default:
               throw new Exception("No property with name '" . $strName . "'");
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        $this->blnModified = true;
		
        switch ($strName) {
            case "ControlId":
                return ($this->strControlId = $mixValue);
            case "ActionParameter":
				return ($this->strActionParameter = $mixValue);
               
            case "Text":           
                return ($this->strText = $mixValue);
               
          	case "Name":
                return ($this->strName = $mixValue);
            case "AutoRenderChildren":
                return ($this->blnAutoRenderChildren = $mixValue);
            case "Style":
               return ($this->objStyle = $mixValue);
                
            case "Template":
                return ($this->strTemplate = $mixValue);
                
            case "Modified":
               return ($this->blnModified = $mixValue);
            default:			   
               throw new Exception("No property '" . $strName . "'");
        }
    }
	public function AddClickEvent(){
		if(!is_null($this->objParentControl)){
			$objParentControl = $this->objParentControl;
		}else{
			$objParentControl = $this->objForm;
		}
		$this->AddAction($objParentControl, $this->strControlId . '_click');
	}
	public function __call($strName, $arrArguments){
		
		foreach(self::$arrExtensions as $intIndex => $objExtension){
			//_dp($objExtension);
			if(method_exists($objExtension, $strName)){
				
				$objExtension->SetControl($this);
				return  call_user_func_array(array($objExtension, $strName), $arrArguments); 
				//return $objExtension->__call($strName, $arrArguments);
			}
		}
	    if(
	    	(method_exists($this, $strName)) 
		){ 
	        return call_user_func_array(
	          	array($this, $strName),
	            $arrArguments
			); 
        }else{ 
        	throw new MJaxExtensionMissingPropertyException(
	          	sprintf(
	          		'The required method "%s" does not exist for %s',
	          		$strName,
	          		get_class($this)
				)
			); 
        } 
	}
    public function After($mixHtml){
        $this->objForm->After(
            '#' . $this->strControlId,
            $mixHtml
        );
    }
    public function Before($mixHtml){
        $this->objForm->Before(
            '#' . $this->strControlId,
            $mixHtml
        );
    }
    public function Append($mixHtml){
        $this->objForm->Append(
            '#' . $this->strControlId,
            $mixHtml
        );
    }
    public function Prepend($mixHtml){
        $this->objForm->Prepend(
            '#' . $this->strControlId,
            $mixHtml
        );
    }
    public function ReplaceWith($mixControl, $mixHtml){
        $this->objForm->ReplaceWith(
            '#' . $this->strControlId,
            $mixHtml
        );
    }

	public static function AddExtension($objExtension){
		self::$arrExtensions[] = $objExtension;
	}

}
?>
