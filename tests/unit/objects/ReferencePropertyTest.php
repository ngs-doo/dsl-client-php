<?php

use EntityRef\foo;
use EntityRef\bar;
use EntityRef\ent;

class ReferencePropertyTest extends BaseTestCase
{
    private $entities;

    public function testPersistEntityReferenceProperty()
    {
        $this->deleteAll('EntityRef\foo');

        $ent = new ent();
        $ent->key = 'abc';

        $foo = new foo();
        $foo->ref = $ent;
        $foo->persist();

        $bar = new bar();
        $bar->ref = $foo->ref;

        // persist fails as two roots cannot reference same entity
        try {
            $bar->persist();
        } catch (Exception $ex) {
            $this->assertInstanceOf('\NGS\Client\Exception\InvalidRequestException', $ex);
        }

        $this->deleteAll('EntityRef\foo');
    }

    public function testEntityCollectionReferenceProperty()
    {
        $blog = new \EntityRef\Blog();

        $c1 = new \EntityRef\Comment();
        $c1->email = 'john@a.com';
        $c1->title = '1';
        $c2 = new \EntityRef\Comment();
        $c2->email = 'smith@a.com';

        $blog->comments = array($c1, $c2);
        $blog->persist();

        $blog->comments = array();
        $blog->persist();

        $blog->comments = array($c1, $c2);
        $blog->persist();

        $blog->delete();

        $this->assertFalse(\EntityRef\Blog::exists($blog->URI));
    }


    public function testRootReferenceWithExplicitKey()
    {
        $this->deleteAll('Rt\ref');
        $this->deleteAll('Rt\rt');

        // remove all references
        array_map(function($rt) {
            $rt->ref = null;
            $rt->persist();
        }, \Rt\rt::findAll());
        $this->deleteAll('Rt\ref');
        $this->deleteAll('Rt\rt');

        $this->assertEmpty(\Rt\ref::findAll());
        $this->assertEmpty(\Rt\rt::findAll());

        $root = new \Rt\rt();
        $root->persist();

        $ref = new \Rt\ref();
        $ref->rt = $root;
        $ref->persist();

        $root->ref = $ref;
        $root->persist();

        try {
            $root->delete();
        } catch (\Exception $ex) {
            $this->assertInstanceOf('\NGS\Client\Exception\RequestException', $ex);
        }
        try {
            $ref->delete();
        } catch (\Exception $ex) {
            $this->assertInstanceOf('\NGS\Client\Exception\RequestException', $ex);
        }

        $root->ref = null;
        $root->persist();
        $ref->delete();
        $root->delete();

        $this->assertFalse(\Rt\rt::exists($root->URI));
        $this->assertFalse(\Rt\ref::exists($ref->URI));
    }

    public function testReferenceToSameType()
    {
        $r1 = new \Rt\selfRef(array('a'=>'1'));
        $r2 = new \Rt\selfRef(array('a'=>'2'));
        $r3 = new \Rt\selfRef(array('a'=>'3'));
        $r4 = new \Rt\selfRef(array('a'=>'4'));

        $r4->persist();
        $r3->refer = $r4;
        $r3->persist();
        $r2->refer = $r3;
        $r2->persist();
        $r1->refer = $r2;
        $r1->persist();

        $this->assertSame('4', $r1->nested);
        $this->assertSame('4', $r1->fromNested);
        $r2 = \rt\selfRef::find($r2->URI);
        $this->assertSame('1', $r2->parent[0]->a);

        $r4->refer = $r1;
        $r4->persist();

        $r2->refer = $r1;
        $r2->persist();


        $this->assertSame('3', $r4->nested);
    }
}
