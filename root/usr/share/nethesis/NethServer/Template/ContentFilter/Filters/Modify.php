<?php

/* @var $view Nethgui\Renderer\Xhtml */

echo $view->textInput('name', ($view->getModule()->getIdentifier() == 'update' ? $view::STATE_READONLY : 0));
echo $view->textInput('Description');

echo $view->checkBox('BlockIpAccess', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled');

echo $view->checkBox('BlackList', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled');

echo $view->checkBox('WhiteList', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled');

echo $view->checkBox('BlockFileTypes', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled');

echo $view->checkBox('BlockBuiltinRules', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled');

echo $view->selector('BlockAll','disabled');
echo $view->selector('Categories', $view::SELECTOR_MULTIPLE);

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);

$checkboxJson = json_encode((string) $view->checkBox('CheckAll','disabled'));  
$checkboxId = $view->getUniqueId('CheckAll');
$categoriesTarget = $view->getClientEventTarget('Categories');

$view->includeJavascript(" 
(function ( $ ) {
    $(document).ready(function() {
        $('.$categoriesTarget').before($checkboxJson);
        $('.$categoriesTarget').css( 'padding-left', '.8em' );
        $('#$checkboxId').click(function() {
            $('.$categoriesTarget :checkbox').not(this).prop('checked', this.checked);
        });
        $('.$categoriesTarget').on('nethguiupdateview', function (e, value) {
            if ($.isArray(value) && value.length < 2){
                $('#$checkboxId').parent().hide();
            } else {
                $('#$checkboxId').parent().show();
            }
        });
    });
})( jQuery );
");
