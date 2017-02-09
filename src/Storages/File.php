<?php

namespace Recca0120\Twzipcode\Storages;

use Recca0120\LoDash\JArray;
use Recca0120\Twzipcode\Rule;
use Recca0120\Twzipcode\Address;
use Recca0120\Twzipcode\Contracts\Storage;

class File implements Storage
{
    public $path;

    public $suffix = '.rules';

    public static $cached = [
        'zip3' => null,
        'zip5' => null,
    ];

    public function __construct($path = null)
    {
        $this->path = ($path ?: realpath(__DIR__.'/../../resources/data')).'/';
    }

    public function zip3(Address $address)
    {
        if (is_null(self::$cached['zip3']) === true) {
            self::$cached['zip3'] = $this->restore('zip3');
        }
        $flat = $address->flat(2);

        return isset(self::$cached['zip3'][$flat]) === true ? self::$cached['zip3'][$flat] : null;
    }

    public function rules($zip3)
    {
        if (empty(self::$cached['zip5']) === true) {
            self::$cached['zip5'] = $this->restore('zip5');
        }

        return isset(self::$cached['zip5'][$zip3]) === true
            ? $this->decompress(self::$cached['zip5'][$zip3])
            : new JArray([]);
    }

    public function load($source)
    {
        $zip5 = new JArray;
        $zip3 = new JArray;
        $this->each($this->prepareSource($source), function ($zipcode, $county, $district, $rules) use ($zip5, $zip3) {
            $zip5[$zipcode] = $this->compress((new JArray($rules))->map(function ($rule) {
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

        $this->store('zip3', $zip3);
        $this->store('zip5', $zip5);
    }

    public function loadFile($file = null)
    {
        $file = $file ?: $this->path.'../Zip32_utf8_10501_1.csv';

        $this->load($this->getSource($file));
    }

    public function flush()
    {
        static::$cached = [
            'zip3' => null,
            'zip5' => null,
        ];

        return $this;
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

        return $this->decompress(
            file_get_contents($this->path.$filename.$this->suffix)
        );
    }
}
