<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\Filter;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\FilterCollection;
use PHPUnit\Framework\TestCase;
use Sonatra\Component\DoctrineExtensions\Filter\AbstractFilter;
use Sonatra\Component\DoctrineExtensions\Tests\Fixtures\BarFilter;

/**
 * Tests case for abstract sql filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AbstractFilterTest extends TestCase
{
    public function getParameters()
    {
        return array(
            array(null, ''),
            array(false, ''),
            array(true, 'f.foo = "bar"'),
        );
    }

    /**
     * @dataProvider getParameters
     *
     * @param bool|null $value    The value of foo_boolean parameter
     * @param string    $expected The expected result
     */
    public function testGetRealParameter($value, $expected)
    {
        /* @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        /* @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $meta->expects($this->any())
            ->method('getName')
            ->willReturn(\stdClass::class);

        $meta->expects($this->any())
            ->method('getColumnName')
            ->willReturnCallback(function ($v) {
                return $v;
            });

        $em->expects($this->any())
            ->method('getFilters')
            ->willReturn(new FilterCollection($em));

        $em->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $em->expects($this->any())
            ->method('getClassMetadata')
            ->willReturnCallback(function ($v) use ($meta) {
                return $v === $meta->getName()
                    ? $meta
                    : null;
            });

        $connection->expects($this->any())
            ->method('quote')
            ->willReturnCallback(function ($v) {
                return '"'.$v.'"';
            });

        $filter = new BarFilter($em);
        $this->assertInstanceOf(AbstractFilter::class, $filter);

        if (null !== $value) {
            $filter->setParameter('foo_boolean', $value, 'boolean');
        }

        $this->assertSame($expected, $filter->addFilterConstraint($meta, 'f'));
    }
}
