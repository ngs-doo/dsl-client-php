<?php

class RootPropertyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetterMethodWithRootNullURI()
    {
        $comment = new \Blog\Comment();
        $user = new \Blog\User();

        $comment->user = $user;
    }
}
