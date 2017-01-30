<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\Filter\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Sonatra\Component\DoctrineExtensions\Filter\Listener\AbstractFilterSubscriber;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tests case for abstract sql filter event subscriber.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AbstractFilterSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testInjectParameters()
    {
        /* @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        /* @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        /* @var SQLFilter|\PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockBuilder(SQLFilter::class)->disableOriginalConstructor()->getMock();
        /* @var FilterCollection|\PHPUnit_Framework_MockObject_MockObject $filterCollection */
        $filterCollection = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $em->expects($this->once())
            ->method('getFilters')
            ->willReturn($filterCollection);

        $filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn(array(
                'foo' => $filter,
            ));

        /* @var AbstractFilterSubscriber|\PHPUnit_Framework_MockObject_MockObject $listener */
        $listener = $this->getMockForAbstractClass(AbstractFilterSubscriber::class, array($em));

        $listener->expects($this->once())
            ->method('supports')
            ->willReturn(SQLFilter::class);

        $listener->expects($this->once())
            ->method('injectParameters')
            ->with($filter);

        $this->assertEquals(array(
            KernelEvents::REQUEST => array(
                array('onEvent', 7),
            ),
        ), $listener->getSubscribedEvents());

        $listener->onEvent($event);
    }
}
