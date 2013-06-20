<?php

use Ref\Par;
use Ref\Simple;
use Ref\Child;
use Ref\Ent;
use Ref\Ent2;
use Ref\Ent3;
use Ref\Ent4;
use Ref\Ent5;

class ValidateReferenceOnPersistTest extends BaseTestCase
{
    public function setUp()
    {
        $this->deleteAll('Ref\Par');
        $this->deleteAll('Ref\Child');
        $this->deleteAll('Ref\Simple');
    }

    /**
     * @expectedException LogicException
     */
    public function testInvalidPersist()
    {
        $root = new Par();
        $root->child = new Child();
    }

    public function testValidPersist()
    {
        $simple = new Simple();
        $simple->persist();
        $child = new Child();
        $child->ent4 = array(
            new Ent4(array(
                'simple' => $simple
            ))
        );
        $child->persist();

        $par = new Par(array(
            'entArr' => array(
                new Ent(array(
                    'simple' => $simple,
                ))
            ),
            'child' => $child,
            'ent' => new Ent2(array(
                'simple' => $simple
            )),
            'simple' => $simple,
            'ent3Arr' => array(
                new Ent3(array(
                    'simple' => $simple
                ))
            )
        ));
        $par->persist();
    }
}
