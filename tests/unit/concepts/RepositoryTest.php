<?php
use NGS\Patterns\Repository;
use NGS\Client\RestHttp;
use Test\Bar;

class RepositoryTest extends \BaseTestCase
{
    public function setUp()
    {
        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Memcached is not available.');
        }
    }

    public function testConstructWithCache()
    {
        $http = RestHttp::instance();
        $memcached = new Memcached();
        $memcached->addServer('localhost', 11211);

        $prefix = 'ngs.test.';
        $repo = new Repository($http, $memcached, $prefix);
        return $repo;
    }

    /**
     * @depends testConstructWithCache
     */
    public function testFindWithMemcached(Repository $repo)
    {
        $foo = new Test\Bar();
        $foo->persist();

        $foo2 = $repo->find('Test\Bar', $foo->URI);

        // check private Repository cache
        $ref = new ReflectionObject($repo);
        $refCache = $ref->getProperty('cache');
        $refCache->setAccessible(true);
        $privateCache = $refCache->getValue($repo);
        $this->assertArrayHasKey('Test\Bar', $privateCache);
        $this->assertContains($foo2, $privateCache['Test\Bar']);

        // $foo3 should be same object as $foo2 stored in private Repository cache
        $foo3 = $repo->find('Test\Bar', $foo->URI);
        $this->assertSame($foo2, $foo3);

        $this->assertEquals($foo, $foo2);
        $foo2 = $repo->find('Test\Bar', $foo->URI); // should use cached values

        $repo->invalidate('Test\Bar', $foo->URI);

        $foo2 = new Test\Bar();
        $foo2->persist();

        $repo->find('Test\Bar', array($foo->URI, $foo2->URI));
        $repo->find('Test\Bar', array($foo->URI, $foo2->URI)); // should use cached values
        $repo->invalidate('Test\Bar', array($foo->URI, $foo2->URI));

        $this->deleteAll('Test\Bar');
    }

    /**
     * @depends testConstructWithCache
     * @expectedException InvalidArgumentException
     */
    public function testInvalidateWithInvalidArgs(Repository $repo)
    {
        $foo = new Test\Bar();
        $repo->invalidate($foo, $foo->URI);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindWithInvalidArgs()
    {
        $repo = new Repository();
        $repo->find(-1, -1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testFindWithInvalidArgs2()
    {
        $repo = new Repository();
        $repo->find('Test\Foo', -1);
    }

    /**
     * @depends testConstructWithCache
     */
    public function testGetAndSetInstance(Repository $repo)
    {
        Repository::instance($repo);
        $this->assertSame($repo, Repository::instance());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructInvalid()
    {
        new Repository(
            RestHttp::instance(),
            new memcached(),
            $prefix = new stdClass()    // must be string
        );
    }

    public function testConstructDefault()
    {
        $repo = Repository::instance();
        $this->assertTrue($repo instanceof Repository);
    }

    /**
     * @depends testConstructWithCache
     * @expectedException NGS\Client\Exception\NotFoundException
     */
    public function testFindNonExistingWithCache(Repository $repoWithCache)
    {
        $repoWithCache->find('Test\Foo', 'non-existing-uri-bla123');
    }

}
