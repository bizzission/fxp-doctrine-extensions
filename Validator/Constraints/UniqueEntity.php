<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Validator\Constraints;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity as BaseUniqueEntity;

/**
 * Constraint for the Unique Entity validator with disable sql filter option.
 *
 * @Annotation
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class UniqueEntity extends BaseUniqueEntity
{
    public $service = 'fxp.doctrine_extensions.orm.validator.unique';
    public $filters = [];
    public $allFilters = true;
}
