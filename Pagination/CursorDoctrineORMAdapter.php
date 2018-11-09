<?php

namespace Requestum\ApiBundle\Pagination;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

/**
 * Class CursorDoctrineORMAdapter
 */
class CursorDoctrineORMAdapter extends DoctrineORMAdapter implements CursorAdapterInterface
{
    /**
     * @var boolean
     */
    private  $forward;

    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private $generalPaginator;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder $query               A Doctrine ORM query or query builder.
     * @param Boolean                                        $fetchJoinCollection Whether the query joins a collection (true by default).
     * @param Boolean|null                                   $useOutputWalkers    Whether to use output walkers pagination mode
     * @param PaginationCursor|null                          $cursor
     */
    public function __construct($query, $fetchJoinCollection = true, $useOutputWalkers = null, $cursor = null)
    {
        if ($cursor && $cursor instanceof PaginationCursor) {
            $this->generalPaginator = new DoctrinePaginator($query, $fetchJoinCollection);
            $this->generalPaginator->setUseOutputWalkers($useOutputWalkers);

            $this->forward = $cursor->isForward();
            $query = $cursor->updateQueryBuilder($query);
        }

        parent::__construct($query, $fetchJoinCollection, $useOutputWalkers);
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        if ($this->generalPaginator) {
            return count($this->generalPaginator);
        }

        return parent::getNbResults();
    }

    /**
     * {@inheritdoc}
     */
    public function getNbCursorResults()
    {
        return parent::getNbResults();
    }

    /**
     * {@inheritdoc}
     */
    public function isCursorForward()
    {
        if ($this->generalPaginator && $this->forward) {
            return true;
        }

        return false;
    }
}
