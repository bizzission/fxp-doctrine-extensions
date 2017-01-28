<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\Fixtures;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sonatra\Component\DoctrineExtensions\Filter\AbstractFilter;

/**
 * Fixture filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BarFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $filter = '';

        if ($this->hasParameter('foo_boolean') && $this->getRealParameter('foo_boolean')) {
            $connection = $this->getEntityManager()->getConnection();
            $col = $this->getClassMetadata($targetEntity->getName())->getColumnName('foo');
            $filter .= $targetTableAlias.'.'.$col.' = '.$connection->quote('bar');
        }

        return $filter;
    }
}
