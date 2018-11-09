<?php

namespace Requestum\ApiBundle\Pagination;

/**
 * Interface CursorAdapterInterface
 */
interface CursorAdapterInterface
{
    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbCursorResults();

    /**
     * @return bool
     */
    public function isCursorForward();
}