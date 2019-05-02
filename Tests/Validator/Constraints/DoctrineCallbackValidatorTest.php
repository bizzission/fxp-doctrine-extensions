<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Tests\Validator\Constraints;

use Doctrine\Common\Persistence\ManagerRegistry;
use Fxp\Component\DoctrineExtensions\Tests\Fixtures\FooCallbackValidatorClass;
use Fxp\Component\DoctrineExtensions\Tests\Fixtures\FooCallbackValidatorObject;
use Fxp\Component\DoctrineExtensions\Validator\Constraints\DoctrineCallback;
use Fxp\Component\DoctrineExtensions\Validator\Constraints\DoctrineCallbackValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Tests case for doctrine callback validator.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class DoctrineCallbackValidatorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var DoctrineCallbackValidator
     */
    protected $validator;

    protected function setUp(): void
    {
        $entityManagerName = 'foo';
        $em = DoctrineTestHelper::createTestEntityManager();
        /** @var ManagerRegistry $registry */
        $registry = $this->createRegistryMock($entityManagerName, $em);
        $context = $this->getMockBuilder(ExecutionContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->context = $context;
        $this->validator = new DoctrineCallbackValidator($registry);
        /* @var ExecutionContextInterface $context */
        $this->validator->initialize($context);
    }

    protected function tearDown(): void
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid(): void
    {
        $this->context->expects($this->never())
            ->method('addViolation')
        ;

        $this->validator->validate(null, new DoctrineCallback('foo'));
    }

    public function testSingleMethod(): void
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback('validate');

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate($object, $constraint);
    }

    public function testSingleMethodExplicitName(): void
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback(['callback' => 'validate']);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate($object, $constraint);
    }

    public function testSingleStaticMethod(): void
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback('validateStatic');

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Static message', [
                '{{ value }}' => 'baz',
            ])
        ;

        $this->validator->validate($object, $constraint);
    }

    public function testClosure(): void
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback(function ($object, ExecutionContextInterface $context) {
            $context->addViolation('My message', ['{{ value }}' => 'foobar']);

            return false;
        });

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate($object, $constraint);
    }

    public function testClosureNullObject(): void
    {
        $constraint = new DoctrineCallback(function ($object, ExecutionContextInterface $context) {
            $context->addViolation('My message', ['{{ value }}' => 'foobar']);

            return false;
        });

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate(null, $constraint);
    }

    public function testClosureExplicitName(): void
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback([
            'callback' => function ($object, ExecutionContextInterface $context) {
                $context->addViolation('My message', ['{{ value }}' => 'foobar']);

                return false;
            },
        ]);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate($object, $constraint);
    }

    public function testArrayCallable(): void
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback([FooCallbackValidatorClass::class, 'validateCallback']);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Callback message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate($object, $constraint);
    }

    public function testArrayCallableNullObject(): void
    {
        $constraint = new DoctrineCallback([FooCallbackValidatorClass::class, 'validateCallback']);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Callback message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate(null, $constraint);
    }

    public function testArrayCallableExplicitName(): void
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback([
            'callback' => [FooCallbackValidatorClass::class, 'validateCallback'],
        ]);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Callback message', [
                '{{ value }}' => 'foobar',
            ])
        ;

        $this->validator->validate($object, $constraint);
    }

    public function testExpectValidConstraint(): void
    {
        $this->expectException(\Fxp\Component\DoctrineExtensions\Exception\UnexpectedTypeException::class);

        $object = new FooCallbackValidatorObject();
        /** @var Constraint $constraint */
        $constraint = $this->getMockForAbstractClass(Constraint::class);

        $this->validator->validate($object, $constraint);
    }

    public function testExpectValidMethods(): void
    {
        $this->expectException(\Fxp\Component\DoctrineExtensions\Exception\ConstraintDefinitionException::class);

        $object = new FooCallbackValidatorObject();

        $this->validator->validate($object, new DoctrineCallback('foobar'));
    }

    public function testExpectValidCallbacks(): void
    {
        $this->expectException(\Fxp\Component\DoctrineExtensions\Exception\ConstraintDefinitionException::class);

        $object = new FooCallbackValidatorObject();

        $this->validator->validate($object, new DoctrineCallback(['foo', 'bar']));
    }

    public function testConstraintGetTargets(): void
    {
        $constraint = new DoctrineCallback('foo');
        $targets = [Constraint::CLASS_CONSTRAINT, Constraint::PROPERTY_CONSTRAINT];

        $this->assertEquals($targets, $constraint->getTargets());
    }

    public function testNoConstructorArguments(): void
    {
        $this->assertInstanceOf(DoctrineCallback::class, new DoctrineCallback());
    }

    public function testAnnotationInvocationSingleValued(): void
    {
        $constraint = new DoctrineCallback(['value' => 'validateStatic']);

        $this->assertEquals(new DoctrineCallback('validateStatic'), $constraint);
    }

    public function testAnnotationInvocationMultiValued(): void
    {
        $constraint = new DoctrineCallback(['value' => [FooCallbackValidatorClass::class, 'validateCallback']]);

        $this->assertEquals(new DoctrineCallback([FooCallbackValidatorClass::class, 'validateCallback']), $constraint);
    }

    protected function createRegistryMock($entityManagerName, $em)
    {
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($entityManagerName))
            ->will($this->returnValue($em))
        ;

        return $registry;
    }
}
