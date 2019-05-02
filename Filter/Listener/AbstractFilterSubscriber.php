<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\DoctrineExtensions\Filter\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Base of Symfony listener for Doctrine Filter with parameter injection.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class AbstractFilterSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var bool
     */
    protected $injected = false;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onEvent', 7],
            ],
        ];
    }

    /**
     * Action on the event.
     *
     * @param Event $event The event
     */
    public function onEvent(Event $event): void
    {
        if (!$event instanceof GetResponseEvent || !$this->injected) {
            if (null !== ($filter = $this->getFilter())) {
                $this->injectParameters($filter);
            }
        }
    }

    /**
     * Get the supported filter.
     *
     * @return null|SQLFilter
     */
    protected function getFilter()
    {
        $supports = $this->supports();
        $filters = $this->entityManager->getFilters()->getEnabledFilters();
        $fFilter = null;

        foreach ($filters as $name => $filter) {
            if ($filter instanceof $supports) {
                $fFilter = $filter;

                break;
            }
        }

        return $fFilter;
    }

    /**
     * Get the supported class.
     *
     * @return string
     */
    abstract protected function supports();

    /**
     * Inject the parameters in doctrine sql filter.
     *
     * @param SQLFilter $filter The doctrine sql filter
     */
    abstract protected function injectParameters(SQLFilter $filter);
}
