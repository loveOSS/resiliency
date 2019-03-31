<?php

namespace Resiliency\Exceptions;

use Exception;
use Resiliency\Contracts\Exception as ResiliencyException;

final class TransactionNotFound extends Exception implements ResiliencyException
{
}
