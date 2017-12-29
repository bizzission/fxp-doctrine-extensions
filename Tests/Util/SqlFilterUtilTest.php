<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Tests\Util;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Fxp\Component\DoctrineExtensions\Tests\Fixtures\BarFilter;
use Fxp\Component\DoctrineExtensions\Util\SqlFilterUtil;
use PHPUnit\Framework\TestCase;

/**
 * Tests case for abstract sql filter.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SqlFilterUtilTest extends TestCase
{
    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var FilterCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterCollection;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->filterCollection = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $this->em->expects($this->once())
            ->method('getFilters')
            ->willReturn($this->filterCollection);
    }

    public function testGetEnabledFilters()
    {
        /* @var SQLFilter|\PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockForAbstractClass(SQLFilter::class, [$this->em]);
        $barFilter = new BarFilter($this->em);
        $barFilter->disable();
        $expected = [
            'foo' => $filter,
        ];

        $this->filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn(array_merge($expected, [
                'bar' => $barFilter,
            ]));

        $this->assertEquals($expected, SqlFilterUtil::getEnabledFilters($this->em));
    }

    public function testIsEnabledWithDisabledSqlFilter()
    {
        $this->filterCollection->expects($this->once())
            ->method('isEnabled')
            ->with('foo')
            ->willReturn(false);

        $this->assertFalse(SqlFilterUtil::isEnabled($this->em, 'foo'));
    }

    public function testIsEnabledWithDisabledEnableSqlFilter()
    {
        $barFilter = new BarFilter($this->em);
        $barFilter->disable();

        $this->filterCollection->expects($this->once())
            ->method('isEnabled')
            ->with('bar')
            ->willReturn(true);

        $this->filterCollection->expects($this->once())
            ->method('getFilter')
            ->willReturn($barFilter);

        $this->assertFalse(SqlFilterUtil::isEnabled($this->em, 'bar'));
    }

    public function testIsEnabledWithEnabledEnableSqlFilter()
    {
        $barFilter = new BarFilter($this->em);

        $this->filterCollection->expects($this->once())
            ->method('isEnabled')
            ->with('bar')
            ->willReturn(true);

        $this->filterCollection->expects($this->once())
            ->method('getFilter')
            ->willReturn($barFilter);

        $this->assertTrue(SqlFilterUtil::isEnabled($this->em, 'bar'));
    }
}
