<?php
namespace NGS\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

abstract class AbstractLoggerHelper extends AbstractLogger
{
    protected static function interpolate($message, array $context = array())
    {
        foreach ($context as $key => $value) {
            $message = preg_replace('/\\{'.$key.'\\}/mu', $value, $message);
        }

        return $message;
    }

    protected static function getLevelPriority($level)
    {
        switch ($level) {
            case LogLevel::DEBUG:
                return 0;
            case LogLevel::INFO:
                return 1;
            case LogLevel::NOTICE:
                return 2;
            case LogLevel::WARNING:
                return 3;
            case LogLevel::ERROR:
                return 4;
            case LogLevel::CRITICAL:
                return 5;
            case LogLevel::ALERT:
                return 6;
            case LogLevel::EMERGENCY:
                return 7;
            default:
                return 8;
        }
    }

    protected static function compareLogLevels($loggerLevel, $messageLevel)
    {
        return self::getLevelPriority($messageLevel) - self::getLevelPriority($loggerLevel);
    }
}
