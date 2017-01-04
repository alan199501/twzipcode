<?php

namespace Recca0120\Twzipcode\Storages;

use Recca0120\LoDash\Arr;
use Recca0120\Twzipcode\Rule;
use Recca0120\Twzipcode\Address;
use Recca0120\Twzipcode\Contracts\Storage;

class File implements Storage
{
    public $path;

    public $suffix = '.rules';

    public static $zipcode = [];

    public function __construct($path = null)
    {
        $this->path = is_null($path) === true ? realpath(__DIR__.'/../../resources/data').'/' : $path.'/';
    }

    public function zip3(Address $address)
    {
        if (empty(self::$zipcode) === true) {
            self::$zipcode = $this->restore('zipcode');
        }
        $flat = $address->flat(2);

        return isset(self::$zipcode[$flat]) === true ? self::$zipcode[$flat] : null;
    }

    public function rules($zip3)
    {
        $rules = $this->restore($zip3);

        return $rules === false ? new Arr([]) : $rules;
    }

    public function load($source)
    {
        $zip3 = new Arr;
        $this->each($this->prepareSource($source), function ($zipcode, $county, $district, $rules) use ($zip3) {
            $this->store($zipcode, (new Arr($rules))->map(function ($rule) {
                return new Rule($rule);
            }));

            if (isset($zip3[$county]) === false) {
                $zip3[$county] = substr($zipcode, 0, 1);
            }

            if (isset($zip3[$county.$district]) === false) {
                $zip3[$county.$district] = substr($zipcode, 0, 3);
            }
        });

        $zip3['宜蘭縣壯圍鄉'] = '263';
        $zip3['新竹縣寶山鄉'] = '308';
        $zip3['臺南市新市區'] = '744';

        $this->store('zipcode', $zip3);
    }

    public function loadFile($file = null)
    {
        $file = is_null($file) === true ? $this->path.'../Zip32_utf8_10501_1.csv' : $file;

        $this->load($this->getSource($file));
    }

    protected function getSource($file)
    {
        $source = '';
        $handle = fopen($file, 'r');
        try {
            while (($line = fgets($handle)) !== false) {
                $source .= $line;
            }
        } finally {
            fclose($handle);
        }

        return $source;
    }

    protected function prepareSource($source)
    {
        $results = [];
        $rules = preg_split('/\n|\r\n$/', $source);
        foreach ($rules as $rule) {
            if (empty(trim($rule)) === false) {
                list($zipcode, $county, $district) = explode(',', $rule);
                $results[$county][$district][substr($zipcode, 0, 3)][] = $rule;
            }
        }

        return $results;
    }

    protected function each($rules, $callback)
    {
        foreach ($rules as $county => $temp) {
            foreach ($temp as $district => $temp2) {
                foreach ($temp2 as $zipcode => $rules) {
                    $callback($zipcode, $county, $district, $rules);
                }
            }
        }
    }

    protected function compress($plainText)
    {
        $plainText = serialize($plainText);
        $method = 'gzcompress';
        if (function_exists($method) === true) {
            $plainText = call_user_func($method, $plainText);
        }

        return $plainText;
    }

    protected function decompress($compressed)
    {
        $method = 'gzuncompress';
        if (function_exists($method) === true) {
            $compressed = call_user_func($method, $compressed);
        }

        return unserialize($compressed);
    }

    protected function store($filename, $data)
    {
        file_put_contents(
            $this->path.$filename.$this->suffix,
            $this->compress($data)
        );
    }

    protected function restore($filename)
    {
        if (file_exists($this->path.$filename.$this->suffix) === false) {
            return false;
        }

        return $this->decompress(file_get_contents($this->path.$filename.$this->suffix));
    }
}
