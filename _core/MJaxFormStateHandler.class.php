<?php
	/**
	 * This is the "standard" FormState handler, storing the base64 encoded session data
	 * (and if requested by QForm, encrypted) as a hidden form variable on the page, itself.
	 */
	class MJaxFormStateHandler{
		public static function Save($strFormState, $blnBackButtonFlag) {
			// Compress (if available)
			if (function_exists('gzcompress'))
				$strFormState = gzcompress($strFormState, 9);

			if (is_null(MJaxForm::$EncryptionKey)) {
				// Don't Encrypt the FormState -- Simply Base64 Encode it
				$strFormState = base64_encode($strFormState);

				// Cleanup FormState Base64 Encoding
				$strFormState = str_replace('+', '-', $strFormState);
				$strFormState = str_replace('/', '_', $strFormState);
			} else {
				
			}
			return $strFormState;
		}

		public static function Load($strPostDataState) {
			$strSerializedForm = $strPostDataState;

			if (is_null(MJaxForm::$EncryptionKey)) {
				// Cleanup from FormState Base64 Encoding
				$strSerializedForm = str_replace('-', '+', $strSerializedForm);
				$strSerializedForm = str_replace('_', '/', $strSerializedForm);
				
				$strSerializedForm = base64_decode($strSerializedForm);
			} else {
				
			}

			// Uncompress (if available)
			if (function_exists('gzcompress'))
				$strSerializedForm = gzuncompress($strSerializedForm);

			return $strSerializedForm;
		}
	}
?>