<?php

namespace Requestum\ApiBundle\Filter\Handler;

use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Util\QueryBuilderHelper;

/**
 * Class AbstractQueryHandler base handler to process search queries via LIKE statement on multiple fields
 */
class SearchHandler extends AbstractByNameHandler
{
    /**
     * @var array
     */
    private $searchFields = [];

    /**
     * @var string
     */
    private $rootAlias;

    /**
     * SearchHandler constructor.
     * @param array $searchFields
     * @param string $rootAlias
     */
    public function __construct(array $searchFields, $rootAlias = 'e')
    {
        $this->searchFields = $searchFields;
        $this->rootAlias = $rootAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryBuilder $builder, $filter, $value)
    {
        $whereExpr = $builder
            ->expr()
            ->orX();

        $joined = [];
        foreach ($this->getSearchFields() as $field) {
            $prevJoinColumn = $joinColumn = $this->rootAlias;

            while (false !== $dotPosition = strpos($field, '.')) {
                $joinColumn = substr($field, 0, $dotPosition);
                $field = substr($field, $dotPosition + 1);

                if (!in_array($joinColumn, $joined, true)) {
                    $joined[] = $joinColumn;
                    QueryBuilderHelper::leftJoinOnce($builder, $prevJoinColumn.'.'.$joinColumn, $joinColumn, $this->rootAlias);
                }
                $prevJoinColumn = $joinColumn;
            }

            $whereExpr
                ->add(sprintf('%s.%s LIKE :query', $joinColumn, $field));
        }

        $builder
            ->andWhere($whereExpr)
            ->setParameter('query', $this->formatValue($value));
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function formatValue($value)
    {
        if ($value[0] === '*') {
            $value[0] = '%';
        }

        if ($value[strlen($value) - 1] === '*') {
            $value[strlen($value) - 1] = '%';
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilterKey()
    {
        return 'query';
    }

    /**
     * @return array Returns list for fields to search. Supports fields in referenced entities in following format: "reference.reference_field",
     *               reference deep is unlimited, so in this case "vehicle.vehicleModel.name" two joins will be performed
     */
    protected function getSearchFields()
    {
        return $this->searchFields;
    }
}
