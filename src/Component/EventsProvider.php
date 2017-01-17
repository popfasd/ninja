<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Component/EventsProvider.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Component;

use MattFerris\Provider\ProviderInterface;
use MattFerris\Di\ContainerInterface;
use Popfasd\Ninja\DomainEvents;
use Popfasd\Ninja\AdminNotifyListener;
use Popfasd\Ninja\FileListener;
use Popfasd\Ninja\SubmissionReceiptListener;

class EventsProvider implements ProviderInterface
{
    /**
     * @var \MattFerris\Di\ContainerInterface Container
     */
    protected $container;

    /**
     * @param \MattFerris\Di\ContainerInterface $container Container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function provides($consumer)
    {
        DomainEvents::setDispatcher($consumer);

        $config = $this->container->get('Config');

        $consumer->addListener(
            'Popfasd.Ninja.SubmissionProcessedEvent',
            [new AdminNotifyListener($config->get('app.mailto')), 'onSubmissionProcessed']
        );

        $consumer->addListener(
            'Popfasd.Ninja.SubmissionProcessedEvent',
            [new FileListener($config->get('app.cacheDir')), 'onSubmissionProcessed']
        );

        $consumer->addListener(
            'Popfasd.Ninja.SubmissionProcessedEvent',
            [new SubmissionReceiptListener($config->get('app.cacheDir')), 'onSubmissionProcessed']
        );
    }
}
