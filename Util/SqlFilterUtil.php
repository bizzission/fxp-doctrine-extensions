<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Util;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Sonatra\Component\DoctrineExtensions\Filter\EnableFilterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SqlFilterUtil
{
    /**
     * Get the list of SQL Filter name must to be disabled.
     *
     * @param ObjectManager|null $om      The object manager instance
     * @param string[]           $filters The list of SQL Filter
     * @param bool               $all     Force all SQL Filter
     *
     * @return string[]
     */
    public static function findFilters($om, array $filters, $all = false)
    {
        if (!$om instanceof EntityManagerInterface || (empty($filters) && !$all)) {
            return array();
        }

        $all = ($all && !empty($filters)) ? false : $all;
        $enabledFilters = self::getEnabledFilters($om);

        return self::doFindFilters($filters, $enabledFilters, $all);
    }

    /**
     * Get the enabled sql filters.
     *
     * @param ObjectManager|null $om The object manager instance
     *
     * @return SQLFilter[]
     */
    public static function getEnabledFilters($om)
    {
        $filters = array();

        if ($om instanceof EntityManagerInterface) {
            $enabledFilters = $om->getFilters()->getEnabledFilters();

            foreach ($enabledFilters as $name => $filter) {
                if (!$filter instanceof EnableFilterInterface
                        || ($filter instanceof EnableFilterInterface && $filter->isEnabled())) {
                    $filters[$name] = $filter;
                }
            }
        }

        return $filters;
    }

    /**
     * Enable the SQL Filters.
     *
     * @param ObjectManager|null $om      The object manager instance
     * @param string[]           $filters The list of SQL Filter
     */
    public static function enableFilters($om, array $filters)
    {
        static::actionFilters($om, 'enable', $filters);
    }

    /**
     * Disable the SQL Filters.
     *
     * @param ObjectManager|null $om      The object manager instance
     * @param string[]           $filters The list of SQL Filter
     */
    public static function disableFilters($om, array $filters)
    {
        static::actionFilters($om, 'disable', $filters);
    }

    /**
     * Check if the filter is enabled.
     *
     * @param ObjectManager|null $om   The object manager instance
     * @param string             $name The filter name
     *
     * @return bool
     */
    public static function isEnabled($om, $name)
    {
        if ($om instanceof EntityManagerInterface) {
            $sqlFilters = $om->getFilters();

            if ($sqlFilters->isEnabled($name)) {
                $filter = $sqlFilters->getFilter($name);

                return !$filter instanceof EnableFilterInterface
                    || ($filter instanceof EnableFilterInterface && $filter->isEnabled());
            }
        }

        return false;
    }

    /**
     * Do find filters.
     *
     * @param string[]    $filters        The filters names to be found
     * @param SQLFilter[] $enabledFilters The enabled SQL Filters
     * @param bool        $all            Force all SQL Filter
     *
     * @return array
     */
    protected static function doFindFilters(array $filters, array $enabledFilters, $all)
    {
        $reactivateFilters = array();

        foreach ($enabledFilters as $name => $filter) {
            if (in_array($name, $filters) || $all) {
                $reactivateFilters[] = $name;
            }
        }

        return $reactivateFilters;
    }

    /**
     * Disable/Enable the SQL Filters.
     *
     * @param ObjectManager|null $om      The object manager instance
     * @param string             $action  The value (disable|enable)
     * @param string[]           $filters The list of SQL Filter
     */
    protected static function actionFilters($om, $action, array $filters)
    {
        if ($om instanceof EntityManagerInterface) {
            $sqlFilters = $om->getFilters();

            foreach ($filters as $name) {
                if ($sqlFilters->isEnabled($name)
                        && ($filter = $sqlFilters->getFilter($name)) instanceof EnableFilterInterface) {
                    $filter->$action();
                } else {
                    $sqlFilters->$action($name);
                }
            }
        }
    }
}
