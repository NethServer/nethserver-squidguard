<?php
namespace NethServer\Module\ContentFilter\Times;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

use Nethgui\System\PlatformInterface as Validate;

/**
 * Configure squidGuard behaviour
 *
 * @author Giacomo Sanchietti
 */
class Modify extends \Nethgui\Controller\Table\Modify
{

    private $times = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24');
    private $days = array('s','m','t','w','h','f','a');

    // Declare all parameters
    public function initialize()
    {
        $dvalidator = $this->createValidator()->collectionValidator($this->createValidator()->memberOf($this->days));
 
        $parameterSchema = array(
            array('name', Validate::USERNAME, \Nethgui\Controller\Table\Modify::KEY),
            array('Days', $dvalidator, \Nethgui\Controller\Table\Modify::FIELD, 'Days', ','),
            array('StartTime', $this->createValidator()->memberOf($this->times), \Nethgui\Controller\Table\Modify::FIELD),
            array('EndTime', $this->createValidator()->memberOf($this->times), \Nethgui\Controller\Table\Modify::FIELD),
            array('Description', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);

        parent::initialize();
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $times = array_map(function($fmt) use ($view) {
                                return array($fmt, $fmt);
        }, $this->times);


        $view['StartTimeDatasource'] = $times;
        $view['EndTimeDatasource'] = $times;
        $view['DaysDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt.'_label'));
        }, $this->days);

    }

    protected function onParametersSaved($changes)
    {
        #$this->getPlatform()->signalEvent('nethserver-squidguard-save@post-process');
    }
}
