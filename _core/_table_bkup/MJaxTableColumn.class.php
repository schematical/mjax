<?php
class MJaxTableColumn{
    protected $strKey = null;
    protected $strTitle = null;
    protected $objRenderObject = null;
    protected $strRenderFunction = null;
    public function __construct($strKey, $mixTitle, $objRenderObject = null, $strFunction = null){
        $this->strKey = $strKey;

        $this->strTitle = $mixTitle;

        $this->objRenderObject = $objRenderObject;
        $this->strRenderFunction = $strFunction;

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
                $strRendered .= '<td>' . $strHtml . '</td>';
            }elseif(!is_null($this->objRenderObject)){
                $strHtml = $this->objRenderObject->{$this->strRenderFunction}($mixData, $objRow, $this);
                $strRendered .= '<td>' . $strHtml . '</td>';
            }else{
                $strRendered .= '<td>' . $mixData . '</td>';
            }

        }else{
            $strRendered .= '<td>&nbsp;</td>';
        }
        return $strRendered;
    }
    public function GetTitle(){
        return $this->strTitle;
    }
}