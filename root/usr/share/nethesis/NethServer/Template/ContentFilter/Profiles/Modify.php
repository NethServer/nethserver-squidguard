<?php

echo $view->textInput('name');
echo $view->textInput('Description');
echo $view->selector('Src', $view::SELECTOR_DROPDOWN);
echo $view->selector('Filter');
echo $view->selector('Time');

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);
