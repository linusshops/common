<?php

/**
 * Applying this trait to a child of Mage_Core_Block_Template will enable
 * parsing of CSV formatted data keys from CMS block content.  This is used
 * when many cms blocks share the same HTML markup, but different data. Defining
 * this data as CSV key value pairs allows the reuse of the HTML without having
 * to paste it across many static blocks.
 *
 * To access data inserted as csv, the call magic method is used. On the first
 * call, the csv data will be loaded and cached.  The method call should
 * be equivalent to the first column (the key).
 *
 * Keys will be normalized to be alphanumeric and lowercase. The higher the line
 * number of a key, the higher priority it has in the case of conflicts. If the
 * same key appears on lines 5 and 600, the one on line 600 will be used.
 *
 * If the composing class is not a child of Mage_Core_Block_Template, it will
 * throw an exception.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2015-12-21
 * @company Linus Shops
 */
trait Linus_Common_Trait_Cms
{
    /** @var string Cache key prefix */
    protected $CMS_CSV_CACHE_KEY = 'Linus_Common_CMS_Csv_';
    /** @var string Default length that the key data should be stored. */
    protected $CMS_CSV_CACHE_LIFETIME = '86400';
    /** @var bool If false, will always parse from the database */
    protected $CMS_CSV_CACHE_ENABLED = true;

    protected $blockCsvData = array();

    /**
     * Given data from toHtml of a static block, parses it as a csv of
     * key-value pairs into an array.
     * @param $source
     * @return array|false|mixed
     */
    protected function parseCsvData($source)
    {
        $cacheKey = $this->CMS_CSV_CACHE_KEY.$this->getBlockId();
        $cache = Mage::app()->getCache();

        if ( (!$data = $cache->load($cacheKey)) || (!$this->CMS_CSV_CACHE_ENABLED)) {
            $sourceArray = explode(PHP_EOL, $source);
            $data = array();
            foreach ($sourceArray as $sourceItem) {
                $itemSplit = str_getcsv($sourceItem, ",", '"', "\\");
                $data[$this->normalizeKey((string)$itemSplit[0])] = array_key_exists(1, $itemSplit)
                    ? $itemSplit[1]
                    : null;
            }

            if ($this->CMS_CSV_CACHE_ENABLED) {
                $cache->save(
                    serialize($data),
                    $cacheKey,
                    array('Linus_Common'),
                    $this->CMS_CSV_CACHE_LIFETIME
                );
            }
        } else {
            $data = unserialize($data);
        }
        return $data;
    }

    /**
     * Normalizes the key into an alphanumeric string
     * @param $key
     * @return string
     */
    protected function normalizeKey($key)
    {
        return strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $key));
    }

    public function fetchCsvData()
    {
        if (!in_array('Mage_Core_Block_Abstract', class_parents($this))) {
            throw new Exception('Containing class must be a child of Mage_Core_Block_Abstract');
        }

        if (empty($this->blockCsvData)) {
            $this->blockCsvData = $this->parseCsvData($this->toHtml());
        }

        return $this->blockCsvData;
    }
}
