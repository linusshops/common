<?php

/**
 * Array manipulation and search methods.
 *
 * @author Sam Schmidt <samuel@dersam.net>
 */
class Linus_Common_Helper_Array extends Mage_Core_Helper_Abstract
{
    /**
     * Descend into an array based on the given dot-delimited path, and
     * retrieve the data at that location. If the path is invalid, return
     * the $default.
     *
     * @param $data
     * @param $path
     * @param $default
     * @return mixed
     */
    public function get($data, $path, $default=null)
    {
        $pathspec = explode('.', $path);
        $register = $data;

        if (isset($data[$path])) {
            $register = $data[$path];
        } else {
            foreach ($pathspec as $p) {
                if (is_array($register) && isset($register[$p])) {
                    //Standard array descent
                    $register = $register[$p];
                } else {
                    //Once a path element is invalid, the rest of the path is worthless.
                    $register = $default;
                    break;
                }
            }
        }

        return $register;
    }
}
