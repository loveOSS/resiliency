<?php

namespace Resiliency;

/**
 * Define the available states of the Circuit Breaker;.
 */
final class States
{
    /**
     * Once opened, a circuit breaker doesn't do any call
     * to third-party services. Only the alternative call is done.
     */
    const OPEN_STATE = 'OPEN';

    /**
     * After some conditions are valid, the circuit breaker
     * try to access the third-party service. If the service is valid,
     * the circuit breaker go to CLOSED state. If it's not, the circuit breaker
     * go to OPEN state.
     */
    const HALF_OPEN_STATE = 'HALF OPEN';

    /**
     * On the first call of the service, or if the service is valid
     * the circuit breaker is in CLOSED state. This means that the callable
     * to evaluate is done and not the alternative call.
     */
    const CLOSED_STATE = 'CLOSED';
}
