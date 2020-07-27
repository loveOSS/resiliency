<?php

namespace Resiliency\Monitors;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\Monitoring\ReportEntry;
use Resiliency\Contracts\Service;

final class SimpleReportEntry implements ReportEntry
{
    private Service $service;
    private CircuitBreaker $circuitBreaker;

    /**
     * @var string the Circuit Breaker transition
     */
    private string $transition;

    public function __construct(Service $service, CircuitBreaker $circuitBreaker, string $transition)
    {
        $this->service = $service;
        $this->circuitBreaker = $circuitBreaker;
        $this->transition = $transition;
    }

    /**
     * {@inheritdoc}
     */
    public function getService(): Service
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getCircuitBreaker(): CircuitBreaker
    {
        return $this->circuitBreaker;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransition(): string
    {
        return $this->transition;
    }
}
