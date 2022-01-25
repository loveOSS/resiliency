<?php

namespace Resiliency;

/**
 * Define the available transitions of the Circuit Breaker;.
 */
final class Transitions
{
    /**
     * Happened only once when calling the Circuit Breaker.
     */
    public const INITIATING_TRANSITION = 'INITIATING';

    /**
     * Happened when we open the Circuit Breaker.
     * This means once the Circuit Breaker is in failure.
     */
    public const OPENING_TRANSITION = 'OPENING';

    /**
     * Happened once the conditions of retry are met
     * in OPEN state to move to HALF_OPEN state in the
     * Circuit Breaker.
     */
    public const CHECKING_AVAILABILITY_TRANSITION = 'CHECKING AVAILABILITY';

    /**
     * Happened when we come back to OPEN state
     * in the Circuit Breaker from the HALF_OPEN state.
     */
    public const REOPENING_TRANSITION = 'REOPENING';

    /**
     * Happened if the service is available again.
     */
    public const CLOSING_TRANSITION = 'CLOSING';

    /**
     * Happened on each try to call the service.
     */
    public const TRIAL_TRANSITION = 'TRIAL';

    /**
     * Happened when the Circuit Breaker is isolated.
     */
    public const ISOLATING_TRANSITION = 'ISOLATING';

    /**
     *  Happened when the Circuit Breaker is reset.
     */
    public const RESETTING_TRANSITION = 'RESETTING';
}
