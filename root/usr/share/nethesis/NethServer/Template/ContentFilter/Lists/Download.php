<?php
$view->requireFlag($view::INSET_DIALOG);

echo $view->header()->setAttribute('template', $T('Download_header'));
echo $view->translate('Download_message');
echo $view->buttonList($view::BUTTON_CANCEL | $view::BUTTON_SUBMIT);
