<?php

namespace Resiliency\Exceptions;

use Exception;
use Resiliency\Contracts\Exception as ResiliencyException;

final class UnavailableService extends Exception implements ResiliencyException
{
}
