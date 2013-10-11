<?php
use NGS\Converter\XmlConverter;

/**
 * Test constructors
 */
class XmlTest extends PHPUnit_Framework_TestCase
{
    public static function providerInvalid()
    {
        return array(
            array(
                '<some invalid xml<<<'
            )
        );
    }

    public static function providerValid()
    {
        $xmls = array(
            array(
                '<ChildrenWithSameName>'.
                    '<Param key="name">Mirko</Param>'.
                    '<Param key="phone">123</Param>'.
                '</ChildrenWithSameName>'
            ),
            array(
                '<ChildrenWithSameName>'.
                    '<Param>'.
                        '<Param>1</Param>'.
                        '<Param>1</Param>'.
                        '<Param>2</Param>'.
                    '</Param>'.
                    '<Param key="phone">123</Param>'.
                '</ChildrenWithSameName>'
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

        $this->assertSame($expected, $xmlFromArr->asXml());
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

    public function testParseSampleDocument()
    {
        $xmlString = '<?xml version="1.0"?>
<PurchaseOrder PurchaseOrderNumber="99503" OrderDate="1999-10-20">
  <Address Type="Shipping">
    <Name>Ellen Adams</Name>
    <Street>123 Maple Street</Street>
    <City>Mill Valley</City>
    <State>CA</State>
    <Zip>10999</Zip>
    <Country>USA</Country>
  </Address>
  <Address Type="Billing">
    <Name>Tai Yee</Name>
    <Street>8 Oak Avenue</Street>
    <City>Old Town</City>
    <State>PA</State>
    <Zip>95819</Zip>
    <Country>USA</Country>
  </Address>
  <DeliveryNotes>Please leave packages in shed by driveway.</DeliveryNotes>
  <Items>
    <Item PartNumber="872-AA">
      <ProductName>Lawnmower</ProductName>
      <Quantity>1</Quantity>
      <USPrice>148.95</USPrice>
      <Comment>Confirm this is electric</Comment>
    </Item>
    <Item PartNumber="926-AA">
      <ProductName>Baby Monitor</ProductName>
      <Quantity>2</Quantity>
      <USPrice>39.98</USPrice>
      <ShipDate>1999-05-21</ShipDate>
    </Item>
  </Items>
</PurchaseOrder>
';

        $xml = XmlConverter::toXml($xmlString);
        $this->assertSame($xmlString, $xml->asXML());

        $xmlArray = XmlConverter::toArray($xml);
        $xmlFromArray = XmlConverter::toXml($xmlArray);

        // xml from array does not preserve whitespace
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml->asXML());
        $xmlNoWhitespace = $dom->saveXML();

        $this->assertSame($xmlNoWhitespace, $xmlFromArray->asXML());
    }
    public function testExampleFiles()
    {
        $xmlFile = $this->getFile('ms-books.xml');
        
        $xml = file_get_contents($xmlFile);
        
        $item = new Test\Elem();
        $item->data = $xml;
        
        $xmlObj = $item->data;
        $this->assertSame($xml, $xmlObj->asXML());
        
        $item->persist();
        
        $fetched = Test\Elem::find($item->URI);
        $xmlDb = $fetched->data;
        
        $this->assertEquals($xmlObj->catalog->asXML(), $xmlDb->catalog->asXML());
        
        // $this->assertSame($xmlObj->asXML(), $xmlDb->asXML());
        
        $item->delete();
    }
}
