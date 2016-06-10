<?php
$view->requireFlag($view::INSET_DIALOG);

echo $view->panel()
    ->insert($view->header()->setAttribute('template', $T('update_header')))
    ->insert($view->literal($T('update_confirmation_label')))
;

echo $view->buttonList()
    ->insert($view->button('Yes', $view::BUTTON_SUBMIT))
    ->insert($view->button('No', $view::BUTTON_CANCEL)->setAttribute('value', $view['Cancel']))
;

