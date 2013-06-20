<?php
use Scraping\Scrape;

class OptimisticConcurrencyTest extends \BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Scraping\Scrape');
    }

    public function testOptimisticConcurrency()
    {
        $scrape = new Scrape();
//        $scrape->persist();

//        $scrape->persist();

    }
}
