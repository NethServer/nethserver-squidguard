<?php

echo $view->fieldset()->setAttribute('template', $T('Lists_selector'))
    ->insert($view->radioButton('Lists', 'toulouse'))
    ->insert($view->fieldsetSwitch('Lists', 'custom')
        ->setAttribute('uncheckedValue', '')
        ->insert($view->textInput('CustomListURL')));

echo $view->buttonList()
    ->insert($view->button('save_and_download', $view::BUTTON_SUBMIT))
    ->insert($view->button('Help', $view::BUTTON_HELP))
;
