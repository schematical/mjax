</div>


    <!-- Analytics
    ================================================== -->
   


	
       <?php 
			//$strFormState = sprintf('<input type="hidden" name="%s" id="%s" value="%s" />', MJaxFormPostData::MJaxForm__FormState, MJaxFormPostData::MJaxForm__FormState, MJaxForm::Serialize($this));
			//echo($strFormState);
		?>		        
        <?php $this->RenderClassJSCalls(); ?>
        <?php $this->RenderControlRegisterJS(); ?>
        <!---START:MISC JS CALLS--->
        <?php $this->RenderMiscJSCalls(); ?>
        <!---END:MISC JS CALLS--->
    </body>
</html>