<?php
class MJaxTableColumn{

    protected $objTable = null;
    protected $strKey = null;
    protected $strTitle = null;
    protected $objRenderObject = null;
    protected $strRenderFunction = null;
    protected $blnEditable = false;

    protected $strEditControlClass = null;
    protected $strEditCtlInitMethod = null;

    protected $strControlClass = null;
    protected $strControlText = null;
    protected $ctlControlAction = null;
    protected $funControlAction = null;
    protected $strControlCssClasses = 'btn';
    protected $strOrderByField = null;

    protected $blnHidden = false;

    protected $arrHeaderCtls = array();

    public function __construct(MJaxTable $objTable, $strKey, $mixTitle){//, $objRenderObject = null, $strFunction = null, $strEditControlClass = 'MJaxTextBox'){
        $this->objTable = $objTable;
        $this->strKey = $strKey;

        $this->strTitle = $mixTitle;


        $this->InitHeaderCtls();
    }

    public function RenderInner($objRow){
        $strRendered = '';
        $mixData = $objRow->GetData($this->strKey);

        if(!is_null($this->strRenderFunction)){
            $strHtml = $this->objRenderObject->{$this->strRenderFunction}($mixData, $objRow, $this);
        }else{
            if(!is_null($mixData)){
                if(
                    (is_object($mixData)) &&
                    ($mixData instanceof MJaxControl)
                ){
                    $strHtml = $mixData->Render(false);
                }else{
                    //if is in edit mode
                    if($objRow->IsSelected() && $this->IsSelected() && $this->blnEditable && $this->objTable->EditMode == MJaxTableEditMode::INLINE){
                        $strHtml = $this->RenderIndvControl($objRow);
                    }else{
                        $strHtml = $mixData;
                    }
                }


            }else{
                if($objRow->IsSelected() && $this->IsSelected() && $this->blnEditable && $this->objTable->EditMode == MJaxTableEditMode::INLINE){
                    $strHtml = $this->RenderIndvControl($objRow);
                }else{
                    $strHtml = '&nbsp;';
                }

            }
        }
        return $strHtml;
    }
    public function Render($objRow){
        $strRendered = '';
        $strHtml = $this->RenderInner($objRow);
        if($this->IsSelected() && $objRow->IsSelected()){
            $strClassName = 'mjax-td-selected';
        }else{
            $strClassName = 'mjax-td';
        }
        $strRendered .=
            sprintf(
                '<td id="%s_%s" class="%s" data-key="%s" data-editable="%s">%s</td>',
                $objRow->ControlId,
                $this->strKey,
                $strClassName,
                $this->strKey,
                ($this->blnEditable?'true':'false'),
                $strHtml

            );
        return $strRendered;
    }
    public function GetTitle(){
        return $this->strTitle;
    }
    public function UpdateValue(){
        $objRow = $this->objTable->SelectedRow;
        if(array_key_exists($this->strKey, $objRow->arrEditControls)){
            $mixValue = $objRow->arrEditControls[$this->strKey]->GetValue();
            $objRow->SetData(
                $this->strKey,
                $mixValue
            );
        }
    }
    public function RenderIndvControl($objRow){
        $mixData = $objRow->GetData($this->strKey);
        if(!array_key_exists($this->strKey, $objRow->arrEditControls)){
            if(!is_null($this->strEditCtlInitMethod)){
                $objRow->arrEditControls[$this->strKey] = $this->objRenderObject->{$this->strEditCtlInitMethod}($objRow, $mixData, $this->strKey);
            }elseif(!is_null($this->strEditControlClass)){
                $strClassName = $this->strEditControlClass;
                $objRow->arrEditControls[$this->strKey] = new $strClassName(
                    $objRow
                );
                $objRow->arrEditControls[$this->strKey]->SetValue($mixData);
            }else{
                //I think this is the remove button
            }
        }

        $strHtml = $objRow->arrEditControls[$this->strKey]->Render(false);
        return $strHtml;
    }
    public function IsSelected(){
        if(is_null($this->objTable->SelectedCol)){
            return false;
        }
        if($this->objTable->SelectedCol->Key != $this->strKey){
            return false;
        }
        return true;
    }
    public function InitHeaderCtls(){
       /* $this->arrHeaderCtls['hide'] = new MJaxLinkButton($this->objTable->Form);
        $this->arrHeaderCtls['hide']->Text = 'Hide this column';
        $this->arrHeaderCtls['hide']->ActionParameter = $this->strKey;


        $this->arrHeaderCtls['hide']->AddAction(
            $this->objTable,
            'lnkHide_click'
        );*/
    }
    public function SetOrderByField($strField){
        $this->strOrderByField = $strField;
        $this->arrHeaderCtls['order_by_asc'] = new MJaxLinkButton($this->objTable->Form);
        $this->arrHeaderCtls['order_by_asc']->Text = 'OOrder By Ascending';
        $this->arrHeaderCtls['order_by_asc']->ActionParameter = array(
           'field'=>$this->strOrderByField,
           'direction'=>'ASC'
        );
        $this->arrHeaderCtls['order_by_asc']->AddAction(
            $this->objTable,
            'lnkOrderBy_click'
        );
        $this->arrHeaderCtls['order_by_desc'] = new MJaxLinkButton($this->objTable->Form);
        $this->arrHeaderCtls['order_by_desc']->Text = 'OOrder By Desending';
        $this->arrHeaderCtls['order_by_desc']->ActionParameter = array(
            'field'=>$this->strOrderByField,
            'direction'=>'DESC'
        );
        $this->arrHeaderCtls['order_by_desc']->AddAction(
            $this->objTable,
            'lnkOrderBy_click'
        );
    }
    public function RenderHeader(){
        if(count($this->arrHeaderCtls) == 0){
            return $this->GetTitle();
        }
        $strHeaderCtlHtml = '';
        foreach($this->arrHeaderCtls as $lnkCtl){
            $strHeaderCtlHtml .= sprintf(
                '<li>%s</li>',
                $lnkCtl->Render(false)
            );
        }
        $strHtml = sprintf(
            '<div class="btn-group">
                  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    %s
                    <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                    %s
                  </ul>
            </div>',
            $this->GetTitle(),
            $strHeaderCtlHtml
        );
        return $strHtml;
    }


    /////////////////////////
    // Public Properties: GET
    /////////////////////////
    public function __get($strName)
    {
        switch ($strName) {
            case "Key":
                return $this->strKey;
            case "Title":
                return $this->strTitle;
            case "RenderObject":
                return $this->objRenderObject;
            case "RenderFunction":
                return $this->strRenderFunction;

            case "EditControlClass":
                return $this->strEditControlClass;
            case "EditCtlInitMethod":
                return $this->strEditCtlInitMethod;


            case "ControlClass":
                return $this->strControlClass;
            case "ControlText":
                return $this->strControlText;
            case "ControlActionObject":
                return $this->ctlControlAction;
            case "ControlActionFunction":
                return $this->funControlAction;
            case "ControlCssClasses":
                return $this->strControlCssClasses;
            case "Editable":
                return $this->blnEditable;
            case "OrderByField":
                return $this->strOrderByField;
            default:
                //parent::__get($strName);
                throw new Exception("Not porperty exists with name '" . $strName . "' in class " . __CLASS__);
        }
    }

    /////////////////////////
    // Public Properties: SET
    /////////////////////////
    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case "Key":
                return $this->strKey = $mixValue;
            case "Title":
                return $this->strTitle = $mixValue;
            case "RenderObject":
                return $this->objRenderObject = $mixValue;
            case "RenderFunction":
                return $this->strRenderFunction = $mixValue;

            case "EditControlClass":
                return $this->strEditControlClass = $mixValue;
            case "EditCtlInitMethod":
                return $this->strEditCtlInitMethod = $mixValue;


            case "ControlClass":
                return $this->strControlClass = $mixValue;
            case "ControlText":
                return $this->strControlText = $mixValue;
            case "ControlActionObject":
                return $this->ctlControlAction = $mixValue;
            case "ControlActionFunction":
                return $this->funControlAction = $mixValue;
            case "ControlCssClasses":
                return $this->strControlCssClasses = $mixValue;
            case "Editable":
                return $this->blnEditable = $mixValue;
            /*case "OrderByField":
                return $this->strOrderByField = $mixValue;*/
            default:
                //parent::__set($strName, $mixValue);
                throw new Exception("Not porperty exists with name '" . $strName . "' in class " . __CLASS__);
        }
    }

}