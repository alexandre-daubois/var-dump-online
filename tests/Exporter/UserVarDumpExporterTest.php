<?php

namespace App\Tests\Exporter;

use App\Entity\Formatter\Node;
use App\Service\UserVarDumpExporter;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class UserVarDumpExporterTest extends TestCase
{
    public function testUnsupportedFormatShouldRaiseAnException()
    {
        /** @var Environment $twig */
        $twig = $this->createMock(Environment::class);
        /** @var SerializerInterface $serializer */
        $serializer = $this->createMock(SerializerInterface::class);

        $exporter = new UserVarDumpExporter($serializer, $twig);

        $this->expectExceptionMessage('Format is not supported (got invalid_format)');
        $exporter->export(new Node(), 'invalid_format');
    }

    public function testJsonFormatShouldTriggerJMSSerializer()
    {
        $root = new Node();
        $root->setDepth(0);
        $root->addChild(new Node());

        /** @var Environment $twig */
        $twig = $this->createMock(Environment::class);

        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer->expects($this->once())
            ->method('serialize');

        $exporter = new UserVarDumpExporter($serializer, $twig);

        $exporter->export($root, UserVarDumpExporter::FORMAT_JSON);
    }

    public function testXMLFormatShouldTriggerJMSSerializer()
    {
        $root = new Node();
        $root->setDepth(0);
        $root->addChild(new Node());

        /** @var Environment $twig */
        $twig = $this->createMock(Environment::class);

        $serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer->expects($this->once())
            ->method('serialize');

        $exporter = new UserVarDumpExporter($serializer, $twig);

        $exporter->export($root, UserVarDumpExporter::FORMAT_XML);
    }

    public function testVardumpFormatShouldTriggerExporterCustomMethod()
    {
        $root = new Node();
        $root->setDepth(0);
        $root->addChild(new Node());

        $exporter = $this->getMockBuilder(UserVarDumpExporter::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatVardump'])
            ->getMock();

        $exporter->expects($this->once())
            ->method('formatVardump');

        $exporter->export($root, UserVarDumpExporter::FORMAT_VARDUMP);
    }
}
