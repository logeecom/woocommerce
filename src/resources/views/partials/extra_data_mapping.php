<?php

use ChannelEngine\Utility\Asset_Helper;
use ChannelEngine\Utility\Shop_Helper;

?>
<script src="<?php echo Asset_Helper::get_js_url( 'ExtraDataMapping.js' ) ?>"></script>
<script src="<?php echo Asset_Helper::get_js_url('ModalService.js') ?>"></script>
<form class="ce-form" onsubmit="return false">
    <div class="ce-extra-data-heading">
        <h3 style="width: 200px"><?php echo __("Extra data mapping") ?></h3>
        <a id="ceAddNewAttribute" class="ce-button-extra-data ce-button__primary ce-button-add-mapping">+</a>
    </div>
    <div id="hidden" class="ce-input-extra-data last" style="display: none;">
        <label>
            <select>
            </select>
            <input type="text" class="small-number-input" maxlength="100" minlength="1"/>
            <a class="ce-button-extra-data ce-button__primary ce-button-remove-mapping">-</a>
        </label>
    </div>
    <input id="ceProductExtraData" type="hidden"
           value="<?php echo Shop_Helper::get_controller_url( 'Config', 'get_extra_data_mappings' ); ?>">
</form>