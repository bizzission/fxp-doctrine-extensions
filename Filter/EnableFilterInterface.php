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

/**
 * Interface of enable doctrine sql filter.
 *
 * @author François Pluchino <francois.pluchino@helloguest.com>
 */
interface EnableFilterInterface
{
    /**
     * Enable the filter.
     *
     * @return self
     */
    public function enable();

    /**
     * Disable the filter.
     *
     * @return self
     */
    public function disable();

    /**
     * Check if the filter is enabled.
     *
     * @return bool
     */
    public function isEnabled();
}
