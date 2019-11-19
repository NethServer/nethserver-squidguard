<?php
namespace NethServer\Module\ContentFilter\Filters;

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

    /* list of blacklists */
    private $categories = array();

    private $index = array();

    private function parseIndex() 
    {
        $c = "/var/squidGuard/blacklists/global_usage";
        $last = "";
        if (is_readable($c)) {
            $handle = @fopen("$c", "r");
            if ($handle) {
                while (($buffer = fgets($handle, 4096)) !== false) {
                    $buffer = trim($buffer);
                    if (!$buffer || $buffer[0] == "#") {
                        continue;
                    }
                    $fields = explode(":",$buffer);
                    if ($fields) {
                        if ($fields[0] == "NAME") {
                            $last = trim($fields[1]);
                            # Add to the list only existing categories
                            if (file_exists("/var/squidGuard/blacklists/$last")) {
                                $name = basename($last); # make sure to correctly catch subcategories
                                $this->index[$name] = ucfirst(str_replace('/', ' ', $last));
                            }
                        }
                    }
                }
                fclose($handle);
            }
        }
    }

    private function readCategories()
    {
        if (!$this->index) {
            $this->parseIndex();
        }
        $this->categories = array_keys($this->index);
        $custom_categories = $this->getPlatform()->getDatabase('contentfilter')->getAll('category');
        foreach ( $custom_categories as $k => $c ) {
            $this->categories[] = $k;
        }
    }
    
    // Declare all parameters
    public function initialize()
    {
        if (!$this->categories) {
            $this->readCategories();
        }

        $cvalidator = $this->createValidator(Validate::ANYTHING_COLLECTION)->collectionValidator($this->createValidator()->memberOf($this->categories));
        $parameterSchema = array(
            array('name', Validate::USERNAME, \Nethgui\Controller\Table\Modify::KEY),
            array('BlockAll', Validate::SERVICESTATUS,  \Nethgui\Controller\Table\Modify::FIELD),
            array('BlockIpAccess', Validate::SERVICESTATUS, \Nethgui\Controller\Table\Modify::FIELD),
            array('BlockFileTypes', Validate::SERVICESTATUS, \Nethgui\Controller\Table\Modify::FIELD),
            array('WhiteList', Validate::SERVICESTATUS, \Nethgui\Controller\Table\Modify::FIELD),
            array('BlackList', Validate::SERVICESTATUS, \Nethgui\Controller\Table\Modify::FIELD),
            array('Categories', $cvalidator, \Nethgui\Controller\Table\Modify::FIELD, 'Categories', ','),
            array('Description', Validate::ANYTHING, \Nethgui\Controller\Table\Modify::FIELD),
        );

        $this->setSchema($parameterSchema);
        $this->setDefaultValue('BlockAll', 'disabled');
        $this->setDefaultValue('BlockIpAccess', 'disabled');

        parent::initialize();
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        $keyExists = $this->getPlatform()->getDatabase('contentfilter')->getType($this->parameters['name']) != '';
        if ($this->getIdentifier() === 'create' && $keyExists) {
            $report->addValidationErrorMessage($this, 'name', 'key_exists_message');
        }
        if ($this->getIdentifier() !== 'create' && ! $keyExists) {
            throw new \Nethgui\Exception\HttpException('Not found', 404, 1416876015);
        }
        if($this->getIdentifier() === 'delete') {
            $v = $this->createValidator()->platform('squidguard-obj-delete', 'filter');
            if( ! $v->evaluate($this->parameters['name'])) {
                $report->addValidationError($this, 'FilterKey', $v);
            }
        }

        parent::validate($report);
    }

    private static function cmpcat($a, $b)
    {
        return strnatcasecmp($a[1],$b[1]);
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);

        if ($this->getIdentifier() == 'delete') {
            $view->setTemplate('Nethgui\Template\Table\Delete');
        }

        $view['BlockAllDatasource'] = array_map(function($fmt) use ($view) {
                                return array($fmt, $view->translate($fmt . '_label'));
        }, array('enabled','disabled'));

        if (!$this->categories) {
            $this->readCategories();
        }
        $tmp = array();
        $lang = strtoupper($view->getTranslator()->getLanguageCode());
        foreach ($this->categories as $cat) {
            $t = ucfirst($cat);
            if (isset($this->index[$cat])) {
                $t = $this->index[$cat];
            }
            $tmp[] = array($cat, $t);
        }
        usort($tmp,array($this,'cmpcat'));
        $view['CategoriesDatasource'] = $tmp;
    }

    protected function onParametersSaved($changes)
    {
        if ($this->getIdentifier() !== 'create') {
            $this->getPlatform()->signalEvent('nethserver-squidguard-save &');
        }
    }

}
