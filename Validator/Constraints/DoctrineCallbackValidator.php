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

use Doctrine\Common\Persistence\ManagerRegistry;
use Fxp\Component\DoctrineExtensions\Exception\ConstraintDefinitionException;
use Fxp\Component\DoctrineExtensions\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for Callback constraint with Doctrine Entity Manager.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class DoctrineCallbackValidator extends ConstraintValidator
{
    protected $registry;

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $object     The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     *
     * @throws UnexpectedTypeException       When constraint is not an instance of DoctrineCallback
     * @throws ConstraintDefinitionException When the targeted by Callback constraint is not a valid callable
     * @throws ConstraintDefinitionException When the targeted by Callback constraint does not exist
     * @throws \ReflectionException
     */
    public function validate($object, Constraint $constraint): void
    {
        if (!$constraint instanceof DoctrineCallback) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\DoctrineCallback');
        }

        $res = $this->doValidateCallback($object, $constraint);

        if (!$res && null !== $object) {
            $this->callObjectMethod($object, $constraint);
        }
    }

    /**
     * @param mixed            $object
     * @param DoctrineCallback $constraint
     *
     * @throws ConstraintDefinitionException
     *
     * @return bool
     */
    private function doValidateCallback($object, DoctrineCallback $constraint): bool
    {
        $callback = $constraint->callback;

        if (\is_array($callback) || $callback instanceof \Closure) {
            if (!\is_callable($callback)) {
                throw new ConstraintDefinitionException(sprintf('"%s::%s" targeted by Callback constraint is not a valid callable', $callback[0], $callback[1]));
            }

            $callback($object, $this->context, $this->registry);

            return true;
        }

        return false;
    }

    /**
     * @param mixed            $object
     * @param DoctrineCallback $constraint
     *
     * @throws ConstraintDefinitionException
     * @throws \ReflectionException
     */
    private function callObjectMethod($object, DoctrineCallback $constraint): void
    {
        $callback = (string) $constraint->callback;

        if (!method_exists($object, $callback)) {
            throw new ConstraintDefinitionException(sprintf('Method "%s" targeted by Callback constraint does not exist', $callback));
        }

        $reflMethod = new \ReflectionMethod($object, $callback);

        if ($reflMethod->isStatic()) {
            $reflMethod->invoke(null, $object, $this->context, $this->registry);
        } else {
            $reflMethod->invoke($object, $this->context, $this->registry);
        }
    }
}
