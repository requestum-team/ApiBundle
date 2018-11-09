<?php

namespace Requestum\ApiBundle\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;

/**
 * Class ApiPagination
 */
class ApiPagination extends Pagerfanta implements ApiPaginationInterface
{
    private $currentPageResults;

    /**
     * @var QueryBuilder|Builder $entitiesQueryBuilder
     */
    protected $entitiesQueryBuilder;

    /**
     * ApiPagination constructor.
     * @param QueryBuilder|Builder  $entitiesQueryBuilder
     * @param AdapterInterface      $adapter
     */
    public function __construct($entitiesQueryBuilder, AdapterInterface $adapter)
    {
        parent::__construct($adapter);

        $this->entitiesQueryBuilder = $entitiesQueryBuilder;
    }

    /**
     * Returns the results for the current page.
     *
     * @return array|\Traversable
     */
    public function getCurrentPageResults()
    {
        if ($this->notCachedCurrentPageResults()) {
            $this->currentPageResults = $this->getCurrentPageResultsFromAdapter();
        }

        return $this->currentPageResults;
    }

    /**
     * {@inheritdoc}
     */
    private function notCachedCurrentPageResults()
    {
        return $this->currentPageResults === null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageResultsFromAdapter()
    {
        $offset = $this->calculateOffsetForCurrentPageResults();
        $length = $this->getMaxPerPage();

        if ($this->getAdapter()->isCursorForward()) {
            $offset = 0;
            $nbCursorResults = $this->getAdapter()->getNbCursorResults();
            if ($nbCursorResults > $this->getMaxPerPage()) {
                $offset = $nbCursorResults - $this->getMaxPerPage();
            }
        }

        return $this->getAdapter()->getSlice($offset, $length);
    }

    /**
     * {@inheritdoc}
     */
    private function calculateOffsetForCurrentPageResults()
    {
        return ($this->getCurrentPage() - 1) * $this->getMaxPerPage();
    }

    /**
     * @return bool
     */
    public  function hasPreviousCursor()
    {
        if (($this->getAdapter()->isCursorForward() && $this->getAdapter()->getNbCursorResults() > $this->getMaxPerPage()) ||
            !$this->getAdapter()->isCursorForward() && $this->getAdapter()->getNbResults() > $this->getAdapter()->getNbCursorResults()) {
            return true;
        }

        return false;
    }

    /**
     * @return null|string
     */
    public function getPreviousCursor()
    {
        if (!$this->hasPreviousCursor()) {
            return null;
        }

        $fistResult = $this->getCurrentPageResults()[0];

        return PaginationCursor::creteCursorInBaseCode($this->entitiesQueryBuilder, $fistResult, true);
    }

    /**
     * @return bool
     */
    public function hasNextCursor()
    {
        if (($this->getAdapter()->isCursorForward() && $this->getAdapter()->getNbResults() > $this->getAdapter()->getNbCursorResults()) ||
            !$this->getAdapter()->isCursorForward() && $this->getAdapter()->getNbCursorResults() > $this->getMaxPerPage()) {
            return true;
        }

        return false;
    }

    /**
     * @return null|string
     */
    public function getNextCursor()
    {
        if (!$this->hasNextCursor()) {
            return null;
        }

        $results = $this->getCurrentPageResults();
        $lastResult = $results[count($results) - 1];

        return PaginationCursor::creteCursorInBaseCode($this->entitiesQueryBuilder, $lastResult, false);
    }

}
