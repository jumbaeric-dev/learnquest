<?php

namespace App\Exceptions;

use RuntimeException;

/** Thrown when a parent's plan does not allow adding more of something. */
class PlanLimitReachedException extends RuntimeException
{
}