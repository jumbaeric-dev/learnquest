<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown whenever a Nova AI provider fails to produce a reply
 * (network error, bad response, missing API key, etc). Caught by
 * NovaConversationService so a provider outage never breaks the
 * child-facing chat UI.
 */
class NovaAiException extends Exception
{
}
