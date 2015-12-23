<?php

/**
 * Provides helper methods for fetching csv formatted key value pairs
 * out of static blocks and dumping it into other blocks.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2015-12-22
 * @company Linus Shops
 */
class Linus_Common_Helper_Cms extends Mage_Core_Helper_Abstract
{
    /** @var string Cache key prefix */
    protected $CMS_CSV_CACHE_KEY = 'Linus_Common_CMS_Csv_';
    /** @var string Default length that the key data should be stored. */
    protected $CMS_CSV_CACHE_LIFETIME = '86400';
    /** @var bool If false, will always parse from the database */
    protected $CMS_CSV_CACHE_ENABLED = false;

    /**
     * @param $blockId
     * @return Mage_Cms_Block_Block
     */
    public function getCsvBlock($blockId)
    {
        return Mage::app()->getLayout()->createBlock('cms/block')
            ->setBlockId($blockId)
            ->setStoreId(Mage::app()->getStore()->getId())
        ;
    }

    /**
     * Given a cms block, parses it as a csv of
     * key-value pairs into an array.
     * @param Mage_Cms_Block_Block $cmsBlock
     * @return array
     */
    public function parseCsvData($cmsBlock)
    {
        $source = $cmsBlock->toHtml();
        $cacheKey = $this->CMS_CSV_CACHE_KEY.$cmsBlock->getBlockId();
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
        return strtolower(preg_replace("/[^A-Za-z0-9_ ]/", '', $key));
    }

    /**
     * Fetch the csv key-value pairs from a cms static block, and inject
     * them into the provided destination block.
     * @param Mage_Cms_Block_Block $cmsBlock
     * @param Mage_Core_Block_Abstract $destinationBlock
     * @return mixed
     * @throws Exception
     */
    public function loadCsvDataFromCmsBlock($cmsBlock, $destinationBlock)
    {
        if (!in_array('Mage_Core_Block_Abstract', class_parents($cmsBlock))) {
            throw new Exception('Data class must be a child of Mage_Core_Block_Abstract');
        }

        $data = $this->parseCsvData($cmsBlock);

        $keys = array();

        foreach ($data as $key => $value) {
            if (empty($key)) {
                continue;
            }
            $destinationBlock->setData($key, $value);
            $keys[] = $key;
        }

        $destinationBlock->setData('csv_keys', $keys);

        return $destinationBlock;
    }
}
