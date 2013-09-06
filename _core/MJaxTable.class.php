<?php
/* 
 * This is a basic dif for use with the MJax Framework
 */
class MJaxTable extends MJaxControl{
    protected $rowSelected = null;
    protected $colSelected = null;
    /*--------Expiremental-----------------*/
    protected $rowNewSelected = null;
    protected $colNewSelected = null;
    /*--------End Expirmental------------*/
	protected $strFooterText = null;
    protected $arrColumnTitles = array();
	protected $arrDataEntites = array();
    protected $strEditMode = 'none';
    protected $strRealEvent = null;
    public function __construct($objParentControl, $strControlId = null){
        parent::__construct($objParentControl, $strControlId);
        $this->AddCssClass('mjax-table');
    }
	public function GetColumns(){
		return $this->arrColumnTitles;
	}
	public function AddRow($arrColumnData = null){
		$objRow = new MJaxTableRow($this);
		//TODO: Possibly allow user to set default row styling
		if(!is_null($arrColumnData)){
            if(
                (is_object($arrColumnData)) &&
                ($arrColumnData instanceof BaseEntity)
            ){
                $mixEntity = $arrColumnData;
                $arrColumnData = array();

                foreach($this->arrColumnTitles as $strKey => $objColumn){

                    try{
                        $arrColumnData[$strKey] = $mixEntity->$strKey;
                    }catch(Exception $e){
                        //Do nothing right now
                    }

                }
                $arrColumnData['_entity'] = $mixEntity;

            }
            if(is_array($arrColumnData)){
                foreach($arrColumnData as $strPropName => $strData){
                    $objRow->AddData($strData, $strPropName);
                }
            }else{
                throw new Exception('Invalid row data passed in');
            }
		}
        $this->blnModified = true;
		return $objRow;
	}

	public function SetDataEntites($arrDataEntites){

		//$this->strDataMode = MJaxTableDataMode::DATA_ENTITY;
		if(!is_array($arrDataEntites)){
            if($arrDataEntites instanceof BaseEntityCollection){
			    $this->arrDataEntites = $arrDataEntites->GetCollection();
            }else{
                $this->arrDataEntites = array($arrDataEntites);
            }
		}else{
			$this->arrDataEntites = $arrDataEntites;
		}
        //_dv($arrDataEntites);
		$arrColumnData = array();
		foreach($this->arrDataEntites as $objEntity){

        	foreach($this->arrColumnTitles as $strKey => $objColumn){

                try{
        		    $arrColumnData[$strKey] = $objEntity->$strKey;
                }catch(MLCMissingPropertyException $e) {}

			}
            $arrColumnData['_entity'] = $objEntity;
			$objRow = $this->AddRow($arrColumnData);
			$objRow->ActionParameter = $objEntity->GetId();
			//_dp($objRow);
		}
        $this->RefreshControls();
		$this->blnModified = true;
		
	}
	public function AddColumn($strKey, $strTitle){//, $objRenderObject = null, $strRenderFunction = null, $strEditControl = 'MJaxTextBox'){

        if($strTitle instanceof MJaxTableColumn){
            $this->arrColumnTitles[$strKey] = $strTitle;
        }else{
            $this->arrColumnTitles[$strKey] = new MJaxTableColumn($this, $strKey, $strTitle);//, $objRenderObject, $strRenderFunction, $strEditControl);
        }
        return $this->arrColumnTitles[$strKey];
	}
    public function RemoveColumn($strKey){
        if(array_key_exists($strKey, $this->arrColumnTitles)){
            unset($this->arrColumnTitles[$strKey]);
            return true;
        }
        return false;
    }
    public function Render($blnPrint = true, $blnRenderAsAjax = false){

        if($blnRenderAsAjax){
            $strElementOverride = 'control';
            $this->Attr('transition', $this->strTransition);
        }else{
            $strElementOverride = 'table';
        }
        $this->arrAttr['data-edit-mode'] = $this->strEditMode;
        $strRendered = parent::Render();
        $strHeader = sprintf("<%s id='%s' name='%s' %s>\n", $strElementOverride, $this->strControlId, $this->strControlId, $this->GetAttrString());
        //If template is set render template
        if(!is_null($this->strTemplate)){
          $strRendered .= $this->RenderFromTempate();
        }else{
          $strRendered .= $this->RenderDefault();
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
	public function RenderFromTemplate(){
	  	if(!file_exists($this->strTemplate)){
            throw new Exception("Template file (" . $this->strTemplate .") does not exist");
        }
        global $_CONTROL;
        $objPrevControl = $_CONTROL;
        $_CONTROL = $this;
        $_FORM = $this->objForm;
        $strRendered = $this->objForm->EvaluateTemplate($this->strTemplate);
        $_CONTROL = $objPrevControl;
		return $strRendered;
	}
	public function RenderDefault(){

		$strRendered = '<thead>';
		$strRendered .= '<tr>';
		foreach($this->arrColumnTitles as $strTitle => $mixProp){
			$strRendered .= sprintf(
                '<th scope="col" class="rounded-company">%s%s</th>',
                ($mixProp->IsSelected()?'<i class="icon-star"></i>':''),
                $mixProp->GetTitle()
            );
		}

		$strRendered .= '</tr>';
		$strRendered .= '</thead>';
		$strRendered .= '<tbody>';
        foreach($this->arrChildControls as $objChildControl){
            $strRendered .= $objChildControl->Render(false);
        }
		
		$strRendered .= '<tbody>';
        $strRendered .= sprintf(
        				'<tfoot>
							<tr>
								<td colspan="%s" class="rounded-foot-left">%s</td>
							</tr>
						</tfoot>',
						count($this->arrColumnTitles),
						$this->strFooterText
		);
		return $strRendered;
	}
	/////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName) {
        switch ($strName) {
            case "Rows": return $this->arrChildControls;
            case "SelectedRow": return $this->rowSelected;
            case "SelectedCol": return $this->colSelected;
            case "EditMode": return $this->strEditMode;
            default:
                return parent::__get($strName);

        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue) {
        $this->blnModified = true;
        switch ($strName) {
            case "Rows": return $this->arrChildControls = $mixValue; //I hope they know what they are doing
            case "SelectedRow": return $this->rowSelected = $mixValue;
            case "SelectedCol": return $this->colSelected = $mixValue;
            case "EditMode": return $this->strEditMode = $mixValue;
            default:
                return parent::__set($strName, $mixValue);
        }
    }

    public function InitRowControl($strKey, $strText, $ctlAction, $funAction, $strCssClasses = 'btn', $strCtlClassName = 'MJaxLinkButton'){

        if(!array_key_exists($strKey, $this->arrColumnTitles)){
            $objColumn = $this->AddColumn($strKey,'');
            $objColumn->ControlClass = $strCtlClassName;
            $objColumn->ControlText = $strText;
            $objColumn->ControlCssClasses = $strCssClasses;
            $objColumn->ControlActionObject = $ctlAction;
            $objColumn->ControlActionFunction = $funAction;
            $this->arrColumnTitles[$strKey] = $objColumn;

        }
        $this->RefreshControls();
        return $this->arrColumnTitles[$strKey];
    }
    public function RefreshControls(){

        foreach($this->Rows as $intIndex => $objRow){
            foreach($this->arrColumnTitles as $strKey => $objColumn){
                //$objRow->RemoveAllActions('click');
                $strCtlClassName = $objColumn->ControlClass;
                if(!is_null($strCtlClassName) && is_null($objRow->GetData($strKey))){
                    $ctlControl = new $strCtlClassName($objRow);
                    $ctlControl->AddCssClass($objColumn->ControlCssClasses);
                    $ctlControl->Text = $objColumn->ControlText;
                    $ctlControl->ActionParameter = $objRow->ActionParameter;
                    if(!is_null($objColumn->ControlActionObject)){
                        $ctlControl->AddAction(
                            new MJaxClickEvent(),
                            new MJaxServerControlAction(
                                $objColumn->ControlActionObject,
                                $objColumn->ControlActionFunction
                            )
                        );
                    }
                    $objRow->AddData(
                        $ctlControl, $strKey
                    );
                }
            }
        }
    }
    public function AddEmptyRow(){
        foreach($this->arrColumnTitles as $strKey => $objColumn){
            $arrColumnData[$strKey] = null;
        }
        $objRow = $this->AddRow($arrColumnData);
        //$objRow->InitEditControls();
        $objRow->ActionParameter = -1;
        $this->rowSelected = $objRow;
        $arrColKeys = array_keys($this->arrColumnTitles);
        $i = 0;
        while(
            (is_null($this->colSelected)) &&
            ($i < count($arrColKeys))
        ){
            if($this->arrColumnTitles[$arrColKeys[$i]]->Editable){
                $this->colNewSelected = $this->arrColumnTitles[$arrColKeys[$i]];
                //$this->colSelected = $this->colNewSelected;
            }
            $i += 1;
        }

        return $objRow;
    }
    public function ParsePostData(){

        if(array_key_exists($this->strControlId. '_data', $_POST)){


            $arrData = $_POST[$this->strControlId . '_data'];



            if(array_key_exists($arrData['row'],$this->arrChildControls)){

                $this->rowNewSelected = $this->arrChildControls[$arrData['row']];

            }else{

                $this->rowNewSelected = null;
            }
            if(array_key_exists($arrData['column_key'],$this->arrColumnTitles)){
                $this->colNewSelected = $this->arrColumnTitles[$arrData['column_key']];
            }else{
                $this->colNewSelected = null;
            }
            $this->blnModified = true;

        }


    }

    public function TriggerEvent($strEvent){
        if(
            (!is_null($this->colSelected)) &&

            (method_exists($this->colSelected, 'UpdateValue'))
        ){
                $this->colSelected->UpdateValue();
        }

        if(
            (is_null($this->rowNewSelected) != is_null($this->rowSelected)) ||
            (
                (!is_null($this->rowNewSelected)) &&
                (!is_null($this->rowSelected)) &&
                ($this->rowSelected->ControlId != $this->rowNewSelected->ControlId)
            )
        ){
            if(!is_null($this->rowSelected)){
                if(
                    (!is_null($this->colSelected)) &&
                    $this->colSelected->Editable
                ){
                    $this->rowSelected->TriggerEvent('mjax-table-edit-save');
                    parent::TriggerEvent('mjax-table-edit-save');
                }
                $this->rowSelected->TriggerEvent('mjax-table-row-blur');
                parent::TriggerEvent('mjax-table-row-blur');
            }

            $this->rowSelected = $this->rowNewSelected;
            $this->rowNewSelected = null;
            if(!is_null($this->rowSelected)){
                $this->rowSelected->TriggerEvent('mjax-table-row-focus');
                parent::TriggerEvent('mjax-table-row-focus');
            }
        }

        if(
            (is_null($this->colSelected)  != is_null($this->colNewSelected)) ||
            ($this->colSelected->Key != $this->colNewSelected->Key)
        ){

            $this->objForm->AddJSCall(
                    '$(function(){  $(".mjax-td-selected input[type!=hidden]").focus(); });'
                    //'$(document).on("mjax-page-load",function(){ alert("x"); $(".mjax-td-selected input[type!=hidden]").focus(); });'
            );
            if(!is_null($this->rowSelected)){
                $this->rowSelected->TriggerEvent('mjax-table-col-blur');
            }
            parent::TriggerEvent('mjax-table-col-blur');


            $this->colSelected = $this->colNewSelected;
            $this->colNewSelected = null;
            if(!is_null($this->rowSelected)){
                $this->rowSelected->TriggerEvent('mjax-table-col-focus');
            }

            if(
                (!is_null($this->colSelected)) &&
                ($this->colSelected->Editable)
            ){
                parent::TriggerEvent('mjax-table-col-focus');
                if(!is_null($this->rowSelected)){
                    $this->rowSelected->TriggerEvent('mjax-table-edit-init');
                }
                parent::TriggerEvent('mjax-table-edit-init');
            }
        }


    }
}
?>