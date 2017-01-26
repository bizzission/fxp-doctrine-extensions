<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Base of Doctrine Filter.
 *
 * @author François Pluchino <francois.pluchino@helloguest.com>
 */
abstract class AbstractFilter extends SQLFilter implements EnableFilterInterface
{
    /**
     * @var EntityManagerInterface|null
     */
    private $entityManager;

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * @var \ReflectionProperty|null
     */
    private $refParameters;

    /**
     * {@inheritdoc}
     */
    public function enable()
    {
        $this->enable = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
        $this->enable = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enable;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($this->isEnabled()) {
            return $this->doAddFilterConstraint($targetEntity, $targetTableAlias);
        }

        return '';
    }

    /**
     * Gets the SQL query part to add to a query.
     *
     * The constraint SQL if there is available, empty string otherwise.
     *
     * @param ClassMetaData $targetEntity     The class metadata of target entity
     * @param string        $targetTableAlias The table alias of target entity
     *
     * @return string
     */
    abstract protected function doAddFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias);

    /**
     * Get the entity manager.
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        if (null === $this->entityManager) {
            $ref = new \ReflectionProperty(SQLFilter::class, 'em');
            $ref->setAccessible(true);
            $this->entityManager = $ref->getValue($this);
        }

        return $this->entityManager;
    }

    /**
     * Gets a parameter to use in a query without the output escaping.
     *
     * @param string $name The name of the parameter
     *
     * @return string|string[]|bool|bool[]|int|int[]|float|float[]|null
     *
     * @throws \InvalidArgumentException
     */
    protected function getRealParameter($name)
    {
        $this->getParameter($name);

        if (null === $this->refParameters) {
            $this->refParameters = new \ReflectionProperty(SQLFilter::class, 'parameters');
            $this->refParameters->setAccessible(true);
        }

        $parameters = $this->refParameters->getValue($this);

        return $parameters[$name]['value'];
    }
}
