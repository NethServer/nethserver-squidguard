<?php

echo $view->textInput('name', ($view->getModule()->getIdentifier() == 'update' ? $view::STATE_READONLY : 0));
echo $view->textInput('Description');
echo $view->selector('Src', $view::SELECTOR_DROPDOWN);
echo $view->selector('Filter');
echo $view->fieldset()->setAttribute('template', $T('Time_label'))
    ->insert($view->radioButton('When', 'always')->setAttribute('label', $T('always_label')))
    ->insert($view->fieldsetSwitch('When', 'rules', $view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->selector('Time', $view::SELECTOR_MULTIPLE | $view::LABEL_NONE))
    )
;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);
