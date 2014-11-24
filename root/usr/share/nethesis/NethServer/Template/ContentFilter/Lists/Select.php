<?php

echo $view->fieldset()->setAttribute('template', $T('Lists_selector'))
    ->insert($view->radioButton('Lists', 'shalla'))
    ->insert($view->radioButton('Lists', 'urlblacklist'))
    ->insert($view->radioButton('Lists', 'toulouse'))
    ->insert($view->fieldsetSwitch('Lists', 'custom')
        ->setAttribute('uncheckedValue', '')
        ->insert($view->textInput('server')));

echo $view->buttonList()
    ->insert($view->button('save_and_download', $view::BUTTON_SUBMIT))
    ->insert($view->button('Download', $view::BUTTON_LINK)->setAttribute('value', $view->getModuleUrl('../Download')))
    ->insert($view->button('Help', $view::BUTTON_HELP))
;
