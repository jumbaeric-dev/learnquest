<?php

namespace App\Exceptions\Billing;

/** The caller failed provider authentication (bad challenge). Reply 401. */
final class WebhookAuthenticationException extends PaymentGatewayException {}
