<?php

namespace Requestum\ApiBundle\Filter\Handler;

use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Util\QueryBuilderHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

        $searchFields = $this->getSearchFields($value);

        foreach ($searchFields as $field) {

            if(is_array($field)) {

                $concatPaths = [];

                foreach ($field as $concatField) {

                    [$concatFieldJoinColumn, $concatField] = $this->processPath($builder, $concatField);
                    $concatPaths[] = sprintf("COALESCE(%s,'')", $concatFieldJoinColumn.'.'.$concatField);
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
     * @param string|array $value
     *
     * @return string
     */
    protected function formatValue($value)
    {
        if(is_array($value)) {

            if (!isset($value['term'])) {
                throw new BadRequestHttpException('Wrong query format. No search term specified.');
            }

            $value = $value['term'];
        }

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
     * @param $value
     * @return array Returns list for fields to search. Supports fields in referenced entities in following format: "reference.reference_field",
     *               reference deep is unlimited, so in this case "vehicle.vehicleModel.name" two joins will be performed
     */
    protected function getSearchFields($value = null)
    {
        if (is_array($value)) {

            if (!isset($value['fields'])) {
                throw new BadRequestHttpException('Wrong query format. No search fields specified.');
            }

            $queryFields = explode(',',$value['fields']);
            $fields = [];

            foreach ($this->searchFields as $key => $searchField) {
                $fieldName = is_array($searchField) ? $key : $searchField;

                if (($fieldKey = array_search($fieldName, $queryFields)) !== false) {
                    $fields[] = $searchField;
                    unset($queryFields[$fieldKey]);
                }
            }

            if (!empty($queryFields)) {
                throw new BadRequestHttpException('Undefined query fields: '.implode(', ',$queryFields));
            }

            return $fields;
        } else {
            return $this->searchFields;
        }
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
