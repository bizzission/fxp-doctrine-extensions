<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\Util;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Sonatra\Component\DoctrineExtensions\Tests\Fixtures\BarFilter;
use Sonatra\Component\DoctrineExtensions\Util\SqlFilterUtil;

/**
 * Tests case for abstract sql filter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SqlFilterUtilTest extends \PHPUnit_Framework_TestCase
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
        $filter = $this->getMockForAbstractClass(SQLFilter::class, array($this->em));
        $barFilter = new BarFilter($this->em);
        $barFilter->disable();
        $expected = array(
            'foo' => $filter,
        );

        $this->filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn(array_merge($expected, array(
                'bar' => $barFilter,
            )));

        $this->assertEquals($expected, SqlFilterUtil::getEnabledFilters($this->em));
    }
}
