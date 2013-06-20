<?php
use Finance\Currency;

class StaticTest extends \BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Finance\Currency');
    }

    public function testStaticValues()
    {
        $items = Finance\Currency::findAll();

        $usd = new Currency(array('Code'=>'USD', 'Name'=>'dollar'));
        $usd->persist();
        $eur = new Currency(array('Code'=>'EUR', 'Name'=>'euro'));
        $eur->persist();

        $this->assertEquals($usd, Currency::USD());
        $this->assertEquals($eur, Currency::EUR());

        $static = Finance\Currency::getStaticValues();
        $this->assertEquals($usd, $static[0]);
        $this->assertEquals($eur, $static[1]);
    }
}
