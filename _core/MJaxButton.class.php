<?php
/* 
 * This is a simple button class for use with the mjax framework
 */
class MJaxButton extends MJaxControl{

    public function Render($blnPrint = true){
        //Render Actions first if applicable
        $strRendered = parent::Render();
        
        $strRendered .= sprintf("<input id='%s' name='%s' type='button' value='%s' %s></input>", $this->strControlId, $this->strControlId, $this->strText,  $this->GetAttrString());
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function SetValue($mixValue){
        return false;
    }
    public function GetValue(){
        return null;
    }
}
?>
