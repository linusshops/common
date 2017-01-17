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
    protected $categoriesData;

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
                if (!is_array($register)) {
                    $register = [$register];
                }

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


    /**
     * Returns paths specific data from the CSV source
     *
     * @param string $path path to data in segment @example 'section.icon'
     * @param array|null $segment
     * @param mixed $default default value to return if not found.
     * @return mixed
     */
    protected function getCsvData($path, $segment=null, $default = [])
    {
        $data = is_array($segment) ? $segment : $this->parsedCsvData;

        return Mage::helper('linus_common/array')->get(
            $data,
            $path,
            $default
        );
    }

    public function getParsedDataArray()
    {
        return $this->parsedCsvData;
    }

    /**
     * Returns the csv label or category name.
     *
     * This will return the label specified in the csv data or will look for
     * the category name. To do so it will only query the db once and get all
     * info for all categories for future reference.
     *
     * @param $item
     * CSV parsed data item that has a 'categoryid' and an optional 'label'
     *
     * @return string
     */
    public function getItemTitle($item)
    {
        $label = '';
        if (!empty($item['title'])) {
            $label = $item['title'];
        } else if (!empty($item['categoryid'])) {
            $label = $this->getCategoryTitle($item['categoryid']);
        }
        return $this->__($label);
    }

    /**
     * Get title from either protected array or database.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getCategoryTitle($categoryId)
    {
        if (!isset($this->categoriesData)) {
            $this->fetchCategories($this->getParsedDataArray());
        }

        if (isset($this->categoriesData[$categoryId])) {
            $title = $this->categoriesData[$categoryId]->getName();
        } else {
            $title = Mage::getModel('catalog/category')->load($categoryId)->getName();
        }
        return $title;
    }

    /**
     * Get category, referencing the category cache if available.
     * @param $categoryId
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory($categoryId)
    {
        if (!isset($this->categoriesData)) {
            $this->fetchCategories($this->getParsedDataArray());
        }

        if (isset($this->categoriesData[$categoryId])) {
            $category = $this->categoriesData[$categoryId];
        } else {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $this->categoriesData[$categoryId] = $category;
        }

        return $category;
    }

    /**
     * Get needed categories and stores them to protected array.
     *
     * This will query the database once and store the results in memory to
     * be used when needed in the rendering of the page.
     *
     * @param $parsedDataArray
     */
    protected function fetchCategories($parsedDataArray)
    {
        $allCategoriesIds = $this->fetchCategoriesIds($parsedDataArray);
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection
            ->addIdFilter($allCategoriesIds)
            ->addNameToResult();

        foreach ($collection as $category) {
            $this->categoriesData[$category->getId()] = $category;
        }
    }

    /**
     * Iterates multidimensional array and returns all 'categoryid'
     *
     * To not query the db for each category title in part, this gets the array from
     * all the csv data. This is a deep multidimensional array and we need to go
     * deep in there and see if any 'categoryid' keys are defined.
     * If so, we get them out in a one dimensional array to use as a filter for
     * the categories.
     *
     * @param array $array
     * @return array
     */
    protected function fetchCategoriesIds(array $array) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($array),
            RecursiveIteratorIterator::CHILD_FIRST);
        $ids = array();
        foreach ($iterator as $key => $value) {
            if ($key === 'categoryid' && !empty($value)) {
                array_push($ids, $value);
            }
        }
        return $ids;
    }
}
