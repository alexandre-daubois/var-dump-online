<?php

namespace App\Tests\Exporter;

use App\Entity\Formatter\Node;
use App\Service\UserVarDumpModelFormatter;
use PHPUnit\Framework\TestCase;
use function Symfony\Component\String\u;

class UserVarDumpModelFormatterTest extends TestCase
{
    public function testCreatePrimitiveNodeShouldReturnValidAndFullfilledNode()
    {
        $formatter = new UserVarDumpModelFormatter();
        $node = $formatter->createPrimitiveNode(Node::TYPE_INT, u('int(1)'));

        $this->assertEquals(Node::TYPE_INT, $node->getType());
        $this->assertEquals('1', $node->getValue());
        $this->assertEmpty($node->getChildren());

        $node = $formatter->createPrimitiveNode(Node::TYPE_FLOAT, u('float(5.3)'));
        $this->assertEquals(Node::TYPE_FLOAT, $node->getType());
        $this->assertEquals('5.3', $node->getValue());
        $this->assertEmpty($node->getChildren());
    }

    public function testCreatePrimitiveNodeWihtoutPrimitiveTypeShouldRaiseAnException()
    {
        $formatter = new UserVarDumpModelFormatter();

        $this->expectExceptionMessage('Format is not a primitive');
        $formatter->createPrimitiveNode(Node::TYPE_OBJECT, u('array(1) { }'));
    }

    public function testStringValueExtractorShouldReturnTheStringContent()
    {
        $formatter = new UserVarDumpModelFormatter();
        $this->assertEquals('Hi "everybody"!', $formatter->extractStringValue(u('string(17) "Hi "everybody"!'), 17)->toString());
    }

    public function testPropertiesExtractorShouldReturnTheArrayProperties()
    {
        $formatter = new UserVarDumpModelFormatter();
        $this->assertEquals(' [0]=> int(8) [1]=> string(5) "Hello}{" [2]=> bool(true) ', $formatter->extractProperties(u('array(3) { [0]=> int(8) [1]=> string(5) "Hello}{" [2]=> bool(true) }'))->toString());
    }
}
