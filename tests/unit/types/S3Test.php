<?php
use NGS\S3 as S3;
use NGS\Client\S3Proxy;
use Test\FileS3;

class S3Test extends BaseTestCase
{
    public function setUp()
    {
        if (!class_exists('S3')) {
            $this->markTestSkipped('Amazon S3 class is not available.');
        }

        $defaultBucket = 'NGS.Test.Bucket';

        if (!isset($_ENV['S3_key']) || !isset($_ENV['S3_secret'])
            || !$_ENV['S3_key'] || !$_ENV['S3_secret'])
            $this->markTestSkipped('No S3 credentials in phpunit.xml');
        $config = array(
            'key'    => $_ENV['S3_key'],
            'secret' => $_ENV['S3_secret']
        );
        S3::setDefaultBucket($defaultBucket);
        S3::setClient(new AmazonS3($config));
    }

    public function testToArray()
    {
        $arr = array(
            'Bucket'   => 'bucket',
            'Key'      => 'key',
            'Length'   => 1,
            'Name'     => 'name',
            'MimeType' => 'bla',
            'Metadata' => array('meta'=>'meta')
        );
        $item = new S3($arr);
        $this->assertSame($arr, $item->toArray());

        return $item;
    }

    /**
     * @depends testToArray
     */
    public function testPersistence(S3 $item)
    {
        $file = new FileS3();
        $file->Name = 'file';
        $file->Content = $item;
        $file->persist();
        $newFile = FileS3::find($file->URI);

        $this->assertEquals($item, $newFile->Content);
        $this->assertSame($item->toArray(), $newFile->Content->toArray());

        $file->delete();
    }

    public function testUploadFromFile()
    {
        $this->markTestIncomplete('todo...');

        $file = $this->getFile('test.txt');
        $fileContent = file_get_contents($file);

        $item = new S3($file);
        $item->setMetadata(array('some'=>'meta'));
        $s3Content = S3::load($item->bucket, $item->key);

        $this->assertSame($fileContent, $s3Content);
        $this->assertTrue($item->delete());
    }

    public function testUploadFromStreamResource()
    {
        $this->markTestIncomplete('todo...');

        $file = $this->getFile('test.txt');
        $fp = fopen($file, 'r');
        $fileContent = file_get_contents($file);

        $item = new S3($fp);
        $s3Content = S3::load($item->bucket, $item->key);

        $this->assertSame($fileContent, $s3Content);
        $this->assertTrue($item->delete());
    }

    //public function
}
