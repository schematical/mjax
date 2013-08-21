<?php
class MJaxTableRow extends MJaxControl{
	 protected $arrData = array();

     public $lnkEdit = null;
     public $arrEditControls = array();
     public function IsSelected(){
         $objRow = $this->objParentControl->SelectedRow;
         if(
             (!is_null($objRow)) &&
            ($objRow->ControlId == $this->strControlId)
         ){
             return true;
         }
         return false;
     }
	 public function AddData($mixData, $strPropName = null){
	 	if(!is_null($strPropName)){
			$this->arrData[$strPropName] = $mixData;	
		}else{
			$this->arrData[] = $mixData;
		}	
	 }
	 public function GetData($strPropName = null){
         if(is_null($strPropName)){
             return $this->arrData;
         }
        if(!array_key_exists($strPropName, $this->arrData)){
            return null;
        }
	 	return $this->arrData[$strPropName];
	 }
	 public function Render($blnPrint = true, $blnRenderAsAjax = false){
	 	//After acomidating for the base data we need to acomidate for  the controls as well
	 	if($blnRenderAsAjax){
            $strElementOverride = 'control';
            $this->Attr('transition', $this->strTransition);
        }else{
            $strElementOverride = 'tr';
        }
        $strRendered = parent::Render();
        $strHeader = sprintf("<%s id='%s' name='%s' %s>\n", $strElementOverride, $this->strControlId, $this->strControlId, $this->GetAttrString());
		foreach($this->objParentControl->GetColumns() as $strKey => $objColumn){



                $strRendered .= $objColumn->Render($this);



		}
		$strFooter = sprintf("</%s>", $strElementOverride);
        if(!$blnRenderAsAjax){
            $strRendered = $strHeader . $strRendered . $strFooter;
        }else{
            $strRendered = $strHeader . MLCApplication::XmlEscape(trim($strRendered)) . $strFooter;
        }
		$this->blnModified = false;
		if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
	 }
    public function InitEditControls(){
        //$objRow->RemoveAllActions('click');
        $this->lnkEdit = new MJaxLinkButton($this);
        $this->lnkEdit->AddCssClass('btn');
        $this->lnkEdit->Text = 'Edit';
        $this->lnkEdit->ActionParameter = $this->ActionParameter;

        $this->lnkEdit->AddAction($this, 'lnkEdit_click');
        $this->AddData(
            $this->lnkEdit, 'edit'
        );
    }
    public function lnkEdit_click(){
        //Decided weither it is an edit init or an edit save event
        if(!$this->IsSelected()){
            $this->objParentControl->SelectedRow = $this;
            $this->objForm->TriggerControlEvent(
                $this->objParentControl->ControlId,
                'mjax-table-edit-init'
            );

            $this->lnkEdit->Text = 'Save';
            $this->blnModified = true;
        }else{
            foreach($this->arrEditControls as $strKey => $ctlEdit){
                if(!is_object($this->arrData[$strKey])){
                    $this->arrData[$strKey] = $ctlEdit->GetValue();
                }
            }
            $this->objForm->TriggerControlEvent(
                $this->objParentControl->ControlId,
                'mjax-table-edit-save'
            );
            $this->objParentControl->SelectedRow = null;
            //Remove all edit controls
            /*foreach($this->arrEditControls as $strKey => $ctlEdit){
                $ctlEdit->Remove();
                unset($this->arrEditControls[$strKey]);
            }*/

            $this->lnkEdit->Text = 'Edit';
            $this->blnModified = true;
        }
        //trigger event accordingly


    }
    public function UpdateEntity(BaseEntity $objEntity){
        foreach($this->arrData as $strKey => $mixValue){
            if(!is_object($mixValue)){
                try{
                    $objEntity->__set($strKey, $mixValue);
                }catch(MLCMissingPropertyException $e){
                    //do nothing

                }
            }
        }

        $objEntity->Save();
        if(!array_key_exists($objEntity->getPKey(), $this->arrData)){
            $this->arrData[$objEntity->getPKey()] = $objEntity->getId();
        }
    }



}
