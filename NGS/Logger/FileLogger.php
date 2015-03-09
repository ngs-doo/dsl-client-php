<?php
namespace NGS\Logger;

class FileLogger extends AbstractLoggerHelper
{
    protected $filename;
    protected $level;

    public function __construct($filename, $level)
    {
        $this->filename = $filename;
        $this->level = $level;
    }

    public function log($level, $message, array $context = array())
    {
        if (self::compareLogLevels($this->level, $level) < 0) {
            return;
        }

        $message = self::interpolate($message, $context);
        $count = 0;
        $message = preg_replace('/(\r\n|\r|\n)/u', PHP_EOL."\t", $message, -1, $count);

        if ($count > 0) {
            $message .= PHP_EOL.'------------------------------------------------------';
        }

        $message = '['.strftime('%c').'] '.$message.PHP_EOL;
        $fp = fopen($this->filename, 'a');
        fwrite($fp, $message);
        fclose($fp);
    }
}
