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

    private $days = array('m','t','w','h','f','a','s');

    // Declare all parameters
    public function initialize()
    {
        $dvalidator = $this->createValidator()->collectionValidator($this->createValidator()->memberOf($this->days));
 
        $parameterSchema = array(
            array('name', Validate::USERNAME, \Nethgui\Controller\Table\Modify::KEY),
            array('Days', $dvalidator, \Nethgui\Controller\Table\Modify::FIELD, 'Days', ','),
            array('StartTime', Validate::TIME, \Nethgui\Controller\Table\Modify::FIELD),
            array('EndTime', Validate::TIME, \Nethgui\Controller\Table\Modify::FIELD),
            array('Description', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);

        parent::initialize();
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        $keyExists = $this->getPlatform()->getDatabase('contentfilter')->getType($this->parameters['name']) != '';
        if ($this->getIdentifier() === 'create' && $keyExists) {
            $report->addValidationErrorMessage($this, 'name', 'key_exists_message');
        }
        if ($this->getIdentifier() !== 'create' && ! $keyExists) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1416876002);
        } else {
            $startTime = strtotime($this->parameters['StartTime']);
            $endTime = strtotime($this->parameters['EndTime']);
            if ($startTime > $endTime) {
                $report->addValidationErrorMessage($this, 'EndTime', 'endtime_grater_than_starttime');
            }
        }
        parent::validate($report);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        if ($this->getIdentifier() == 'delete') {
            $view->setTemplate('Nethgui\Template\Table\Delete');
        }

        $view['DaysDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt.'_label'));
        }, $this->days);

    }

    protected function onParametersSaved($changes)
    {
        if ($this->getIdentifier() !== 'create') {
            $this->getPlatform()->signalEvent('nethserver-squidguard-save &');
        }
    }

}
