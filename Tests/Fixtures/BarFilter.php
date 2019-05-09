<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Tests\Fixtures;

use Doctrine\ORM\Mapping\ClassMetadata;
use Fxp\Component\DoctrineExtensions\Filter\AbstractFilter;

/**
 * Fixture filter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class BarFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function doAddFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        $filter = '';

        try {
            if ($this->hasParameter('foo_boolean') && $this->getRealParameter('foo_boolean')) {
                $connection = $this->getEntityManager()->getConnection();
                $col = $this->getClassMetadata($targetEntity->getName())->getColumnName('foo');
                $filter .= $targetTableAlias.'.'.$col.' = '.$connection->quote('bar');
            }
        } catch (\Exception $e) {
            // nothing do
        }

        return $filter;
    }
}
