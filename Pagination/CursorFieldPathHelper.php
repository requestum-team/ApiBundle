<?php

namespace Requestum\ApiBundle\Pagination;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\OrderBy;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class CursorFieldPathHelper
 */
class CursorFieldPathHelper
{

    /**
     * @param array     $fieldPathArray
     * @param string    $rootAlias
     * @param array     $joins
     *
     * @throws \Exception
     * @return string
     */
    static function getFieldPathFromQuery($fieldPathArray, $rootAlias, $joins)
    {
            if ($fieldPathArray[0] == $rootAlias) {
                unset($fieldPathArray[0]);

                return implode('.', $fieldPathArray);
            }

            $aliasPathArray = null;
            /** @var Join $join */
            foreach ($joins[$rootAlias] as $join) {
                if ($join->getAlias() == $fieldPathArray[0]) {
                    unset($fieldPathArray[0]);
                    $aliasPathArray = explode('.', $join->getJoin());
                    break;
                }
            }

            if ($aliasPathArray === null) {
                throw new \Exception('Unable to process field');
            }

            $fieldPathArray = array_merge($aliasPathArray, $fieldPathArray);

            return self::getFieldPathFromQuery($fieldPathArray, $rootAlias, $joins);
    }

}
