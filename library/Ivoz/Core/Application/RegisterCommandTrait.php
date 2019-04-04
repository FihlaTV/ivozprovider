<?php

namespace Ivoz\Core\Application;

use Ivoz\Core\Domain\Service\DomainEventPublisher;
use Ivoz\Core\Application\RequestId;
use Ivoz\Core\Application\Event\CommandWasExecuted;

trait RegisterCommandTrait
{
    /**
     * @var DomainEventPublisher
     */
    private $eventPublisher;

    /**
     * @var RequestId
     */
    private $requestId;

    private function registerCommand(string $service, string $method, $aguments = [], $agent = [])
    {
        $event = new CommandWasExecuted(
            $this->requestId->toString(),
            $service,
            $method,
            $aguments,
            $agent
        );

        $this->eventPublisher->publish($event);
    }
}
