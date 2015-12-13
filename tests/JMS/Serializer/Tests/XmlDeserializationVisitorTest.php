<?php

namespace JMS\Serializer\Tests;

use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\XmlDeserializationVisitor;

class XmlDeserializationVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function invoke(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * This test fails on PHP 5.5.9-5.5.11
     * See https://bugs.php.net/bug.php?id=67081
     */
    public function testEntitySubset()
    {
        $previous = libxml_use_internal_errors(true);
        $previousEntityLoaderState = libxml_disable_entity_loader(true);

        $data = '<?xml version="1.0"?>
            <!DOCTYPE author [
                <!ENTITY foo SYSTEM "php://filter/read=convert.base64-encode/resource=' . basename(__FILE__) . '">
            ]>
            <result>
                &foo;
            </result>';

        $dom = new \DOMDocument();
        $dom->loadXML($data);

        $visitor = new XmlDeserializationVisitor(new CamelCaseNamingStrategy());

        $params = array($dom->childNodes->item(0), $data);
        $expected = '<!ENTITY foo SYSTEM "php://filter/read=convert.base64-encode/resource=XmlDeserializationVisitorTest.php">';

        $this->assertEquals($expected, $this->invoke($visitor, 'getDomDocumentTypeEntitySubset', $params));

        libxml_use_internal_errors($previous);
        libxml_disable_entity_loader($previousEntityLoaderState);
    }
}
