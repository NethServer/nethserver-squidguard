<?php

/*
 * Copyright (C) 2017 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

namespace NethServer\Module\ContentFilter\Profiles;

class ProfileAdapter extends \Nethgui\Adapter\LazyLoaderAdapter
{
    /**
     *
     * @var \Nethgui\System\PlatformInterface
     */
    private $platform;

    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
        parent::__construct(array($this, 'readProfiles'));
    }

    public function flush()
    {
        $this->data = NULL;
        return $this;
    }

    public function readProfiles()
    {
        $this->adapter = $adapter = $this->platform->getTableAdapter('contentfilter', 'profile');
        $index=0;
        $data = new \ArrayObject();
        foreach($adapter as $key => $row) {
            if($key == 'default_profile') {
                continue;
            }
            $row['index'] = ++$index;
            $data[$key] = $row;
        }
        // Move the default profile at the end of the list
        if(isset($adapter['default_profile'])) {
            $data['default_profile'] = $adapter['default_profile'];
            $data['default_profile']['index'] = ++$index;
        }
        return $data;
    }

    
    public function offsetSet($offset, $value)
    {
        return $this->adapter->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->adapter->offsetUnset($offset);
    }

    public function save()
    {
        return $this->adapter->save();
    }

    public function set($value)
    {
        return $this->adapter->set($value);
    }

    public function delete()
    {
        return $this->adapter->delete();
    }
}
