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

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Fixture class for doctrine callback validator.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FooCallbackValidatorClass
{
    /**
     * Validates static method in class.
     *
     * @param object                    $object
     * @param ExecutionContextInterface $context
     *
     * @return bool
     */
    public static function validateCallback($object, ExecutionContextInterface $context)
    {
        $context->addViolation('Callback message', array('{{ value }}' => 'foobar'));

        return false;
    }
}
