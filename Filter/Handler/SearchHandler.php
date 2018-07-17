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

        foreach ($this->getSearchFields() as $field) {

            if(is_array($field)) {

                $concatPaths = [];

                foreach ($field as $concatField) {

                    [$concatFieldJoinColumn, $concatField] = $this->processPath($builder, $concatField);
                    $concatPaths[] = $concatFieldJoinColumn.'.'.$concatField;
                }

                $concatExpr = implode(", ' ', ", $concatPaths);
                $queryExpr = sprintf('CONCAT(%s)', $concatExpr);

            } else {

                list($joinColumn, $field) = $this->processPath($builder, $field);
                $queryExpr = $joinColumn.'.'.$field;
            }

            $whereExpr
                ->add(sprintf('%s LIKE :query', $queryExpr));
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


    /**
     * Parse path, join tables, return last join column and field name
     *
     * @param QueryBuilder $builder
     * @param string $path
     * @return array
     */
    protected function processPath(QueryBuilder $builder, $path)
    {
        $joinColumn = $prevJoinColumn = $this->rootAlias;

        while (false !== $dotPosition = strpos($path, '.')) {
            $joinColumn = substr($path, 0, $dotPosition);
            $path = substr($path, $dotPosition + 1);

            QueryBuilderHelper::leftJoinOnce($builder, $prevJoinColumn.'.'.$joinColumn, $joinColumn, $this->rootAlias);

            $prevJoinColumn = $joinColumn;
        }

        return [$joinColumn, $path];
    }
}
