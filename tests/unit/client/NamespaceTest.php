<?php

use NGS\Client\HttpClient;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NamespaceTest extends BaseTestCase
{
    public function testClassNames()
    {
        $client = new HttpClient('http://localhost');
        
        $this->assertSame('Test\Root', $client->getClassName('Test.Root'));
        $this->assertSame('a\b\c\d',   $client->getClassName('a.b.c.d'));
        
        $this->assertSame('Test.Root', $client->getDslName('Test\Root'));
        $this->assertSame('Test.Root', $client->getDslName('\Test\Root'));
        $this->assertSame('a.b.c.d', $client->getDslName('a\b\c\d'));
        $this->assertSame('a.b.c.d', $client->getDslName('\a\b\c\d'));
    }

    public function testClassNamesWithPrefix()
    {
        $client = new HttpClient('http://localhost');
        
        $client->setNamespacePrefix('prefix');
        
        $this->assertSame('prefix\Test\Root', $client->getClassName('Test.Root'));
        $this->assertSame('prefix\a\b\c\d',   $client->getClassName('a.b.c.d'));
        
        $client2 = new HttpClient('http://localhost');
        $client2->setNamespacePrefix('a\bit\longer\prefix');
        
        $this->assertSame('a\bit\longer\prefix\Test\Root', $client2->getClassName('Test.Root'));
        $this->assertSame('a\bit\longer\prefix\a\b\c\d',   $client2->getClassName('a.b.c.d'));
    }    
}
