<?php
class MJaxTableColumn{

    protected $strKey = null;
    protected $strTitle = null;
    protected $objRenderObject = null;
    protected $strRenderFunction = null;
    protected $strControlClass = null;
    public function __construct($strKey, $mixTitle, $objRenderObject = null, $strFunction = null, $strControlClass = 'MJaxTextBox'){
        $this->strKey = $strKey;

        $this->strTitle = $mixTitle;

        $this->objRenderObject = $objRenderObject;
        $this->strRenderFunction = $strFunction;
        $this->strControlClass = $strControlClass;
        error_log($strControlClass);

    }
    public function Render($objRow){

        $strRendered = '';
        $mixData = $objRow->GetData($this->strKey);
        if(!is_null($mixData)){
            if(
                (is_object($mixData)) &&
                ($mixData instanceof MJaxControl)
            ){
                $strHtml = $mixData->Render(false);
            }elseif(!is_null($this->objRenderObject)){
                $strHtml = $this->objRenderObject->{$this->strRenderFunction}($mixData, $this);
            }else{
                //if is in edit mode
                if($objRow->IsSelected()){
                    if(!array_key_exists($this->strKey, $objRow->arrEditControls)){
                        $strClassName = $this->strControlClass;
                        $objRow->arrEditControls[$this->strKey] = new $strClassName(
                            $objRow
                        );
                    }
                    $objRow->arrEditControls[$this->strKey]->SetValue($mixData);
                    $strHtml = $objRow->arrEditControls[$this->strKey]->Render(false);
                }else{
                    $strHtml = $mixData;
                }
            }


        }else{
            if($objRow->IsSelected()){

                if(!array_key_exists($this->strKey, $objRow->arrEditControls)){
                    $strClassName = $this->strControlClass;
                    $objRow->arrEditControls[$this->strKey] = new $strClassName(
                        $objRow
                    );
                }
                $objRow->arrEditControls[$this->strKey]->SetValue($mixData);
                $strHtml = $objRow->arrEditControls[$this->strKey]->Render(false);
            }else{
                $strHtml = '&nbsp;';
            }

        }
        $strRendered .= '<td>' . $strHtml . '</td>';
        return $strRendered;
    }
    public function GetTitle(){
        return $this->strTitle;
    }
}