<?php

namespace Requestum\ApiBundle\Filter\Handler;

use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Util\QueryBuilderHelper;

/**
 * Class OrderHandler
 */
class OrderHandler extends AbstractByNameHandler
{
    /**
     * @var string
     */
    private $rootAlias;

    /**
     * SearchHandler constructor.
     * @param string $rootAlias
     */
    public function __construct($rootAlias = 'e')
    {
        $this->rootAlias = $rootAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryBuilder $builder, $filter, $value)
    {
        list($field, $direction) = explode('|', $value);

        $explodeFields = explode('.', $field);

        if (count($explodeFields) > 1) {
            $explodeFields = array_reverse($explodeFields);
            $orderByField = $explodeFields[1] . '.' . $explodeFields[0];
        } else {
            $orderByField = 'e.' . $field;
        }

        $joined = [];
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

        $builder
            ->orderBy($orderByField, $direction)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilterKey()
    {
        return 'order-by';
    }
}
