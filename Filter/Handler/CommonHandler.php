<?php

namespace Requestum\ApiBundle\Filter\Handler;

use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Filter\ContextDataInterface;
use Requestum\ApiBundle\Util\QueryBuilderHelper;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class CommonFilterHandler.
 */
class CommonHandler extends AbstractHandler
{
    const DEFAULT_ROOT_ALIAS = 'e';

    /** @var int */
    private $parameterIndex = 0;

    /** @var string */
    private $rootAlias;

    /** @var string[] */
    protected $pathAliases = [];

    /**
     * @param string $rootAlias
     * @param array  $pathAliases
     */
    public function __construct($rootAlias = self::DEFAULT_ROOT_ALIAS, $pathAliases = [])
    {
        $this->rootAlias = $rootAlias;
        $this->pathAliases = $pathAliases;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($filter, $value)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryBuilder $builder, $filter, $value)
    {
        if (isset($this->pathAliases[$filter])) {
            $valueArray = [];
            PropertyAccess::createPropertyAccessor()->setValue($valueArray, $this->pathAliases[$filter], $value);
            $filter = key($valueArray);
            $value = $valueArray[$filter];
        }

        $this->processField($builder, $filter, $value);
    }

    /**
     * @param QueryBuilder $query
     * @param string       $field
     * @param string       $value
     * @param string|null  $parentAlias
     */
    private function processField(QueryBuilder $query, $field, $value, $parentAlias = null)
    {
        if (null === $value) {
            return;
        }

        $parentAlias = $parentAlias ?: $this->rootAlias.'.';

        // if assoc array dealing with join otherwise WHERE IN construction
        if (is_array($value) && !isset($value[0])) {
            $join = $parentAlias.$field;

            if (!QueryBuilderHelper::isJoined($query, $join)) {
                $query->leftJoin($join, $field);
            }

            foreach ($value as $subField => $subValue) {
                if (is_array($subValue) && array_values($subValue) !== $subValue) { // we have deeper join
                    $this->processField($query, $subField, $subValue, $field.'.');
                } else {
                    $this->addWhere($query, $field.'.'.$subField, $subValue);
                }
            }
        } elseif ($value instanceof ContextDataInterface) {
            $filters = $value->getFilters();
            if (count($filters) === 1) {
                $this->addWhere($query, $field, $filters[key($filters)]);
            } else {
                $join = $parentAlias.$field;
                $query->leftJoin($join, $field);
                foreach ($filters as $subField => $subValue) {
                    $this->addWhere($query, $field.'.'.$subField, $subValue);
                }
            }
        } else {
            $value = $value === 'true' ? true : $value;
            $value = $value === 'false' ? false : $value;

            $this->addWhere($query, $field, $value);
        }

        $dql = $query->getDQL();
    }

    /**
     * @param QueryBuilder $query
     * @param string       $field
     * @param string       $value
     */
    private function addWhere(QueryBuilder $query, $field, $value)
    {
        if (strpos($field, '.') !== false) {
            $realField = $field;
            $field = str_replace('.', '_', $field);
        } else {
            $realField = $this->rootAlias.'.'.$field;
        }

        $parameterName = $this->getParameterName($field);

        if (is_array($value)) {
            $query
                ->andWhere($query->expr()->in($realField, ':'.$parameterName))
                ->setParameter(':'.$parameterName, $value)
            ;
        } elseif (strpos($value, '<=') === 0) {
            $query
                ->andWhere($realField.' <= '.':'.$parameterName)
                ->setParameter(':'.$parameterName, trim(substr($value, 2)))
            ;
        } elseif (strpos($value, '<') === 0) {
            $query
                ->andWhere($realField.' < '.':'.$parameterName)
                ->setParameter(':'.$parameterName, trim(substr($value, 1)))
            ;
        } elseif (strpos($value, '>=') === 0) {
            $query
                ->andWhere($realField.' >= '.':'.$parameterName)
                ->setParameter(':'.$parameterName, trim(substr($value, 2)))
            ;
        } elseif (strpos($value, '>') === 0) {
            $query
                ->andWhere($realField.' > '.':'.$parameterName)
                ->setParameter(':'.$parameterName, trim(substr($value, 1)))
            ;
        } elseif (strpos($value, '<>') !== false) {
            list($value1, $value2) = explode('<>', $value);
            $parameterName1 = $parameterName.'1';
            $parameterName2 = $parameterName.'2';

            $query
                ->andWhere(sprintf('%s BETWEEN :%s AND :%s', $realField, $parameterName1, $parameterName2))
                ->setParameter($parameterName1, $value1)
                ->setParameter($parameterName2, $value2)
            ;
        } elseif (strpos($value, '!=') === 0) {
            $query
                ->andWhere($realField.' != '.':'.$parameterName)
                ->setParameter(':'.$parameterName, trim(substr($value, 2)))
            ;
        } elseif (strpos($value, '*') === 0 && strrpos($value, '*') === strlen($value) - 1) {
            $query
                ->andWhere($realField.' LIKE '.':'.$parameterName)
                ->setParameter(':'.$parameterName, '%'.substr($value, 1, -1).'%')
            ;
        } elseif (strpos($value, '*') === 0) {
            $query
                ->andWhere($realField.' LIKE  '.':'.$parameterName)
                ->setParameter(':'.$parameterName, '%'.substr($value, 1))
            ;
        } elseif (strrpos($value, '*') === strlen($value) - 1) {
            $query
                ->andWhere($realField.' LIKE '.':'.$parameterName)
                ->setParameter(':'.$parameterName, substr($value, 0, -1).'%')
            ;
        } elseif ($value === 'is_null_value') {
            $query
                ->andWhere($query->expr()->isNull($realField))
            ;
        } elseif ($value === 'is_not_null_value') {
            $query
                ->andWhere($query->expr()->isNotNull($realField))
            ;
        } else {
            $query
                ->andWhere($realField.' = :'.$parameterName)
                ->setParameter(':'.$parameterName, $value)
            ;
        }
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function getParameterName($field)
    {
        return $field.++$this->parameterIndex;
    }
}
