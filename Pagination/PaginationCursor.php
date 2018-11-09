<?php

namespace Requestum\ApiBundle\Pagination;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\OrderBy;
use SebastianBergmann\CodeCoverage\Node\Builder;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class PaginationCursor
 */
class PaginationCursor
{
    const BOOLEAN_TRUE  = true;
    const BOOLEAN_FALSE = false;

    /**
     * @var string
     */
    protected $sequentialIdName;

    /**
     * @var string|integer
     */
    protected $sequentialIdValue;

    /**
     * @var ArrayCollection
     */
    protected $sortFields;

    /**
     * @var boolean
     */
    protected $forward;

    public function __construct()
    {
        $this->forward = self::BOOLEAN_FALSE;
        $this->sortFields = new ArrayCollection();
    }


    /**
     * @return ArrayCollection
     */
    public function getSortFields()
    {
        return $this->sortFields;
    }

    /**
     * @param array $sortFields
     *
     * @return PaginationCursor
     */
    public function setSortFields($sortFields)
    {
        $this->sortFields = $sortFields;
        foreach ($sortFields as $sortField) {
            $this->addSortField($sortField);
        }

        return $this;
    }

    /**
     * @param array $sortField
     *
     * @return PaginationCursor
     */
    public function addSortField($sortField)
    {
        if (!$this->sortFields->contains($sortField)) {
            $this->sortFields->add($sortField);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSequentialIdName()
    {
        return $this->sequentialIdName;
    }

    /**
     * @param string $sequentialIdName
     */
    public function setSequentialIdName($sequentialIdName)
    {
        $this->sequentialIdName = $sequentialIdName;
    }

    /**
     * @return string|integer
     */
    public function getSequentialIdValue()
    {
        return $this->sequentialIdValue;
    }

    /**
     * @param string|integer $sequentialIdValue
     */
    public function setSequentialIdValue($sequentialIdValue)
    {
        $this->sequentialIdValue = $sequentialIdValue;
    }

    /**
     * @return bool
     */
    public function isForward()
    {
        return $this->forward;
    }

    /**
     * @param bool $forward
     */
    public function setForward($forward)
    {
        $this->forward = $forward;
    }

    /**
     * @param string $string
     *
     * @return PaginationCursor
     */
    static function createCursorObjectFromBaseCode($string)
    {
        $json = base64_decode($string);
        $data = json_decode($json, true);

        $cursorObject = new self();
        $cursorObject->setForward(isset($data['forward']) ? $data['forward'] : false);
        $cursorObject->setSequentialIdName(isset($data['sequentialIdName']) ? $data['sequentialIdName'] : null);
        $cursorObject->setSequentialIdValue(isset($data['sequentialIdValue']) ? $data['sequentialIdValue'] : null);
        $cursorObject->setSortFields(isset($data['sortFields']) ? new ArrayCollection($data['sortFields']) : []);

        return $cursorObject;
    }

    /**
     * @param QueryBuilder|Builder  $entitiesQueryBuilder
     * @param object                $entity
     * @param boolean               $isForward
     *
     * @return string
     * @throws \Exception
     */
    static function creteCursorInBaseCode($entitiesQueryBuilder, $entity, $isForward)
    {
        $orderByParts = $entitiesQueryBuilder->getDQLPart('orderBy');
        $joins = $entitiesQueryBuilder->getDQLPart('join');
        $rootAlias = $entitiesQueryBuilder->getRootAlias();

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $cursor = new self();
        $cursor->setForward($isForward);

        /** @var OrderBy $orderByPart * */
        foreach ($orderByParts as $key => $orderByPart) {
            $part = explode(' ', $orderByPart->getParts()[0]);
            $fieldPathArray = explode('.', $part[0]);

            $fieldSortType = $part[1];
            $fieldPath = CursorFieldPathHelper::getFieldPathFromQuery($fieldPathArray, $rootAlias, $joins);
            $fieldValue = $propertyAccessor->getValue($entity, $fieldPath);

            if ($key == count($orderByParts) - 1) {
                $cursor->setSequentialIdName($fieldPath);
                $cursor->setSequentialIdValue($fieldValue);
                continue;
            }

            $cursor->addSortField(
                [
                    'fieldName'     => $fieldPath,
                    'fieldValue'    => $fieldValue,
                    'sortType'      => $fieldSortType,
                ]
            );
        }

        return $cursor->getBaseCode();
    }

    /**
     * @return string
     */
    public function getBaseCode()
    {
        $data =
            [
                'sortFields'        => $this->getSortFields()->toArray(),
                'sequentialIdName'  => $this->getSequentialIdName(),
                'sequentialIdValue' => $this->getSequentialIdValue(),
                'forward'           => $this->isForward(),
            ];
        $json = json_encode($data, true);

        return base64_encode($json);
    }


    /**
     * @param QueryBuilder|Builder $entitiesQueryBuilder
     *
     * @return bool
     * @throws \Exception
     */
    public function checkCursorByQueryBuilder($entitiesQueryBuilder)
    {
        $rootAlias = $entitiesQueryBuilder->getRootAlias();
        $joins = $orderByParts = $entitiesQueryBuilder->getDQLPart('join');
        $orderByParts = $entitiesQueryBuilder->getDQLPart('orderBy');
        // unset orderById
        unset($orderByParts[count($orderByParts) - 1]);

        if ($this->getSortFields()->count() != count($orderByParts)) {
            throw new \Exception('Unable to process field');
        }

        /** @var OrderBy $orderByPart */
        foreach ($orderByParts as $key => $orderByPart) {
            $part = explode(' ', $orderByPart->getParts()[0]);
            $fieldSortType = $part[1];
            $fieldPathArray = explode('.', $part[0]);
            $fieldPath = CursorFieldPathHelper::getFieldPathFromQuery($fieldPathArray, $rootAlias, $joins);

            $issetField = $this->getSortFields()->exists(function ($key, $sortField) use ($fieldPath, $fieldSortType) {
                return $sortField['fieldName'] == $fieldPath && $sortField['sortType'] == $fieldSortType;
            });

            if (!$issetField) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param QueryBuilder|Builder $qb
     *
     * @return QueryBuilder|Builder
     * @throws \Exception
     */
    public function updateQueryBuilder($qb)
    {
        $rootAlias = $qb->getRootAlias();

        if ($this->getSortFields()->count()) {
            $andEqualityCondition = $qb->expr()->andX();
            $andDifferenceCondition = $qb->expr()->andX();

            $queryParameters = [];

            foreach ($this->getSortFields() as $key => $field) {
                if (!preg_match('/./', $field['fieldName'])) {
                    $field['fieldName'] = $rootAlias . '.' . $field['fieldName'];
                }

                $andEqualityCondition->add($qb->expr()->eq($field['fieldName'], '?' . $key));


                if ($this->isConditionMore($field['sortType'])) {
                    $andDifferenceCondition->add($qb->expr()->gt($field['fieldName'], '?' . $key));
                } else {
                    $andDifferenceCondition->add($qb->expr()->lt($field['fieldName'], '?' . $key));
                }

                $queryParameters[$key] = $field['fieldValue'];
            }

            if ($this->isForward()) {
                $andEqualityCondition->add($qb->expr()->lt($rootAlias . '.' . $this->getSequentialIdName(), $this->getSequentialIdValue()));
            } else {
                $andEqualityCondition->add($qb->expr()->gt($rootAlias . '.' . $this->getSequentialIdName(), $this->getSequentialIdValue()));
            }


            $qb
                ->andWhere($qb->expr()->orX(
                    $andEqualityCondition,
                    $andDifferenceCondition
                ))
                ->setParameters($queryParameters)
            ;

        } else {
            $qb
                ->andWhere($rootAlias . '.' . $this->sequentialIdName .' > :sequentialIdValue')
                ->setParameters(
                    [
                        'sequentialIdValue' =>$this->getSequentialIdValue(),
                    ]
                );
        };

        return $qb;
    }

    /**
     * @param string $sortType
     *
     * @return boolean
     * @throws \Exception
     */
    protected function isConditionMore($sortType)
    {
        switch ($sortType) {
            case 'asc':
                return $this->isForward() ? false : true;
                break;

            case 'desc':
                return $this->isForward()? true : false;
                break;

            default:
                throw new \Exception('Sorting type is unknown');
                break;
        }
    }
}
