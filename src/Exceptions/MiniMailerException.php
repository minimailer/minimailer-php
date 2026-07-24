<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

use Exception;

/**
 * Base type for every exception the SDK throws. Catch this to handle any
 * MiniMailer failure regardless of cause.
 */
class MiniMailerException extends Exception {}
