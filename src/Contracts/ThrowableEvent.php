<?php

namespace Resiliency\Contracts;

/**
 * Specific event in case of failure.
 */
interface ThrowableEvent extends Event
{
    /**
     * @return Exception the Exception
     */
    public function getException(): Exception;
}
