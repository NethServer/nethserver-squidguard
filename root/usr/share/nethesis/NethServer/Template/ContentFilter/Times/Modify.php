<?php

echo $view->textInput('name');
echo $view->textInput('Description');
echo $view->selector('Days', $view::SELECTOR_MULTIPLE);
echo $view->columns()
    ->insert($view->selector('StartTime', $view::SELECTOR_DROPDOWN))
    ->insert($view->selector('EndTime', $view::SELECTOR_DROPDOWN));

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);
