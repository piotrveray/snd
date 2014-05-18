
function jQueryValidation()
{
 
        var is_ok = true;
        
        jQuery("input.required-entry").each(function(){
            if(jQuery(this).is(":hidden") == false)
            {
                var varid = jQuery(this).attr('id');
                jQuery('#label_err_' + varid).remove();
                
                if(jQuery(this).val().length > 0){
                    jQuery(this).removeClass('validation-failed');
                }
                else{
                    jQuery(this).addClass('validation-failed');
                    jQuery(this).after('<div id="label_err_' + varid + '" class="validation-advice">To pole jest wymagane</div>');
                    
                    is_ok = false;
                }
            }
        });
        
        return is_ok;

}
