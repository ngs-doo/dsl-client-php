<?php

use Cache\ProcessedTransaction;
use Cache\Transaction;
use NGS\Client\Exception\InvalidRequestException;

class RelationshipTest extends \BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Struct\LinkedList');
    }

    public function testPersistRelationship()
    {
        $trans = new Transaction();
        $trans->realmID = uniqid();
        $trans->persist();

        $processed = new ProcessedTransaction();
        $processed->transactionID = $trans->ID;
        $processed->persist();

        $found = ProcessedTransaction::find($processed->URI);
        $this->assertEquals($processed, $found);
    }

    /**
     * @expectedException NGS\Client\Exception\InvalidRequestException
     */
    public function testDeleteRelationship()
    {
        $trans = new Transaction();
        $trans->realmID = uniqid();
        $trans->persist();
        $processed = new ProcessedTransaction();
        $processed->transactionID = $trans->ID;
        $processed->persist();

        $trans->delete();
    }
}