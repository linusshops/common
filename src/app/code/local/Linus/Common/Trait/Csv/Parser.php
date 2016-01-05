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
 * [
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
}
