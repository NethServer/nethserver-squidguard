<?php
namespace NethServer\Module\ContentFilter;

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
 * Configure lists for SquidGuard
 *
 * @author Giacomo Sanchietti
 */
class Lists extends \Nethgui\Controller\AbstractController
{

    public $sortId = 70;

    private $lists = array('toulouse','custom');

    // Declare all parameters
    public function initialize()
    {
        parent::initialize();
  
        $this->declareParameter('Lists', $this->createValidator()->memberOf($this->lists), array('configuration', 'squidguard', 'Lists'));
        $this->declareParameter('CustomListURL', Validate::ANYTHING, array('configuration', 'squidguard', 'CustomListURL'));
    }

    public function process() {
        parent::process();
        if ($this->getRequest()->isMutation()) {
            $this->getPlatform()->signalEvent('nethserver-squidguard-downloadlists &');
        }
    }

}
