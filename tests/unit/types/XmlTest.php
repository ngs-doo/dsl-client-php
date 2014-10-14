<?php
use NGS\Converter\XmlConverter;

/**
 * Test constructors
 */
class XmlTest extends BaseTestCase
{
    public static function providerXmlFiles()
    {
        return array(
            array('books.xml'),
            array('orders.xml'),
            array('ampersands.xml'),
        );
    }

    public static function providerInvalid()
    {
        return array(
            array(
                '<some invalid xml<<<'
            )
        );
    }

    public function providerValid()
    {
        $xmls = array(
            array(
'<ChildrenWithSameName>
  <Param key="name">Mirko</Param>
  <Param key="phone">123</Param>
</ChildrenWithSameName>'
            ),
            array(
'<ChildrenWithSameName>
  <Param>
    <Param>1</Param>
    <Param>1</Param>
    <Param>2</Param>
  </Param>
  <Param key="phone">123</Param>
</ChildrenWithSameName>'
            ),
            array(
                "<singleRoot>text</singleRoot>",
            ),
            array(
                "<singleEmptyRoot></singleEmptyRoot>",
                "<singleEmptyRoot/>",
            ),
        );
        foreach($xmls as &$val) {
            $val[0] = "<?xml version=\"1.0\"?>\n".$val[0]."\n";
            if(!isset($val[1]))
                $val[1] = $val[0];
            else
                $val[1] =  "<?xml version=\"1.0\"?>\n".$val[1]."\n";
        }

        foreach (self::providerXmlFiles() as $data) {
            $content = file_get_contents($this->getFile($data[0]));
            $xmls[] = array($content, $content);
        }
        return $xmls;
    }

    // Fixes bug produced by convrertin json xml array:
    // SimpleXMLElement::addAttribute(): Attribute already exists
    public function testArrayConversionsChildrenWithSameName()
    {
        // $xml = XmlConverter::toXml($source);

        $jsonXmlArray = array(
            'User' => array(
                'Param' => array(
                    array(
                        '@key'  => 'name',
                        '#text' => 'Mirko'
                    ),
                    array(
                        '@key'  => 'phone',
                        '#text' => '123'
                    ),
                )
            )
        );

        $xmlElem = XmlConverter::toXml($jsonXmlArray);

        $expectedXml = "<?xml version=\"1.0\"?>\n<User><Param key=\"name\">Mirko</Param><Param key=\"phone\">123</Param></User>\n";

        $this->assertSame($expectedXml, $xmlElem->asXml());

        $this->assertSame($jsonXmlArray, XmlConverter::toArray($xmlElem));
    }

    /**
     * @dataProvider providerValid
     */
    public function testToFromArray($source, $expected)
    {
        $xml = XmlConverter::toXml($source);

        $this->assertSame($expected, $xml->asXml());

        $arr = XmlConverter::toArray($xml);

        $xmlFromArr = XmlConverter::toXml($arr);

        $this->assertSame($expected, $this->formatXml($xmlFromArr));
    }

    public function testFromArrayEscapesAmpersands()
    {
        $source = array(
            'test' => array(
                'unescaped' => 'One & two',
                'escaped' => 'One &amp; two'));

        $xml = XmlConverter::toXml($source);

        $expectedXml = '<?xml version="1.0"?>
<test><unescaped>One &amp; two</unescaped><escaped>One &amp;amp; two</escaped></test>
';
        $this->assertSame($expectedXml, $xml->asXML());

        $xmlFromString = XmlConverter::toXml($expectedXml);
        $this->assertSame($expectedXml, $xmlFromString->asXML());
    }

    /**
     * @dataProvider providerValid
     */
    public function testPersistAndLoadXml($xmlContent)
    {
        $item = new Test\Elem();
        $item->data = $xmlContent;
        $oldXml = $item->data;

        $item->persist();

        $fetched = Test\Elem::find($item->URI);

        $this->assertSame($this->formatXml($oldXml), $this->formatXml($fetched->data));

        $item->delete();
    }

    public function testSingleElement()
    {
        $str = "<singleRoot>text</singleRoot>";
        $arr = array('singleRoot' => 'text');
        $arr2 = array('singleRoot' => array('#text' => 'text'));

        $xml1 = XmlConverter::toXml($str);
        $xml2 = XmlConverter::toXml($arr);
        $xml3 = XmlConverter::toXml($arr2);

        $this->assertEquals($xml1, $xml2);
        $this->assertEquals($xml2, $xml3);
    }

    private function formatXml(SimpleXMLElement $xml)
    {
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        return $dom->saveXML();
    }
}
