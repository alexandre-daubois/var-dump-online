<?php

namespace App\Tests\Formatter;

use App\Entity\Formatter\Node;
use App\Entity\UserVarDumpModel;
use App\Exception\UnknownTypeException;
use App\Service\FormatterResultChecker;
use App\Service\UserVarDumpModelFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarExporter\VarExporter;

class UserVarDumpModelFormatterTest extends TestCase
{
    public function provideSimpleNodes(): \Generator
    {
        $node = (new Node())
            ->setType(Node::TYPE_FLOAT)
            ->setValue(4.5);
        yield [$node, 'float(4.5)'];

        $node = (new Node())
            ->setType(Node::TYPE_INT)
            ->setValue(5);
        yield [$node, 'int(5)'];

        $node = (new Node())
            ->setType(Node::TYPE_BOOLEAN)
            ->setValue('true');
        yield [$node, 'bool(true)'];

        $node = (new Node())
            ->setType(Node::TYPE_BOOLEAN)
            ->setValue('false');
        yield [$node, 'bool(false)'];

        $node = (new Node())
            ->setType(Node::TYPE_ENUM)
            ->setValue('Foo::BAR');
        yield [$node, 'enum(Foo::BAR)'];

        $node = (new Node())
            ->setType(Node::TYPE_NULL)
            ->setDepth(1);
        yield [$node, 'NULL'];
        yield [$node, 'null'];

        $node = (new Node())
            ->setType(Node::TYPE_RESOURCE)
            ->setValue('stream')
            ->setExtraData([
                'internalId' => '1',
            ]);
        yield [$node, 'resource(1) of type (stream)'];

        $node = (new Node())
            ->setType(Node::TYPE_STRING)
            ->setValue('Hello world')
            ->setExtraData([
                'length' => 11,
            ]);
        yield [$node, 'string(11) "Hello world"'];

        $node = (new Node())
            ->setType(Node::TYPE_STRING)
            ->setValue('string(65)')
            ->setExtraData([
                'length' => 10,
            ]);
        yield [$node, 'string(10) "string(65)"'];

        $node = (new Node())
            ->setType(Node::TYPE_ARRAY)
            ->setValue(0);
        yield [$node, 'array(0) { }"'];
    }

    /**
     * @dataProvider provideSimpleNodes
     */
    public function testCreateScalarNode(Node $node, string $dump): void
    {
        $checker = $this->createMock(FormatterResultChecker::class);
        $checker->expects($this->once())
            ->method('checkFormatResult')
            ->willReturn(true);

        $formatter = new UserVarDumpModelFormatter($checker);

        $model = (new UserVarDumpModel())
            ->setContent($dump);

        $generatedNode = current($formatter->format($model)->getChildren());
        $this->assertSame($node->getType(), $generatedNode->getType());
        $this->assertSame($node->getExtraData(), $generatedNode->getExtraData());
        $this->assertSame($node->getValue(), $generatedNode->getValue());
        $this->assertEmpty($node->getChildren());
    }

    public function testSendUnknownType(): void
    {
        $formatter = new UserVarDumpModelFormatter($this->createMock(FormatterResultChecker::class));

        $model = (new UserVarDumpModel())
            ->setContent('Hello world');

        $this->expectException(UnknownTypeException::class);
        $this->expectExceptionMessage('Hello world');
        $formatter->format($model)->getChildren();
    }

    public function provideNestedData(): \Generator
    {
        $dump = <<<VARDUMP
array(4) {
  ["hey"]=>
  object(stdClass)#3 (0) {
  }
  [0]=>
  int(1)
  [1]=>
  bool(true)
  [2]=>
  float(5.6)
}
VARDUMP;

        $root = (new Node())
            ->setDepth(0);

        $root->addChild(
            (new Node())
                ->setType(Node::TYPE_ARRAY)
                ->setValue(4)
                ->setDepth(1)
                ->addChild(
                    (new Node())
                        ->setType(Node::TYPE_OBJECT)
                        ->setValue('stdClass')
                        ->setExtraData([
                            'internalId' => '3',
                            'propertiesCount' => '0',
                            'propertyName' => '"hey"',
                        ])
                )
                ->addChild(
                    (new Node())
                        ->setType(Node::TYPE_INT)
                        ->setValue(1)
                        ->setExtraData([
                            'propertyName' => '0',
                        ])
                )
                ->addChild(
                    (new Node())
                        ->setType(Node::TYPE_BOOLEAN)
                        ->setValue('true')
                        ->setExtraData([
                            'propertyName' => '1',
                        ])
                )
                ->addChild(
                    (new Node())
                        ->setType(Node::TYPE_FLOAT)
                        ->setValue(5.6)
                        ->setExtraData([
                            'propertyName' => '2',
                        ])
                )
        );

        yield [$root, $dump];
    }

    /**
     * @dataProvider provideNestedData
     */
    public function testNestedParse(Node $root, string $dump): void
    {
        $checker = $this->createMock(FormatterResultChecker::class);
        $checker->expects($this->once())
            ->method('checkFormatResult')
            ->willReturn(true);

        $formatter = new UserVarDumpModelFormatter($checker);

        $model = new UserVarDumpModel();
        $model->setContent($dump);

        $generatedNode = $formatter->format($model);

        $this->assertSame(VarExporter::export($root), VarExporter::export($generatedNode));
    }
}
