<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Tests\Filter\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Query\FilterCollection;
use Fxp\Component\DoctrineExtensions\Filter\Listener\AbstractFilterSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Tests case for abstract sql filter event subscriber.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class AbstractFilterSubscriberTest extends TestCase
{
    /**
     * @throws
     */
    public function testInjectParameters(): void
    {
        /** @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|RequestEvent $event */
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|SQLFilter $filter */
        $filter = $this->getMockBuilder(SQLFilter::class)->disableOriginalConstructor()->getMock();
        /** @var FilterCollection|\PHPUnit_Framework_MockObject_MockObject $filterCollection */
        $filterCollection = $this->getMockBuilder(FilterCollection::class)->disableOriginalConstructor()->getMock();

        $em->expects($this->once())
            ->method('getFilters')
            ->willReturn($filterCollection)
        ;

        $filterCollection->expects($this->once())
            ->method('getEnabledFilters')
            ->willReturn([
                'foo' => $filter,
            ])
        ;

        /** @var AbstractFilterSubscriber|\PHPUnit_Framework_MockObject_MockObject $listener */
        $listener = $this->getMockForAbstractClass(AbstractFilterSubscriber::class, [$em]);

        $listener->expects($this->once())
            ->method('supports')
            ->willReturn(SQLFilter::class)
        ;

        $listener->expects($this->once())
            ->method('injectParameters')
            ->with($filter)
        ;

        $this->assertEquals([
            KernelEvents::REQUEST => [
                ['onEvent', 7],
            ],
        ], $listener::getSubscribedEvents());

        $listener->onEvent($event);
    }
}
