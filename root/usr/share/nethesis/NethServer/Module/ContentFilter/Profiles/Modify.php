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
 * Configure squidGuard profiles
 *
 * @author Giacomo Sanchietti
 */
class Modify extends \Nethgui\Controller\Table\Modify
{
    private $users = array();
    private $ADUsers = array();
    private $userGroups = array();
    private $hosts = array();
    private $hostGroups = array();
    private $mode = NULL;
    private $filters = array();
    private $times = array();

    private function prepareVars()
    {
        if (!$this->users) {
            $this->users = $this->getPlatform()->getDatabase('accounts')->getAll('user');
            $role = $this->getPlatform()->getDatabase('configuration')->getProp('smb','ServerRole');
            if ($role == 'ADS') { // list  active directory users
                $lines = $this->getPlatform()->exec('/usr/bin/getent passwd')->getOutputArray();
                foreach($lines as $line) {
                    $tmp = explode(':',$line);
                    if (intval($tmp[2]) >= 50000) {
                        $this->ADUsers[] = $tmp[0];
                    }
                }
 
            }
        }
        if (!$this->userGroups) {
            $this->userGroups = $this->getPlatform()->getDatabase('accounts')->getAll('group');
        }
        if (!$this->hosts) {
            $h = $this->getPlatform()->getDatabase('hosts')->getAll('host');
            $l = $this->getPlatform()->getDatabase('hosts')->getAll('local');
            $this->hosts = array_merge($h, $l);
        }
        if (!$this->hostGroups) {
            $this->hostGroups = $this->getPlatform()->getDatabase('hosts')->getAll('host-group');
        }
        $this->filters = $this->getPlatform()->getDatabase('contentfilter')->getAll('filter'); 
        $this->times = $this->getPlatform()->getDatabase('contentfilter')->getAll('time'); 
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
            array('Filter', Validate::ANYTHING,  \Nethgui\Controller\Table\Modify::FIELD),
            array('Time', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
            array('Description', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);
        $this->setDefaultValue('Time','');
        $this->setDefaultValue('Filter','filter;default');

        parent::initialize();
    }

    private function keyExists($key)
    {
        $db = '';
        $tmp = explode(';', $key);
        if ($tmp[0] == 'user' || $tmp[0] == 'group') {
            $db = 'accounts';
        } else if ($tmp[0] == 'host' || $tmp[0] == 'host-group') {
            $db = 'hosts';
        } else if ($tmp[0] == 'time' || $tmp[0] == 'filter') {
            $db = 'contentfilter';
        } else {
            return false;
        }
        return ($this->getPlatform()->getDatabase($db)->getType($tmp[1]) != '');
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        $keyExists = $this->keyExists($this->parameters['name']);
        if ($this->getIdentifier() === 'create' && $keyExists) {
            $report->addValidationErrorMessage($this, 'name', 'key_exists_message');
        }
        if ($this->getIdentifier() && $this->parameters['Src']) {
             if (strpos($this->parameters['Src'],';') === false ) {
                 // User from active directory, no further check
             } else {
                 if (!$this->keyExists($this->parameters['Src'])) {
                     $report->addValidationErrorMessage($this, 'Src', 'key_doesnt_exists_message');
                 }
            }
        }
        if ($this->getIdentifier() && $this->parameters['Filter'] && !$this->keyExists($this->parameters['Filter'])) {
            $report->addValidationErrorMessage($this, 'Filter', 'key_doesnt_exists_message');
        }
        if ($this->parameters['Time'] && !$this->keyExists($this->parameters['Time'])) {
            $report->addValidationErrorMessage($this, 'Time', 'key_doesnt_exists_message');
        }
        parent::validate($report);
    }

    private function arrayToDatasource($array, $prefix)
    {
        $ret = array();
        foreach($array as $key => $props) {
            $ret[] = array($prefix.';'.$key, $key);
        }
        return $ret;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $this->prepareVars();
        if ($this->getIdentifier() == 'delete') {
            $view->setTemplate('Nethgui\Template\Table\Delete');
        }

        $view['FilterDatasource'] = $this->arrayToDatasource($this->filters,'filter');
        $tmp = $this->arrayToDatasource($this->times,'time');
        array_unshift($tmp,array('',$view->translate('always_label')));
        $view['TimeDatasource'] = $tmp;


        $tmp = array();
        $u = $view->translate('Users_label');
        $ug = $view->translate('UserGroups_label');
        $users = $this->arrayToDatasource($this->users,'user');
        if ($users) {
            $tmp[] = array($users,$u);
        }
        $groups = $this->arrayToDatasource($this->userGroups,'group');
        if ($groups) {
            $tmp[] = array($groups,$ug);
        }
        $h = $view->translate('Hosts_label');
        $hg = $view->translate('HostGroups_label');
        $hosts = $this->arrayToDatasource($this->hosts,'host');
        if ($hosts) {
            $tmp[] = array($hosts,$h);
        }
        $hgroups = $this->arrayToDatasource($this->hostGroups,'host-group');
        if ($hgroups) {
            $tmp[] = array($hgroups,$hg);
        }
        $adu = $view->translate('ADUsers_label');
        $adusers = array();
        foreach($this->ADUsers as $k) {
            $adusers[] = array($k, $k);
        }
        if ($adusers) {
            $tmp[] = array($adusers,$adu);
        }

        $view['SrcDatasource'] = $tmp;

    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-squidguard-save &');
    }
}
