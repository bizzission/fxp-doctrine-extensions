<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Tests\Filter;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\FilterCollection;
use Fxp\Component\DoctrineExtensions\Filter\AbstractFilter;
use Fxp\Component\DoctrineExtensions\Tests\Fixtures\BarFilter;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for abstract sql filter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractFilterTest extends TestCase
{
    public function getParameters()
    {
        return [
            [null, ''],
            [false, ''],
            [true, 'f.foo = "bar"'],
        ];
    }

    /**
     * @dataProvider getParameters
     *
     * @param null|bool $value    The value of foo_boolean parameter
     * @param string    $expected The expected result
     */
    public function testGetRealParameter($value, $expected): void
    {
        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        /** @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $meta->expects($this->any())
            ->method('getName')
            ->willReturn(\stdClass::class)
        ;

        $meta->expects($this->any())
            ->method('getColumnName')
            ->willReturnCallback(function ($v) {
                return $v;
            })
        ;

        $em->expects($this->any())
            ->method('getFilters')
            ->willReturn(new FilterCollection($em))
        ;

        $em->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection)
        ;

        $em->expects($this->any())
            ->method('getClassMetadata')
            ->willReturnCallback(function ($v) use ($meta) {
                return $v === $meta->getName()
                    ? $meta
                    : null;
            })
        ;

        $connection->expects($this->any())
            ->method('quote')
            ->willReturnCallback(function ($v) {
                return '"'.$v.'"';
            })
        ;

        $filter = new BarFilter($em);
        $this->assertInstanceOf(AbstractFilter::class, $filter);

        if (null !== $value) {
            $filter->setParameter('foo_boolean', $value, 'boolean');
        }

        $this->assertSame($expected, $filter->addFilterConstraint($meta, 'f'));
    }
}
