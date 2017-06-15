<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\Validator\Constraints;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonatra\Component\DoctrineExtensions\Tests\Fixtures\FooCallbackValidatorClass;
use Sonatra\Component\DoctrineExtensions\Tests\Fixtures\FooCallbackValidatorObject;
use Sonatra\Component\DoctrineExtensions\Validator\Constraints\DoctrineCallback;
use Sonatra\Component\DoctrineExtensions\Validator\Constraints\DoctrineCallbackValidator;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Tests case for doctrine callback validator.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineCallbackValidatorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var DoctrineCallbackValidator
     */
    protected $validator;

    protected function setUp()
    {
        $entityManagerName = 'foo';
        $em = DoctrineTestHelper::createTestEntityManager();
        /* @var ManagerRegistry $registry */
        $registry = $this->createRegistryMock($entityManagerName, $em);
        $context = $this->getMockBuilder(ExecutionContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $context;
        $this->validator = new DoctrineCallbackValidator($registry);
        /* @var ExecutionContextInterface $context */
        $this->validator->initialize($context);
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    protected function createRegistryMock($entityManagerName, $em)
    {
        $registry = $this->getMockBuilder(ManagerRegistry::class)->getMock();
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($entityManagerName))
            ->will($this->returnValue($em));

        return $registry;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new DoctrineCallback('foo'));
    }

    public function testSingleMethod()
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback('validate');

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate($object, $constraint);
    }

    public function testSingleMethodExplicitName()
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback(array('callback' => 'validate'));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate($object, $constraint);
    }

    public function testSingleStaticMethod()
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback('validateStatic');

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Static message', array(
                    '{{ value }}' => 'baz',
                ));

        $this->validator->validate($object, $constraint);
    }

    public function testClosure()
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback(function ($object, ExecutionContextInterface $context) {
            $context->addViolation('My message', array('{{ value }}' => 'foobar'));

            return false;
        });

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate($object, $constraint);
    }

    public function testClosureNullObject()
    {
        $constraint = new DoctrineCallback(function ($object, ExecutionContextInterface $context) {
            $context->addViolation('My message', array('{{ value }}' => 'foobar'));

            return false;
        });

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate(null, $constraint);
    }

    public function testClosureExplicitName()
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback(array(
            'callback' => function ($object, ExecutionContextInterface $context) {
                $context->addViolation('My message', array('{{ value }}' => 'foobar'));

                return false;
            },
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('My message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate($object, $constraint);
    }

    public function testArrayCallable()
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback(array(FooCallbackValidatorClass::class, 'validateCallback'));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Callback message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate($object, $constraint);
    }

    public function testArrayCallableNullObject()
    {
        $constraint = new DoctrineCallback(array(FooCallbackValidatorClass::class, 'validateCallback'));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Callback message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate(null, $constraint);
    }

    public function testArrayCallableExplicitName()
    {
        $object = new FooCallbackValidatorObject();
        $constraint = new DoctrineCallback(array(
            'callback' => array(FooCallbackValidatorClass::class, 'validateCallback'),
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('Callback message', array(
                    '{{ value }}' => 'foobar',
                ));

        $this->validator->validate($object, $constraint);
    }

    /**
     * @expectedException \Sonatra\Component\DoctrineExtensions\Exception\UnexpectedTypeException
     */
    public function testExpectValidConstraint()
    {
        $object = new FooCallbackValidatorObject();
        /* @var Constraint $constraint */
        $constraint = $this->getMockForAbstractClass(Constraint::class);

        $this->validator->validate($object, $constraint);
    }

    /**
     * @expectedException \Sonatra\Component\DoctrineExtensions\Exception\ConstraintDefinitionException
     */
    public function testExpectValidMethods()
    {
        $object = new FooCallbackValidatorObject();

        $this->validator->validate($object, new DoctrineCallback('foobar'));
    }

    /**
     * @expectedException \Sonatra\Component\DoctrineExtensions\Exception\ConstraintDefinitionException
     */
    public function testExpectValidCallbacks()
    {
        $object = new FooCallbackValidatorObject();

        $this->validator->validate($object, new DoctrineCallback(array('foo', 'bar')));
    }

    public function testConstraintGetTargets()
    {
        $constraint = new DoctrineCallback('foo');
        $targets = array(Constraint::CLASS_CONSTRAINT, Constraint::PROPERTY_CONSTRAINT);

        $this->assertEquals($targets, $constraint->getTargets());
    }

    public function testNoConstructorArguments()
    {
        $this->assertInstanceOf(DoctrineCallback::class, new DoctrineCallback());
    }

    public function testAnnotationInvocationSingleValued()
    {
        $constraint = new DoctrineCallback(array('value' => 'validateStatic'));

        $this->assertEquals(new DoctrineCallback('validateStatic'), $constraint);
    }

    public function testAnnotationInvocationMultiValued()
    {
        $constraint = new DoctrineCallback(array('value' => array(FooCallbackValidatorClass::class, 'validateCallback')));

        $this->assertEquals(new DoctrineCallback(array(FooCallbackValidatorClass::class, 'validateCallback')), $constraint);
    }
}
