<?php

echo $view->textInput('name');
echo $view->textInput('Description');
echo $view->selector('BlockAll','disabled');
echo $view->selector('Categories', $view::SELECTOR_MULTIPLE);

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);
