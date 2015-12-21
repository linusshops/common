<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2015-12-21
 * @company Linus Shops
 */
class Linus_Common_Trait_Cms
{
    protected $CMS_CSV_CACHE_KEY = 'Linus_Common_CMS_Csv_';
    protected $CMS_CSV_CACHE_LIFETIME = '86400';

    protected $blockCsvData = array();

    protected function parseCsvData($source)
    {
        $cacheKey = $this->CMS_CSV_CACHE_KEY.$this->getBlockId();
        $cache = Mage::app()->getCache();
        if (!$data = $cache->load($cacheKey)) {
            $sourceArray = explode(PHP_EOL, $source);
            $data = array();
            foreach ($sourceArray as $sourceItem) {
                $itemSplit = str_getcsv($sourceItem, ",", '"', "\\");
                $data[$this->normalizeKey((string)$itemSplit[0])] = (array_key_exists(1,
                    $itemSplit)) ? $itemSplit[1] : null;
            }

            $cache->save(
                $data,
                $cacheKey,
                array('Linus_Common'),
                $this->CMS_CSV_CACHE_LIFETIME
            );
        }
        return $data;
    }

    protected function normalizeKey($key)
    {
        return strtolower($key);
    }

    public function __call($name, $arguments)
    {
        if (!in_array('Mage_Core_Block_Template', class_parents($this))) {
            throw new Exception('Containing class must be a child of Mage_Core_Block_Template');
        }

        if (empty($this->blockCsvData)) {
            $this->blockCsvData = $this->parseCsvData($this->getCmsBlockHtml());
        }

        $key = $this->normalizeKey($name);
        return isset($this->blockCsvData[$key])
            ? $this->blockCsvData[$key]
            : '';
    }
}
