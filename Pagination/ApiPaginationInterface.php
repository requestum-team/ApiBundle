<?php

namespace Requestum\ApiBundle\Pagination;

/**
 * Interface ApiPaginationInterface
 */
interface ApiPaginationInterface
{
    /**
     * @return boolean
     */
    public  function hasPreviousCursor();

    /**
     * @return string|null
     */
    public function getPreviousCursor();

    /**
     * @return boolean
     */
    public function hasNextCursor();

    /**
     * @return string|null
     */
    public function getNextCursor();

}
