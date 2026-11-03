<?php

namespace App\Exceptions;

use RuntimeException;

/** Thrown when a child opens unfinished content their parent's plan excludes. */
class ContentLockedException extends RuntimeException
{
}