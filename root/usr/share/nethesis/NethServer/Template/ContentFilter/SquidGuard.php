<?php
// send some translated strings to the javascript context:
$view->includeTranslations(array(
    'Block source IP',
    'Block domain',
    'Block URL',
    'Add blocked IP',
    'Add blocked domain',
    'Add blocked URL',
    'Do not filter source IP',
    'Allow domain',
    'Allow URL',
    'Delete',
    'Add unfiltered IP',
    'Add allowed domain',
    'Add allowed URL'
));

$view->includeFile('NethServer/Js/nethserver.collectioneditor.squidguard-allow.js');
$view->includeFile('NethServer/Js/nethserver.collectioneditor.squidguard-deny.js');
$view->includeFile('NethServer/Css/nethserver.collectioneditor.squidguard.css');

$bip = $view->fieldsetSwitch('BlockIpAccess', 'enabled',  $view::FIELDSETSWITCH_CHECKBOX)
        ->setAttribute('template', $T('BlockIpAccess'))
        ->setAttribute('uncheckedValue', 'disabled');

$expr = $view->fieldsetSwitch('Expressions', 'enabled',  $view::FIELDSETSWITCH_CHECKBOX)
        ->setAttribute('template', $T('Expressions'))
        ->setAttribute('uncheckedValue', 'disabled');

$bacl = $view->fieldset('', $view::FIELDSET_EXPANDABLE)->setAttribute('template', $T('Block_label'))
->insert(
         $view->collectionEditor('BlockAcl', $view::LABEL_NONE)
                 ->setAttribute('class', 'DenyAclList')
                 ->setAttribute('dimensions', '20x30')
        );

$aacl = $view->fieldset('', $view::FIELDSET_EXPANDABLE)->setAttribute('template', $T('Allow_label'))
->insert(
         $view->collectionEditor('AllowAcl', $view::LABEL_NONE)
                 ->setAttribute('class', 'AllowAclList')
                 ->setAttribute('dimensions', '20x30')
        );


echo $view->fieldsetSwitch('status', 'enabled',  $view::FIELDSETSWITCH_CHECKBOX)
        ->setAttribute('template', $T('SquidGuard_status'))
        ->setAttribute('uncheckedValue', 'disabled');

echo $bip;
echo $expr;
echo $view->textInput('BlockedFileTypes')->setAttribute('placeholder','exe,zip');
echo $bacl;
echo $aacl;

$status = $view->getClientEventTarget('status');
$ball = $view->getClientEventTarget('BlockAll');
$acat = $view->getClientEventTarget('AllowedCategories');
$bcat = $view->getClientEventTarget('BlockedCategories');

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
