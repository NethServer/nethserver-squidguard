<?php
namespace NethServer\Module\ContentFilter\Categories;

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
 * Configure squidGuard profiles
 *
 * @author Giacomo Sanchietti
 */
class Modify extends \Nethgui\Controller\Table\Modify
{
    // Declare all parameters
    public function initialize()
    {
        $columns = array(
            'Key',
            'Description',
            'Actions',
        );

        $parameterSchema = array(
            array('name', Validate::USERNAME, \Nethgui\Controller\Table\Modify::KEY),
            array('Domains', Validate::ANYTHING,  \Nethgui\Controller\Table\Modify::FIELD),
            array('Description', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);

        parent::initialize();
    }

    public function readDomains($domains)
    {
        return str_replace(',',"\n",$domains);
    }

    public function writeDomains($domains)
    {
        return array(trim(preg_replace('/\s+/', ',', $domains)));
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        if ($this->getIdentifier() == 'delete') {
            $view->setTemplate('Nethgui\Template\Table\Delete');
        }
    }

    protected function onParametersSaved($changes)
    {
        if ($this->getIdentifier() !== 'create') {
            $this->getPlatform()->signalEvent('nethserver-squidguard-save &');
        }
    }

}
