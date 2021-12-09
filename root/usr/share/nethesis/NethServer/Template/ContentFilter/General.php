<?php
// send some translated strings to the javascript context:
$view->includeTranslations(array(
    'Block domain',
    'Block URL',
    'Add blocked domain',
    'Allow domain',
    'Allow URL',
    'Delete',
    'Add allowed domain'
));

$view->includeFile('NethServer/Js/nethserver.collectioneditor.squidguard-allow.js');
$view->includeFile('NethServer/Js/nethserver.collectioneditor.squidguard-deny.js');
$view->includeFile('NethServer/Css/nethserver.collectioneditor.squidguard.css');

$expr = $view->checkbox('Expressions', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled');

$bacl = $view->fieldset('')->setAttribute('template', $T('Block_label'))
->insert(
         $view->collectionEditor('BlockAcl', $view::LABEL_NONE)
                 ->setAttribute('class', 'DenyAclList')
                 ->setAttribute('dimensions', '20x30')
        );

$aacl = $view->fieldset('')->setAttribute('template', $T('Allow_label'))
->insert(
         $view->collectionEditor('AllowAcl', $view::LABEL_NONE)
                 ->setAttribute('class', 'AllowAclList')
                 ->setAttribute('dimensions', '20x30')
        );

echo $view->checkbox('status', 'enabled')
        ->setAttribute('uncheckedValue', 'disabled');

echo $expr;
echo $view->textInput('BlockedFileTypes')->setAttribute('placeholder','exe,zip');
echo $bacl;
echo $aacl;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);
