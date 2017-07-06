<?php
$view->includeFile('NethServer/Js/jquery.timepicker.min.js');
$view->includeFile('NethServer/Css/jquery.timepicker.css');

echo $view->textInput('name', ($view->getModule()->getIdentifier() == 'update' ? $view::STATE_READONLY : 0));
echo $view->textInput('Description');
echo $view->selector('Days', $view::SELECTOR_MULTIPLE);
echo $view->columns()
    ->insert($view->textInput('StartTime'))
    ->insert($view->textInput('EndTime'));

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_CANCEL | $view::BUTTON_HELP);

$start_id = $view->getClientEventTarget('StartTime');
$end_id = $view->getClientEventTarget('EndTime');

$view->includeJavascript("
(function ( $ ) {
    $(document).ready(function() {
        $('.$start_id').timepicker( { minTime: \"00:00\", timeFormat: \"\H:\i\"} ); 
        $('.$end_id').timepicker( { minTime: \"00:00\", timeFormat: \"\H:\i\"} );
    });
})( jQuery );
");
