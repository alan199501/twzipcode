<?php

namespace Recca0120\Twzipcode;

use Recca0120\Twzipcode\Storages\File;
use Recca0120\Twzipcode\Contracts\Storage;

class Rules
{
    public function __construct(Storage $storage = null)
    {
        $this->storage = is_null($storage) === true ? new File() : $storage;
    }

    public function match($address)
    {
        $address = is_a($address, Address::class) === true ? $address : new Address($address);
        $zip3 = $this->storage->zip3($address);
        $rule = $this->storage->rules($zip3)->find(function ($rule) use ($address) {
            return $rule->match($address);
        });

        return is_null($rule) === false ? $rule->zip5() : $zip3;
    }

    public function load($source)
    {
        return $this->storage->load($source);
    }

    public function loadFile($file)
    {
        return $this->storage->loadFile($file);
    }
}
