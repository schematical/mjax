<?php
class MJaxTableColumn{

    protected $strKey = null;
    protected $strTitle = null;
    protected $objRenderObject = null;
    protected $strRenderFunction = null;
    protected $strControlClass = null;
    protected $strEditCtlInitMethod = null;
    public function __construct($strKey, $mixTitle, $objRenderObject = null, $strFunction = null, $strControlClass = 'MJaxTextBox'){
        $this->strKey = $strKey;

        $this->strTitle = $mixTitle;

        $this->objRenderObject = $objRenderObject;
        $this->strRenderFunction = $strFunction;
        if(method_exists($this->objRenderObject, $strControlClass)){
            $this->strEditCtlInitMethod = $strControlClass;
        }elseif(class_exists($strControlClass)){
            $this->strControlClass = $strControlClass;
        }else{
            throw new Exception("Cannot figure out what control class or render method was passed in for \$strControlClass parameter");
        }
    }
    public function Render($objRow){

        $strRendered = '';
        $mixData = $objRow->GetData($this->strKey);
        if(!is_null($this->objRenderObject)){
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
                    //if($objRow->IsSelected()){
                        $strHtml = $this->RenderIndvControl($objRow);
                    //}else{
                        $strHtml = $mixData;
                    //}
                }


            }else{
                //if($objRow->IsSelected()){

                    $strHtml = $this->RenderIndvControl($objRow);
                //}else{
                    $strHtml = '&nbsp;';
                //}

            }
        }
        $strRendered .= '<td>' . $strHtml . '</td>';
        return $strRendered;
    }
    public function GetTitle(){
        return $this->strTitle;
    }
    public function RenderIndvControl($objRow){
        $mixData = $objRow->GetData($this->strKey);
        if(!array_key_exists($this->strKey, $objRow->arrEditControls)){
            if(!is_null($this->strEditCtlInitMethod)){
                $objRow->arrEditControls[$this->strKey] = $this->objRenderObject->{$this->strEditCtlInitMethod}($objRow, $mixData, $this->strKey);
            }elseif(!is_null($this->strControlClass)){
                $strClassName = $this->strControlClass;
                $objRow->arrEditControls[$this->strKey] = new $strClassName(
                    $objRow
                );
            }else{
                //I think this is the remove button
            }
        }
        $objRow->arrEditControls[$this->strKey]->SetValue($mixData);
        $strHtml = $objRow->arrEditControls[$this->strKey]->Render(false);
        return $strHtml;
    }
}