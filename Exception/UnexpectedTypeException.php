<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Exception;

use Symfony\Component\Validator\Exception\UnexpectedTypeException as BaseUnexpectedTypeException;

/**
 * Base UnexpectedTypeException for the doctrine extensions component.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class UnexpectedTypeException extends BaseUnexpectedTypeException implements ExceptionInterface
{
}
