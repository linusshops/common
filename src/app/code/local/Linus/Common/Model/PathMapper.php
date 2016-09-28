<?php

/**
 *
 *
 * @author Sam Schmidt <samuel@dersam.net>
 * @since 2016-09-28
 */
class Linus_Common_Model_PathMapper
{
    protected static $paths = null;

    public function __construct()
    {
        if (self::$paths == null) {
            Mage::dispatchEvent('common_block_template_update', [
                'path_mapper' => $this,
            ]);

            if (self::$paths == null) {
                self::$paths = [];
            }
        }
    }

    public function addPath($original, $new)
    {
        self::$paths[$original] = $new;
    }

    public function addPaths($paths)
    {
        foreach ($paths as $original=>$new) {
            $this->addPath($original, $new);
        }
    }

    /**
     * @param $originalTemplatePath
     * @return bool|string
     */
    public function getRemap($originalTemplatePath)
    {
        return isset(self::$paths[$originalTemplatePath])
            ? self::$paths[$originalTemplatePath]
            : false
        ;
    }
}
