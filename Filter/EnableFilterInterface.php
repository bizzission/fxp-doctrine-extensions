<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Filter;

/**
 * Interface of enable doctrine sql filter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
interface EnableFilterInterface
{
    /**
     * Enable the filter.
     *
     * @return EnableFilterInterface
     */
    public function enable(): EnableFilterInterface;

    /**
     * Disable the filter.
     *
     * @return EnableFilterInterface
     */
    public function disable(): EnableFilterInterface;

    /**
     * Check if the filter is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
