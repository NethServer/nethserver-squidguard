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
    private $userGroups = array();
    private $hosts = array();
    private $hostGroups = array();
    private $zones = array();
    private $roles = array();
    private $ipranges = array();
    private $cidrs = array();
    private $mode = NULL;
    private $filters = array();
    private $times = array();
    private $provider = null;


    private function prepareVars()
    {
        if (!$this->users) {
            $user_provider = new \NethServer\Tool\UserProvider($this->getPlatform());
            $this->users = $user_provider->getUsers();
        }
        if (!$this->userGroups) {
            $group_provider = new \NethServer\Tool\GroupProvider($this->getPlatform());
            $this->userGroups = $group_provider->getGroups();
        }
        if (!$this->hosts) {
            $h = $this->getPlatform()->getDatabase('hosts')->getAll('host');
            $l = $this->getPlatform()->getDatabase('hosts')->getAll('local');
            $this->hosts = array_merge($h, $l);
        }
        if (!$this->hostGroups) {
            $this->hostGroups = $this->getPlatform()->getDatabase('hosts')->getAll('host-group');
        }
        if (!$this->zones) {
            $this->zones = $this->getPlatform()->getDatabase('networks')->getAll('zone');
        }
        if (!$this->roles) {
            foreach($this->getPlatform()->getDatabase('networks')->getAll() as $k) {
                if (in_array($k['role'], array('green','blue','orange'))) {
                    $this->roles[$k['role']] = '';
                }
            }
        }
        if (!$this->cidrs) {
            $this->cidrs = $this->getPlatform()->getDatabase('hosts')->getAll('cidr');
        }
        if (!$this->ipranges) {
            $this->ipranges = $this->getPlatform()->getDatabase('hosts')->getAll('iprange');
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

        $parameterSchema = array(
            array('name', Validate::USERNAME, \Nethgui\Controller\Table\Modify::KEY),
            array('Src', Validate::ANYTHING,  \Nethgui\Controller\Table\Modify::FIELD),
            array('Filter', Validate::ANYTHING,  \Nethgui\Controller\Table\Modify::FIELD),
            array('FilterElse', Validate::ANYTHING,  \Nethgui\Controller\Table\Modify::FIELD),
            array('Time', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD, 'Time', ','),
            array('Description', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);
        $this->setDefaultValue('Time','');
        $this->setDefaultValue('Filter','filter;default');
        $this->setDefaultValue('FilterElse','');

        parent::initialize();

        $this->declareParameter('When', $this->createValidator()->memberOf('rules', 'always'));
    }

    public function bind(\Nethgui\Controller\RequestInterface $request)
    {
        $this->prepareVars();
        parent::bind($request);
        if($request->isMutation() && $this->parameters['When'] === 'always') {
            $this->parameters['Time'] = '';
        } else {
            $this->parameters['When'] = count($this->parameters['Time']) ? 'rules' : 'always';
        }
    }

    private function keyExists($key)
    {
        $db = '';
        if (strpos($key, ';') === false) { //this is a creation of a new profile
            return $this->getPlatform()->getDatabase('contentfilter')->getType($key) != '';
        }
        $tmp = explode(';', $key);
        if ($tmp[0] == 'user') {
            return in_array($tmp[1], array_keys($this->users));
        } else if ($tmp[0] == 'group') {
            return in_array($tmp[1], array_keys($this->userGroups));
        } else if ($tmp[0] == 'host' || $tmp[0] == 'host-group' || $tmp[0] == 'cidr' || $tmp[0] == 'iprange') {
            $db = 'hosts';
        } else if ($tmp[0] == 'time' || $tmp[0] == 'filter') {
            $db = 'contentfilter';
        } else if ($tmp[0] == 'zone') {
            $db = 'networks';
        } else if ($tmp[0] == 'role') {
            return in_array($tmp[1],array('green','blue','orange')); 
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
        if ($this->getIdentifier() && $this->parameters['FilterElse'] && !$this->keyExists($this->parameters['FilterElse'])) {
            $report->addValidationErrorMessage($this, 'FilterElse', 'key_doesnt_exists_message');
        }
        if (isset($this->parameters['Time'])) {
            foreach($this->parameters['Time'] as $timeKey) {
                $timeKey = substr($timeKey, 5); // trim leading "time;" string
                if( ! isset($this->times[$timeKey]) ) {
                    $report->addValidationErrorMessage($this, 'Time', 'key_doesnt_exists_message');
                    break;
                }
            }
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

        $tmpFilters = $this->filters;
        unset($tmpFilters['default']);
        $view['FilterElseDatasource'] = array_merge(array(array('', 'default')), $this->arrayToDatasource($tmpFilters,'filter'));

        $view['TimeDatasource'] = $this->arrayToDatasource($this->times,'time');

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
        $i = $view->translate('IpRanges_label');
        $ipranges = $this->arrayToDatasource($this->ipranges,'iprange');
        if ($ipranges) {
            $tmp[] = array($ipranges,$i);
        }

        $c = $view->translate('Cidrs_label');
        $cidrs = $this->arrayToDatasource($this->cidrs,'cidr');
        if ($cidrs) {
            $tmp[] = array($cidrs,$c);
        }

        $roles = $this->arrayToDatasource($this->roles,'role');
        $zones = $this->arrayToDatasource($this->zones,'zone');
        $z = $view->translate('Zones_label');
        if ($zones || $roles) {
            $tmp[] = array(array_merge($roles, $zones),$z);
        }

        $view['SrcDatasource'] = $tmp;

    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-squidguard-save &');
        $this->getParent()->getAdapter()->flush();
    }
}
