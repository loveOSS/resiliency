<?php

namespace Resiliency\Monitors;

use Resiliency\Contracts\CircuitBreaker;
use Resiliency\Contracts\ReportEntry;
use Resiliency\Contracts\Service;

final class SimpleReportEntry implements ReportEntry
{
    /**
     * @var Service the Service called
     */
    private $service;

    /**
     * @var CircuitBreaker the Circuit Breaker called
     */
    private $circuitBreaker;

    /**
     * @var string the Circuit Breaker transition
     */
    private $transition;

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
