<?php
namespace NethServer\Module\ContentFilter\Roles;

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
 * Configure squidGuard roles
 *
 * @author Giacomo Sanchietti
 */
class Modify extends \Nethgui\Controller\Table\Modify
{
    private $users = array();
    private $userGroups = array();
    private $hosts = array();
    private $hostGroups = array();
    private $mode = NULL;
    private $profiles = array();

    private function prepareVars()
    {
        if (!$this->mode) {
            $this->mode = $this->getPlatform()->getDatabase('configuration')->getProp('squid', 'Mode');
        }
        if ($this->mode == 'authenticated') {
            if (!$this->users) {
                $this->users = $this->getPlatform()->getDatabase('accounts')->getAll('user');
            }
            if (!$this->userGroups) {
                $this->userGroups = $this->getPlatform()->getDatabase('accounts')->getAll('group');
            }
        } else {
            if (!$this->hosts) {
                $this->hosts = $this->getPlatform()->getDatabase('hosts')->getAll('host');
            }
            if (!$this->hostGroups) {
                $this->hostGroups = $this->getPlatform()->getDatabase('hosts')->getAll('host-group');
            }
        }
        $this->profiles = $this->getPlatform()->getDatabase('contentfilter')->getAll('profile'); 
    }
 
    // Declare all parameters
    public function initialize()
    {
        $columns = array(
            'Key',
            'Description',
            'Actions',
        );

        $this->prepareVars();

        $parameterSchema = array(
            array('name', Validate::USERNAME, \Nethgui\Controller\Table\Modify::KEY),
            array('Src', Validate::ANYTHING,  \Nethgui\Controller\Table\Modify::FIELD),
            array('Profile', Validate::ANYTHING,  \Nethgui\Controller\Table\Modify::FIELD),
            array('Time', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
            array('Description', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);

        parent::initialize();
    }


    private function arrayToDatasource($array)
    {
        $ret = array();
        foreach($array as $key => $props) {
            $ret[] = array($key, $key);
        }
        return $ret;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $this->prepareVars();

        $view['mode'] = $this->mode;
        $view['ProfileDatasource'] = $this->arrayToDatasource($this->profiles);


        if ($this->mode == 'authenticated') {
            $u = $view->translate('Users_label');
            $ug = $view->translate('Groups_label');
            $view['SrcDatasource'] = array(array($this->arrayToDatasource($this->users),$u), array($this->arrayToDatasource($this->userGroups),$ug));
        } else {
            $h = $view->translate('Hosts_label');
            $hg = $view->translate('Groups_label');
            $view['SrcDatasource'] = array(array($this->arrayToDatasource($this->hosts),$h),array($this->arrayToDatasource($this->hostGroups),$hg));
        }

    }

    protected function onParametersSaved($changes)
    {
        #$this->getPlatform()->signalEvent('nethserver-squidguard-save@post-process');
    }
}
