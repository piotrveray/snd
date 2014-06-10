<?php

class Sniadaniowo_Blockeddates_BlockeddatesController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        
        $adr= array();
        $csv = Mage::getBaseDir() .'/var/csv/dates.csv';
        if(!file_exists($csv)){
            if (($handle = fopen($csv, "w")) !== FALSE) {
                fclose($handle);
            }
        }
        if(isset($_POST['date'])){
            $csvn = '';
            for($i = 1; $i < count($_POST['date']); $i++){
                $csvn .= $_POST['date'][$i].';';
                $csvn .= "\n";
            }
            if (($handle = fopen($csv, "w")) !== FALSE) {
                fwrite($handle, $csvn);
                fclose($handle);
            }
            
        }
        
        if (($handle = fopen($csv, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                $adr[] = array(
                    'date' => $data[0],
                );
            }
            fclose($handle);
        }
        
        $text = '<form method="post">
            <table id="address_wrapper" style="display:none;"><tbody>
            <tr class="option-row">
    <td><input name="date[]" value="" class="input-text required-option" type="text"></td>
    <td class="a-left" id="delete_button_container_option_0"><input type="hidden" class="delete-flag" name="option[delete][option_0]" value=""><button id="id_0cf75a7ec29f7ef728d1e4f731e545b1" title="Delete" type="button" class="scalable delete delete-option" onclick="jQuery(this).parents(\'tr.option-row\').remove();" style=""><span><span><span>Delete</span></span></span></button></td>
    </tr></tbody>
</table>
<div class="entity-edit" id="matage-options-panel">
    <div class="entry-edit-head">
    <h4 class="icon-head head-edit-form fieldset-legend">Nieaktywne dni</h4>
    </div>
    <div class="box">
        <div class="hor-scroll">
            <table class="dynamic-grid" cellspacing="0" cellpadding="0">
                <tbody id="addresses"><tr id="attribute-options-table">
                                            <th style="width:175px;">Data (format YYYY-MM-DD)</th>
                        <th>
                        <button id="add_new_option_button" title="Add Option" type="button" class="scalable add" onclick="" style=""><span><span><span>Add Option</span></span></span></button>                                                    </th>
                    </tr>
                    ';
        
        foreach($adr as $a){
$text .= '<tr class="option-row">
    <td><input name="date[]" value="'.$a['date'].'" class="input-text required-option" type="text"></td>
    <td class="a-left" id="delete_button_container_option_0"><input type="hidden" class="delete-flag" name="option[delete][option_0]" value=""><button id="id_0cf75a7ec29f7ef728d1e4f731e545b1" title="Delete" type="button" class="scalable delete delete-option" onclick="jQuery(this).parents(\'tr.option-row\').remove();" style=""><span><span><span>Delete</span></span></span></button></td>
    </tr>';
        
        }

$text .= '
                    
            </tbody></table>
        </div>
        <input type="hidden" id="option-count-check" value="1">
    </div>
    <button title="Zapisz" type="submit" class="scalable" onclick="" style=""><span><span><span>Zapisz</span></span></span></button>
</div>
<input name="form_key" type="hidden" value="'.Mage::getSingleton('core/session')->getFormKey().'" />
</form>
<script type="text/javascript" src="'.Mage::getBaseUrl('js').'jquery/jquery.js"></script>
<script type="text/javascript">
console.log(\'csd\');
console.log(jQuery(\'#add_new_option_button\').length);
jQuery(\'#add_new_option_button\').click(function(){
console.log(\'csd\');
    jQuery(\'tbody#addresses\').append(jQuery(\'#address_wrapper tbody\').html());
});
</script>
';
        
        $block = $this->getLayout()
        ->createBlock('core/text')
        ->setText($text);           
        $this->_addContent($block);
        
        $this->renderLayout();
        
    }
}

?>