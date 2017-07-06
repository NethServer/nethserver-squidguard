<?php

echo $view->textInput('name', ($view->getModule()->getIdentifier() == 'update' ? $view::STATE_READONLY : 0));
echo $view->textInput('Description');
echo $view->fieldset()->setAttribute('template', $T('Domains_label'))
 ->insert($view->textArea('Domains', $view::LABEL_NONE));

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);
