<?php
use NGS\Client\StandardProxy;

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    private static $echoLogEnabled;

    protected function deleteAll($class)
    {
        $items = $class::findAll();
        if ($items) {
            $proxy = new StandardProxy();
            $proxy->delete($items);
        }
    }

    public function getFixturesPath()
    {
        return __DIR__.'/../fixtures';
    }

    public function getFile($file)
    {
        $path = $this->getFixturesPath().'/'.$file;
        if(!is_file($path)) {
            throw new InvalidArgumentException('TestCase file not found: "'.$path.'"');
        }
        return $path;
    }

    public function echoLog($event, $data)
    {
        if (self::$echoLogEnabled && $event === 'request.sent') {
            echo $data['request']."\n";
            echo 'RESPONSE:'."\n";
            echo $data['response']['body']."\n\n";
        }
    }

    // quick debug: echo all server communication
    public function echoRequests($enabled=true)
    {
        if (!isset(self::$echoLogEnabled))
            \NGS\Client\HttpClient::instance()->addSubscriber(array($this, 'echoLog'));

        self::$echoLogEnabled = $enabled;
    }
}
