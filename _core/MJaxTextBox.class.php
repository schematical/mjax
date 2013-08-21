<?php
/* 
 * This is a basic ajax text box
 */
class MJaxTextBox extends MJaxControl{
	const Allow = 'Allow';
	const HtmlEntities = 'HtmlEntities';
    protected $strCrossScripting = null;
    protected $strTextMode = null;
    public function __construct($objParentControl,$strControlId = null, $arrAttrs = null) {
        parent::__construct($objParentControl,$strControlId, $arrAttrs);
        $this->strTextMode = MJaxTextMode::SingleLine;
    }
    public function Render($blnPrint = true){
        $strRendered = parent::Render();
        switch($this->strTextMode){
            case(MJaxTextMode::MultiLine):
                $strRendered .= sprintf("<textarea id='%s' name='%s' %s>%s</textarea>", $this->strControlId, $this->strName, $this->GetAttrString(), $this->strText);
            break;
			default:
				$strRendered .= sprintf(
					"<input id='%s' name='%s' type='%s' value='%s' %s></input>",
					$this->strControlId, 
					$this->strName, 
					$this->strTextMode, 
					$this->strText,  
					$this->GetAttrString()
				);
            	break;
        }
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function ParsePostData() {
			// Check to see if this Control's Value was passed in via the POST data
			if (array_key_exists($this->strControlId, $_POST)) {
				// It was -- update this Control's value with the new value passed in via the POST arguments
                if($this->strText != $_POST[$this->strControlId]){
                    //$this->blnModified = true;
                }
				$this->strText = $_POST[$this->strControlId];

				switch ($this->strCrossScripting) {
					case self::Allow:
						// Do Nothing, allow everything
						break;
					case self::HtmlEntities:
						// Go ahead and perform HtmlEntities on the text
						$this->strText = htmlentities($this->strText);
						break;
					default:
						// Deny the Use of CrossScripts

						// Check for cross scripting patterns
						// TODO: Change this to RegExp
						$strText = strtolower($this->strText);
						if ((strpos($strText, '<script') !== false) ||
							(strpos($strText, '<applet') !== false) ||
							(strpos($strText, '<embed') !== false) ||
							(strpos($strText, '<style') !== false) ||
							(strpos($strText, '<link') !== false) ||
							(strpos($strText, '<body') !== false) ||
							(strpos($strText, '<iframe') !== false) ||
							(strpos($strText, 'javascript:') !== false) ||
							(strpos($strText, ' onfocus=') !== false) ||
							(strpos($strText, ' onblur=') !== false) ||
							(strpos($strText, ' onkeydown=') !== false) ||
							(strpos($strText, ' onkeyup=') !== false) ||
							(strpos($strText, ' onkeypress=') !== false) ||
							(strpos($strText, ' onmousedown=') !== false) ||
							(strpos($strText, ' onmouseup=') !== false) ||
							(strpos($strText, ' onmouseover=') !== false) ||
							(strpos($strText, ' onmouseout=') !== false) ||
							(strpos($strText, ' onmousemove=') !== false) ||
							(strpos($strText, ' onclick=') !== false) ||
							(strpos($strText, '<object') !== false) ||
							(strpos($strText, 'background:url') !== false))
							throw new Exception('Cross site script detected ' . $this->strControlId);
				}
			}
		}

		public function __get($strName) {
			switch ($strName) {             
                case "CrossScripting": return $this->strCrossScripting;
				case "TextMode": return $this->strTextMode;
				default:
					return parent::__get($strName);
			}
		}
		public function Length(){
			return strlen($this->strText);
		}
		public function LengthIsLongerThan($intCompair){
			return  ($this->Length() > $intCompair);
		}
		public function __set($strName, $mixValue) {
			$this->blnModified = true;

			switch ($strName) {
				// APPEARANCE
                case "Placeholder":
						$this->Attr('placeholder', $mixValue);
						break;
				case "Columns":
						$this->Attr('columns', $mixValue);
						break;
				// BEHAVIOR
				case "CrossScripting":
						$this->strCrossScripting = $mixValue;
						break;
				case "MaxLength":
						$this->Attr('minlength', $mixValue);
						break;
				case "MinLength":
						$this->Attr('minlength', $mixValue);
						break;
				case "ReadOnly":
						$this->Attr('readonly', $mixValue?'readonly':'');
						break;
				case "Rows":
						$this->Attr('rows', $mixValue);
						break;
				case "TextMode":
						$this->strTextMode = $mixValue;
					break;
					
            default:
					parent::__set($strName, $mixValue);
					
					break;
			}
		}
    public function SetValue($mixValue){
        return $this->strText = $mixValue;
    }
    public function GetValue(){
        return $this->strText;
    }
    
}
?>
