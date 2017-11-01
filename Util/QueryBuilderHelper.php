<?php

namespace Requestum\ApiBundle\Util;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Requestum\ApiBundle\Filter\Handler\CommonHandler;

/**
 * Class QueryBuilderHelper.
 */
class QueryBuilderHelper
{
    /**
     * @param QueryBuilder $builder
     * @param string       $join
     * @param string       $rootAlias
     *
     * @return bool
     */
    public static function isJoined(QueryBuilder $builder, $join, $rootAlias = CommonHandler::DEFAULT_ROOT_ALIAS)
    {
        $part = $builder->getDQLPart('join');

        $joined = false;

        if (count($part) && isset($part[$rootAlias])) {
            /** @var Join $joinExpr */
            foreach ($part[$rootAlias] as $joinExpr) {
                if ($joinExpr->getJoin() === $join) {
                    $joined = true;
                    break;
                }
            }
        }

        return $joined;
    }

    /**
     * @param QueryBuilder $builder
     * @param string       $join
     * @param string       $alias
     * @param string       $rootAlias
     */
    public static function leftJoinOnce(QueryBuilder $builder, $join, $alias, $rootAlias = CommonHandler::DEFAULT_ROOT_ALIAS)
    {
        if (!self::isJoined($builder, $join, $rootAlias)) {
            $builder->leftJoin($join, $alias);
        }
    }

    /**
     * @param QueryBuilder $builder
     * @param string       $join
     * @param string       $alias
     * @param string       $rootAlias
     */
    public static function joinOnce(QueryBuilder $builder, $join, $alias, $rootAlias = CommonHandler::DEFAULT_ROOT_ALIAS)
    {
        if (!self::isJoined($builder, $join, $rootAlias)) {
            $builder->join($join, $alias);
        }
    }
}
