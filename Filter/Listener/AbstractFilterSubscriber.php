<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Filter\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Base of Symfony listener for Doctrine Filter with parameter injection.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
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
        return array(
            KernelEvents::REQUEST => array(
                array('onEvent', 7),
            ),
        );
    }

    /**
     * Action on the event.
     *
     * @param Event $event The event
     */
    public function onEvent(Event $event)
    {
        if (!$event instanceof GetResponseEvent || !$this->injected) {
            $this->findSqlFilter();
        }
    }

    /**
     * Find the supported doctrine sql filter and injects the parameters.
     */
    protected function findSqlFilter()
    {
        $this->injected = true;
        $supports = $this->supports();
        $filters = $this->entityManager->getFilters()->getEnabledFilters();

        foreach ($filters as $name => $filter) {
            if ($filter instanceof $supports) {
                $this->injectParameters($filter, $name);
                break;
            }
        }
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
     * @param string    $name   The name of sql filter
     */
    abstract protected function injectParameters(SQLFilter $filter, $name);
}
