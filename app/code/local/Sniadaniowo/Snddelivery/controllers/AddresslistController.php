<?php

class Sniadaniowo_Snddelivery_AddresslistController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        
        $adr= array();
        $csv = Mage::getBaseDir() .'/var/csv/adresy.csv';
        if(isset($_POST['city']) && isset($_POST['street']) && isset($_POST['numbers'])){
            $csvn = '';
            for($i = 1; $i < count($_POST['city']); $i++){
                $csvn .= $_POST['city'][$i].';'.$_POST['street'][$i].';'.$_POST['numbers'][$i].';';
                if(isset($_POST['code'])){
                    $csvn .= $_POST['code'][$i].';';
                }
                else{
                    $csvn .= ';';
                }
                if(isset($_POST['codenr'])){
                    $csvn .= $_POST['codenr'][$i].';';
                }
                else{
                    $csvn .= ';';
                }
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
                    'city' => $data[0],
                    'street' => $data[1],
                    'numbers' => $data[2],
                    'code' => $data[3],
                    'codenr' => $data[4],
                );
            }
            fclose($handle);
        }
        
        $text = '<form method="post">
            <table id="address_wrapper" style="display:none;"><tbody>
            <tr class="option-row">
    <td><input name="city[]" value="" class="input-text required-option" type="text"></td>
    <td><input name="street[]" value="" class="input-text required-option" type="text"></td>
    <td><input name="numbers[]" value="" class="input-text required-option" type="text"></td>
    <td><input name="code[]" value="" class="input-text required-option" type="text"></td>
    <td><input name="codenr[]" value="" class="input-text required-option" type="text"></td>
    <td class="a-left" id="delete_button_container_option_0"><input type="hidden" class="delete-flag" name="option[delete][option_0]" value=""><button id="id_0cf75a7ec29f7ef728d1e4f731e545b1" title="Delete" type="button" class="scalable delete delete-option" onclick="jQuery(this).parents(\'tr.option-row\').remove();" style=""><span><span><span>Delete</span></span></span></button></td>
    </tr></tbody>
</table>
<div class="entity-edit" id="matage-options-panel">
    <div class="entry-edit-head">
    <h4 class="icon-head head-edit-form fieldset-legend">Lista adresów</h4>
    </div>
    <div class="box">
        <div class="hor-scroll">
            <table class="dynamic-grid" cellspacing="0" cellpadding="0">
                <tbody id="addresses"><tr id="attribute-options-table">
                                            <th>Miasto</th>
                                            <th>Ulica</th>
                                            <th>Numer domu</th>
                                            <th>Kod do bramy</th>
                                            <th>Nr mieszkania dla kodu</th>
                        <th>
                        <button id="add_new_option_button" title="Add Option" type="button" class="scalable add" onclick="" style=""><span><span><span>Add Option</span></span></span></button>                                                    </th>
                    </tr>
                    ';
        
        foreach($adr as $a){
$text .= '<tr class="option-row">
    <td><input name="city[]" value="'.$a['city'].'" class="input-text required-option" type="text"></td>
    <td><input name="street[]" value="'.$a['street'].'" class="input-text required-option" type="text"></td>
    <td><input name="numbers[]" value="'.$a['numbers'].'" class="input-text required-option" type="text"></td>
    <td><input name="code[]" value="'.$a['code'].'" class="input-text" type="text"></td>
    <td><input name="codenr[]" value="'.$a['codenr'].'" class="input-text" type="text"></td>
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