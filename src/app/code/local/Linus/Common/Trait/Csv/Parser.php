<?php

/**
 * Provides methods for parsing a nested data structure out of injected
 * CSV key-value pairs.
 *
 * The expected format for a csv row is an arbitrary-length underscore-delimited
 * path that ends in a key name, with the second column being the value of that
 * key.
 *
 * Example CSV:
 * "column_1_section_helmets_title","Motorcycle Helmets"
 * "column_1_section_helmets_icon","motorcycle-helmets"
 *
 * will yield
 *
 * 'column' => [
    1 => [
        'section' => [
            'helmets' => [
                'title' => "Motorcycle Helmets",
                'icon'  => "motorcycle-helmets"
            ]
        ]
    ]
 * ]
 *
 * When this is composed on a block, prepare() must always be called at the
 * start of the template, as it takes care of parsing the nested structure.
 *
 * Linus_Common_Block_Csv can be used if you do not already have a block, and
 * are composing your blocks via layout xml.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-01-05
 * @company Linus Shops
 */
trait Linus_Common_Trait_Csv_Parser
{
    protected $parsedCsvData = array();

    /**
     * Take any actions necessary for parsing and building the nested data.
     */
    public function prepare()
    {
        $this->parseDataKeys();
    }

    /**
     * Convert the key-value pair array to a nested data structure.
     */
    private function parseDataKeys()
    {
        $keys = $this->getCsvKeys();

        foreach($keys as $key) {
            $this->insertByPath($key, $this->getData($key));
        }
    }

    /**
     * Given a path (first column of the csv) and a value (second column),
     * inject it into the nested structure.
     * @param string $path first column of the csv
     * @param string $value second column of the csv
     */
    private function insertByPath($path, $value)
    {
        $pathspec = explode('_',$path);
        $register = &$this->parsedCsvData;

        foreach ($pathspec as $p) {
            if (!isset($register[$p])) {
                $register[$p] = array();
            }

            $register = &$register[$p];
        }

        $register = $value;
    }

    /**
     * Provide magic methods for looking up data in the nested structure
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        //If this trait is composed into a Varien_Object, we must defer
        //to the special starting method strings if present
        if (is_subclass_of($this, 'Varien_Object')) {
            if (in_array(substr($method, 0, 3),
                array('get', 'set', 'uns', 'has'))) {
                return parent::__call($method, $args);
            }
        }

        $path = '';

        if (count($args) > 0) {
            $path = '.' . implode('.', $args);
        }

        return $this->getCsvData($method.$path);
    }

    protected function getCsvData($path)
    {
        $pathspec = explode('.',$path);
        $register = $this->parsedCsvData;

        //Descend into the data array
        foreach ($pathspec as $p) {
            if (is_array($register) && isset($register[$p])) {
                //Standard array descent
                $register = $register[$p];
            } else {
                //Once a path element is invalid, the rest of the path is worthless.
                $register = null;
                break;
            }
        }

        return $register;
    }

    public function getParsedDataArray()
    {
        return $this->parsedCsvData;
    }
}
