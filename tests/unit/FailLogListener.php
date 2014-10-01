<?php
use NGS\Client\HttpClient;

class FailLogListener implements PHPUnit_Framework_TestListener
{
    private $httpBuffer = '';

    public function __construct ()
    {
        HttpClient::instance()->addSubscriber(array($this, 'logHttpEvent'));
    }

    public function logHttpEvent($event, $data)
    {
        if ($event === 'request.sent') {
            $output =
                "\n".$data['request']."\n".
                'RESPONSE:'."\n".
                $data['response']['body']."\n";
            $this->httpBuffer .= $output;
        }
    }

    private function logFail($test)
    {
        if ($this->httpBuffer)
            printf("\nFAILED TEST: '%s'. HTTP log:\n %s\n", $test->getName(), $this->httpBuffer);
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->logFail($test);
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->logFail($test);
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->httpBuffer = '';
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}

    public function endTest(PHPUnit_Framework_Test $test, $time) {}

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {}

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {}
}
