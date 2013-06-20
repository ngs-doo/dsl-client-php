<?php
use NGS\Client\Exception\InvalidRequestException;

class ExceptionsTest extends \BaseTestCase
{
    public function testRequestExceptions()
    {
        $message = 'not found';
        $code = 404;
        $headers = array('Content-type' => 'text/html');

        $ex = new InvalidRequestException($message, $code);
    }
}
