<?php

namespace NethServer\Module\ContentFilter\Lists;

/*
 * Copyright (C) 2014  Nethesis S.r.l.
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

/**
 * Download squidguard blacklists
 *
 */
class Download extends \Nethgui\Controller\AbstractController
{

    public function process()
    {
        parent::process();
        if ($this->getRequest()->isMutation()) {
            $this->getPlatform()->signalEvent('nethserver-squidguard-downloadlists &');
        }
    }


    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        if ($this->getRequest()->isValidated()) {
            $view->getCommandList()->show();
        }
    }

}
