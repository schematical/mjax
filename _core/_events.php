<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class MJaxEventBase{
    protected $blnRendered = false;
    protected $blnOnce = false;
    protected $objControl = null;
    protected $objCssClass = null;
    protected $strSelector = null;
    protected $objAction = null;


    /*---extra post data----*/
    protected $strKeyCode = null;
    
    public function Init($mixTarget, $objAction){
    	
        if($mixTarget instanceof MJaxControl){
            $this->objControl =  $mixTarget;
            $this->strSelector = "#" . $this->objControl->ControlId;
        }elseif($mixTarget instanceof MJaxCssClass){
            $this->objCssClass =  $mixTarget;
            $this->strSelector = "." . $this->objCssClass->ClassName;
        }elseif(is_string($mixTarget)){
            $this->strSelector = $mixTarget;
        }else{
            throw new Exception("Event target must be either a MJaxControl, MJaxCssClass, or string");
        }
        $this->objAction = $objAction;
        $this->objAction->SetEvent($this);
    }
    public function Exicute(){
        if(array_key_exists(MJaxEventPostData::KEYCODE, $_POST)){
            $this->strKeyCode = $_POST[MJaxEventPostData::KEYCODE];
        }
        $this->objControl->Form->ActiveEvent = $this;
        $this->objAction->Exicute($this->objControl->Form, $this->objControl->ControlId, $this->objControl->ActionParameter);
    }
    public function GetEventName(){
        return $this->strEventName;
    }
    public function SetControl($objControl){    	
    	$this->objControl = $objControl;
    }
    public function __get($strName) {
        switch ($strName) {
            case "Once": return $this->blnOnce;
            case "Rendered": return $this->blnRendered;
            case "Selector": return $this->strSelector;
            case "EventName": return $this->strEventName;
            case "Action": return $this->objAction;
            case "KeyCode": return $this->strKeyCode;
            case "Control": return $this->objControl;
            default:
                throw new MLCMissingPropertyException($this, $strName);
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        switch ($strName) {
            case "Once":
                return ($this->blnOnce = QType::Cast($mixValue, QType::Boolean));
            case "Rendered":
                return ($this->blnRendered = QType::Cast($mixValue, QType::Boolean));
            case "KeyCode":
                return ($this->strKeyCode = $mixValue);
            default:
                return parent::__set($strName, $mixValue);

        }
    }
    public function Render(){
        $strRendered = sprintf("$('body').on('%s', '%s', %s);", $this->strEventName,  $this->strSelector, $this->objAction->Render());
        $this->blnRendered = true;
        return $strRendered;
    }
    public function RenderUnbind(){
        if($this->blnOnce){
            $strRendered = sprintf("$(this).unbind('%s');", $this->strEventName);
            return $strRendered;
        }else{
            return '';
        }
    }
}
class MJaxClickEvent extends MJaxEventBase{
    protected $strEventName = 'click';
}
class MJaxEnterKeyEvent extends MJaxEventBase{
    protected $strEventName = 'enter';
}
class MJaxChangeEvent extends MJaxEventBase{
    protected $strEventName = 'change';
}
class MJaxBlurEvent extends MJaxEventBase{
    protected $strEventName = 'blur';
}
class MJaxDblClickEvent extends MJaxEventBase{
    protected $strEventName = 'dblclick';
}
class MJaxFocusEvent extends MJaxEventBase{
    protected $strEventName = 'focus';
}
class MJaxFocusInEvent extends MJaxEventBase{
    protected $strEventName = 'focusin';
}
class MJaxFocusOutEvent extends MJaxEventBase{
    protected $strEventName = 'focusout';
}
class MJaxHoverEvent extends MJaxEventBase{
    protected $strEventName = 'hover';
}
class MJaxKeyDownEvent extends MJaxEventBase{
    protected $strEventName = 'keydown';
}
class MJaxKeyPressEvent extends MJaxEventBase{
    protected $strEventName = 'keypress';
}
class MJaxKeyUpEvent extends MJaxEventBase{
    protected $strEventName = 'keyup';
}
class MJaxMouseDownEvent extends MJaxEventBase{
    protected $strEventName = 'mousedown';
}
class MJaxMouseEnterEvent extends MJaxEventBase{
    protected $strEventName = 'mouseenter';
}
class MJaxMouseLeaveEvent extends MJaxEventBase{
    protected $strEventName = 'mouseleave';
}
class MJaxMouseMoveEvent extends MJaxEventBase{
    protected $strEventName = 'mousemove';
}
class MJaxMouseOutEvent extends MJaxEventBase{
    protected $strEventName = 'mouseout';
}
class MJaxMouseOverEvent extends MJaxEventBase{
    protected $strEventName = 'mouseover';
}
class MJaxMouseUpEvent extends MJaxEventBase{
    protected $strEventName = 'mouseup';
}
class MJaxResizeEvent extends MJaxEventBase{
    protected $strEventName = 'resize';
}
class MJaxScrollEvent extends MJaxEventBase{
    protected $strEventName = 'scroll';
}
class MJaxSelectEvent extends MJaxEventBase{
    protected $strEventName = 'select';
}
class MJaxSubmitEvent extends MJaxEventBase{
    protected $strEventName = 'submit';
}
class MJaxToggleEvent extends MJaxEventBase{
    protected $strEventName = 'toggle';
}
class MJaxTriggerEvent extends MJaxEventBase{
    protected $strEventName = 'trigger';
}
class MJaxUploadEvent extends MJaxEventBase{
    protected $strEventName = 'upload';
}
class MJaxFancyboxCloseEvent extends MJaxEventBase{
    protected $strEventName = 'fancybox-close';
}
class MJaxTimeoutEvent extends MJaxEventBase{
    protected $intMiliseconds = 10000;
    protected $strEventName = 'timeout';
    public function __construct($intMiliseconds){
        $this->intMiliseconds = $intMiliseconds;
    }
    public function Render(){
        $strRendered = sprintf(
            "window.setTimeout(
                %s,
                %s
            );",
            $this->objAction->Render(),
            $this->intMiliseconds
        );
        $this->blnRendered = true;
        return $strRendered;
    }
}

class MJaxBeforeUnloadEvent extends MJaxEventBase{
    protected $strEventName = 'beforeunload';
    public function Render(){
        $strRendered = sprintf(
            "$(window).bind('%s',
                %s
            );",
            $this->strEventName,
            $this->objAction->Render()

        );
        $this->blnRendered = true;
        return $strRendered;
    }
}

/*
 * New table edit event 8/21/13
 */
class MJaxTableEditInitEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-table-edit-init';
}
class MJaxTableEditSaveEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-table-edit-save';
}
class MJaxTableSelectEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-table-select';
}
class MJaxTableRowBlurEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-table-row-blur';
}
class MJaxTableRowFocusEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-table-row-focus';
}
class MJaxTableColBlurEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-table-col-blur';
}
class MJaxTableColFocusEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-table-col-focus';
}

class MJaxSuccessEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-success';
}
class MJaxErrorEvent extends MJaxEventBase{
    protected $strEventName = 'mjax-error';
}
?>
