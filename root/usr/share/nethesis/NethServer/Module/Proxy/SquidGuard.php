<?php
namespace NethServer\Module\Proxy;

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
class SquidGuard extends \Nethgui\Controller\AbstractController
{

    public $sortId = 20;

    /* list of blacklists */
    private $categories = array();

    private function readCategories()
    {
        $blDir = "/var/squidGuard/blacklists";
        $d = dir($blDir);
        while (false !== ($entry = $d->read())) {
            if ($entry == "." || $entry == "..") {
                continue;
            }
            $this->categories[] = $entry;
        }
        $d->close();
    }
    
    // Declare all parameters
    public function initialize()
    {
        parent::initialize();
  
        if (!$this->categories) {
            $this->readCategories();
        }

        $this->declareParameter('status', Validate::SERVICESTATUS, array('configuration', 'squidguard', 'status'));
        $this->declareParameter('BlockAll', Validate::SERVICESTATUS, array('configuration', 'squidguard', 'BlockAll'));
        $this->declareParameter('BlockIpAccess', Validate::SERVICESTATUS, array('configuration', 'squidguard', 'BlockIpAccess'));
        $this->declareParameter('Expressions', Validate::SERVICESTATUS, array('configuration', 'squidguard', 'Expressions'));
    
        $this->declareParameter('BlockAcl', Validate::ANYTHING, array(
            array('configuration', 'squidguard', 'Ban'),
            array('configuration', 'squidguard', 'DomainBlacklist'),
            array('configuration', 'squidguard', 'UrlBlacklist'),
        ));
        $this->declareParameter('AllowAcl', Validate::ANYTHING, array(
            array('configuration', 'squidguard', 'Unfiltered'),
            array('configuration', 'squidguard', 'DomainWhitelist'),
            array('configuration', 'squidguard', 'UrlWhitelist'),
        ));

        $cvalidator = $this->createValidator(Validate::ANYTHING_COLLECTION)->collectionValidator($this->createValidator()->memberOf($this->categories));
        $this->declareParameter('AllowedCategories', $cvalidator, array('configuration', 'squidguard', 'AllowedCategories',','));
        $this->declareParameter('BlockedCategories', $cvalidator, array('configuration', 'squidguard', 'BlockedCategories',','));

    }


    public function readBlockAcl($Ban, $DomainBlacklist, $UrlBlacklist)
    {
        $BlockAcl = '';
        
        // Append ACL suffix to each list:
        foreach (array('BAN' => $Ban, 'DB' => $DomainBlacklist, 'UB' => $UrlBlacklist) as $acl => $list) {
            foreach (explode(',', $list) as $item) {
                $BlockAcl .= $item ? ($item . ":" . $acl . "\r\n") : '';
            }
        }

        return $BlockAcl;
    }


    public function readAllowAcl($Unfiltered, $DomainWhitelist, $UrlWhitelist)
    {
        $AllowAcl = '';

        // Append ACL suffix to each list:
        foreach (array('UN' => $Unfiltered, 'DW' => $DomainWhitelist, 'UW' => $UrlWhitelist) as $acl => $list) {
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
            isset($acls['BAN']) ? implode(',', array_unique($acls['BAN'])) : '',
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
            // $Unfiltered:
            isset($acls['UN']) ? implode(',', array_unique($acls['UN'])) : '',
            // $DomainWhitelist:
            isset($acls['DW']) ? implode(',', array_unique($acls['DW'])) : '',
            // $UrlWhitelist:
            isset($acls['UW']) ? implode(',', array_unique($acls['UW'])) : ''
        );
    }


    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        $view['statusDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt . '_label'));
        }, array('enabled','disabled'));
        $view['BlockAllDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt . '_label'));
        }, array('enabled','disabled'));

        if (!$this->categories) {
            $this->readCategories();
        }
        $view['AllowedCategoriesDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt . '_label'));
        }, $this->categories);
        $view['BlockedCategoriesDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt . '_label'));
        }, $this->categories);

    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-squidguard-save@post-process');
    }
}
