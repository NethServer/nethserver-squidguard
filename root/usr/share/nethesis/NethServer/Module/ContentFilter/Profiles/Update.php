<?php
namespace NethServer\Module\ContentFilter\Profiles;

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
 * Signal nethserver-squidguard-save event
 *
 * @author Giacomo Sanchietti
 */
class Update extends \Nethgui\Controller\Table\AbstractAction
{

    public function process()
    {
        if ( ! $this->getRequest()->isMutation()) {
            return;
        }

        $this->getPlatform()->signalEvent('nethserver-squidguard-save &');
    }
}
