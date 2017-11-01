<?php

namespace Requestum\ApiBundle\Filter;

/**
 * Class FilterExpander
 */
class FilterExpander
{
    /**
     * @param array $filters
     * @return array
     */
    public function expand($filters)
    {
        $resultFilters = [];
        foreach ($filters as $path => $value) {
            $resultFilters = $this->setItem($resultFilters, $path, $value);
        }
        
        return $resultFilters; 
    }

    /**
     * @param array $arr
     * @param $path
     * @param $value
     * @return array
     */
    public function setItem(array $arr, $path, $value) {
        $path = explode('.', $path);
        $current = &$arr;
        foreach ($path as $item) {
            if (!isset($current[$item]) || !is_array($current[$item])) {
                $current[$item] = [];
            }
            $current = &$current[$item];
        }
        $current = $value;

        return $arr;
    }
}