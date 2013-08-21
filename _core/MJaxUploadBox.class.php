<?php
/* 
 * This is a basic ajax text box
 */
class MJaxUploadBox extends MJaxControl{
	const Validate_Image = '/^(image\/bmp|image\/gif|image\/jpeg|image\/png|image\/tiff)$/i';

    protected $strValidate = null;
    protected $intMaxFilesize = 1048576;
	protected $arrFileData = null;
    public function __construct($objParentControl,$strControlId = null) {
        parent::__construct($objParentControl,$strControlId); 
		$this->objForm->AddHeaderAsset(new MJaxJSHeaderAsset(
            __MJAX_CORE_ASSET_URL__ .'/js/MJax.UploadBox.js'
        ));
		$this->objForm->AddJSCall(
			sprintf(
				'$(function(){ MJax.UploadBox.Init("#%s"); });',
				$this->strControlId
			)
		);
    }
    public function Render($blnPrint = true){
        $strRendered = parent::Render();
		
		$this->strName = $this->strControlId;
        $strRendered .= sprintf("<input id='%s' name='%s' type='file' value='%s' %s />", $this->strControlId, $this->strName, $this->strText,  $this->GetAttrString());
		
		  
        if($blnPrint){
            echo($strRendered);
        }else{
            return $strRendered;
        }
    }
    public function ParsePostData() {
		// Check to see if this Control's Value was passed in via the POST data
		if (array_key_exists($this->strControlId, $_FILES)) {
			$this->arrFileData = $_FILES[$this->strControlId];			
		}else{
			$this->arrFileData = null;			
		}
	}

        /////////////////////////
		// Public Properties: GET
		/////////////////////////
		public function __get($strName) {
			switch ($strName) {
                /*
				// APPEARANCE
				case "Columns": return $this->intColumns;
				case "Text": return $this->strText;
				case "LabelForReMJaxuired": return $this->strLabelForReMJaxuired;
				case "LabelForReMJaxuiredUnnamed": return $this->strLabelForReMJaxuiredUnnamed;
				case "LabelForTooShort": return $this->strLabelForTooShort;
				case "LabelForTooShortUnnamed": return $this->strLabelForTooShortUnnamed;
				case "LabelForTooLong": return $this->strLabelForTooLong;
				case "LabelForTooLongUnnamed": return $this->strLabelForTooLongUnnamed;

				// BEHAVIOR
				case "MaxLength": return $this->intMaxLength;
				case "MinLength": return $this->intMinLength;
				case "ReadOnly": return $this->blnReadOnly;
				case "Rows": return $this->intRows;
                 */
                case "CrossScripting": return $this->strCrossScripting;
				case "TextMode": return $this->strTextMode;
				case "FileData": return $this->arrFileData;

				default:
						return parent::__get($strName);
			}
		}
        	///////////////////////// MJaxType::String)
		// Public Properties: SET
		/////////////////////////
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
        return false;$this->arrFileData = $mixValue;
    }
    public function GetValue(){
       /* if(
            (!is_null($this->arrFileData)) &&
            (array_key_exists(''))*/
        return $this->arrFileData;
    }
    
}
?>
