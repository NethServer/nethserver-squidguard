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
 * Configure general squidGuard behaviour
 *
 * @author Giacomo Sanchietti
 */
class General extends \Nethgui\Controller\AbstractController implements \Nethgui\Component\DependencyConsumer
{

    public $sortId = 10;

    // Declare all parameters
    public function initialize()
    {
        parent::initialize();
  
        $ftvalidator = $this->createValidator()->orValidator($this->createValidator()->regexp('/(\w+)(,\s*\w+)*/'),$this->createValidator()->isEmpty());
        $this->declareParameter('status', Validate::SERVICESTATUS, array('configuration', 'ufdbGuard', 'status'));
        $this->declareParameter('BlockAll', Validate::SERVICESTATUS, array('configuration', 'squidguard', 'BlockAll'));
        $this->declareParameter('Expressions', Validate::SERVICESTATUS, array('configuration', 'squidguard', 'Expressions'));
        $this->declareParameter('BlockedFileTypes', $ftvalidator, array('configuration', 'squidguard', 'BlockedFileTypes'));
    
        $this->declareParameter('BlockAcl', Validate::ANYTHING, array(
            array('configuration', 'squidguard', 'DomainBlacklist'),
            array('configuration', 'squidguard', 'UrlBlacklist'),
        ));
        $this->declareParameter('AllowAcl', Validate::ANYTHING, array(
            array('configuration', 'squidguard', 'DomainWhitelist'),
            array('configuration', 'squidguard', 'UrlWhitelist'),
        ));

    }


    public function readBlockAcl($DomainBlacklist, $UrlBlacklist)
    {
        $BlockAcl = '';
        
        // Append ACL suffix to each list:
        foreach (array('DB' => $DomainBlacklist, 'UB' => $UrlBlacklist) as $acl => $list) {
            foreach (explode(',', $list) as $item) {
                $BlockAcl .= $item ? ($item . ":" . $acl . "\r\n") : '';
            }
        }

        return $BlockAcl;
    }


    public function readAllowAcl($DomainWhitelist, $UrlWhitelist)
    {
        $AllowAcl = '';

        // Append ACL suffix to each list:
        foreach (array('DW' => $DomainWhitelist, 'UW' => $UrlWhitelist) as $acl => $list) {
            foreach (explode(',', $list) as $item) {
                $AllowAcl .= $item ? ($item . ":" . $acl . "\r\n") : '';
            }
        }

        return $AllowAcl;
    }

    public function writeBlockAcl($BlockAcl)
    {
        $acls = array();

        foreach (explode("\n", $BlockAcl) as $line) {
            $parts = array();
            if (preg_match('/^\s*([^:\s]+)\s*:\s*([^\s]+)\s*$/', $line, $parts) > 0) {
                $acls[$parts[2]][] = $parts[1];
            }
        }

        return array(
            // $Ban:
            // $DomainBlacklist:
            isset($acls['DB']) ? implode(',', array_unique($acls['DB'])) : '',
            // $UrlBlacklist:
            isset($acls['UB']) ? implode(',', array_unique($acls['UB'])) : ''
        );
    }

    public function writeAllowAcl($AllowAcl)
    {
        $acls = array();

        foreach (explode("\n", $AllowAcl) as $line) {
            $parts = array();
            if (preg_match('/^\s*([^:\s]+)\s*:\s*([^\s]+)\s*$/', $line, $parts) > 0) {
                $acls[$parts[2]][] = $parts[1];
            }
        }

        return array(
            // $DomainWhitelist:
            isset($acls['DW']) ? implode(',', array_unique($acls['DW'])) : '',
            // $UrlWhitelist:
            isset($acls['UW']) ? implode(',', array_unique($acls['UW'])) : ''
        );
    }

    private static function cmpcat($a, $b)
    {
        return strnatcasecmp($a[1],$b[1]); 
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $squidStatus = $this->getPlatform()->getDatabase('configuration')->getProp('squid', 'status');
        if ($squidStatus == 'disabled') {
            $this->notifications->warning($view->translate('squid_disabled_label'));
        }
        $view['statusDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt . '_label'));
        }, array('enabled','disabled'));
        $view['BlockAllDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt . '_label'));
        }, array('enabled','disabled'));

    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-squidguard-save &');
    }

    public function setUserNotifications(\Nethgui\Model\UserNotifications $n)
    {
        $this->notifications = $n;
        return $this;
    }
    public function getDependencySetters()
    {
        return array(
            'UserNotifications' => array($this, 'setUserNotifications'),
        );
    }
}
