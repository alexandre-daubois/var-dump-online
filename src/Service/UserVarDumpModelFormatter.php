<?php

namespace App\Service;

use App\Entity\Formatter\Node;
use App\Entity\UserVarDumpModel;
use App\Exception\UnknownTypeException;
use function Symfony\Component\String\u;
use Symfony\Component\String\UnicodeString;

class UserVarDumpModelFormatter
{
    /**
     * @var Node
     */
    private $root;

    public function __construct()
    {
        $this->root = new Node();
        $this->root->setDepth(0);
    }

    public function format(UserVarDumpModel $model)
    {
        $content = $model->getContent();

        $this->processContent($content, $this->root);

        return $this->root;
    }

    /**
     * @param Node|null $currentNode
     *
     * @throws UnknownTypeException
     */
    private function processContent(string $content, Node $currentNode)
    {
        $content = u($content);
        $node = null;

        if ($content->startsWith(Node::TYPE_ARRAY)) {
            $node = new Node();
            $node
                ->setType(Node::TYPE_ARRAY)
                ->setValue($this->extractValue(Node::TYPE_ARRAY, $content))
                ->setDepth($currentNode->getDepth() + 1);

            $this->processProperties($this->extractProperties($content), $node, $node->getValue());
        } elseif ($content->startsWith(Node::TYPE_FLOAT)) {
            $node = $this->createPrimitiveNode(Node::TYPE_FLOAT, $content);
        } elseif ($content->startsWith(Node::TYPE_BOOLEAN)) {
            $node = $this->createPrimitiveNode(Node::TYPE_BOOLEAN, $content);
        } elseif ($content->startsWith(Node::TYPE_INT)) {
            $node = $this->createPrimitiveNode(Node::TYPE_INT, $content);
        } elseif ($content->startsWith(Node::TYPE_NULL)) {
            $node = new Node();
            $node->setType(Node::TYPE_NULL);
        } elseif ($content->startsWith(Node::TYPE_STRING)) {
            $node = new Node();

            $node
                ->setType(Node::TYPE_STRING)
                ->setExtraData([
                    'length' => intval($this->extractValue(Node::TYPE_STRING, $content)->toString()),
                ])
                ->setValue($this->extractStringValue($content, intval($node->getExtraData()['length'])));
        } elseif ($content->startsWith(Node::TYPE_OBJECT)) {
            $node = new Node();
            $node
                ->setType(Node::TYPE_OBJECT)
                ->setValue($this->extractValue(Node::TYPE_OBJECT, $content))
                ->setDepth($currentNode->getDepth() + 1);

            $node->addExtraData('internalId', $content->after('#')->before(' ')->toString());
            $node->addExtraData('propertiesCount', $content->after('#')->after('(')->before(')')->toString());
            $this->processProperties($this->extractProperties($content), $node, $node->getExtraData()['propertiesCount']);
        } else {
            throw new UnknownTypeException($content->toString());
        }

        $currentNode->addChild($node);

        return $node;
    }

    private function createPrimitiveNode(string $type, UnicodeString $content): Node
    {
        $node = new Node();
        $node
            ->setType($type)
            ->setValue(
                $this->extractValue($type, $content)
            );

        return $node;
    }

    private function extractValue(string $type, UnicodeString $content): UnicodeString
    {
        return $content
            ->after($type.'(')
            ->before(')');
    }

    /**
     * We use `substr` to ensure we capture the whole string, even if it contains double quotes.
     */
    private function extractStringValue(UnicodeString $content, int $length): UnicodeString
    {
        $subString = $content
            ->after('"');

        return u(substr($subString, 0, $length));
    }

    private function extractProperties(UnicodeString $content): UnicodeString
    {
        return $content
            ->after('{')
            ->beforeLast('}');
    }

    /**
     * @throws UnknownTypeException
     */
    private function processProperties(UnicodeString $content, Node $currentNode, int $propertiesCount): void
    {
        $content = $content->trim();
        $sanitizedContent = $content->toString();

        // Catch arrays content
        preg_match('/((array\(\d+\))|object\(\d+\)) { (.*) }/', $sanitizedContent, $arrayContentMatches, PREG_OFFSET_CAPTURE);

        if (!empty($arrayContentMatches)) {
            for ($i = $arrayContentMatches[3][1]; $i < strlen($arrayContentMatches[3][0]) + $arrayContentMatches[3][1]; ++$i) {
                $sanitizedContent[$i] = '_';
            }
        }

        preg_match_all('/(int\([-+]?\d+\))|(float\([-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\))|(string\(\d+\) ".*")|(bool\((true|false)\))|(NULL)|(array\(\d+\))|(object\(.+\))/U', $sanitizedContent, $matches, PREG_OFFSET_CAPTURE);
        preg_match_all('/\[(\d+|.+)]=>/U', $sanitizedContent, $keyMatches);

        for ($i = 0; $i < count($matches[0]) && $i < $propertiesCount; ++$i) {
            $offset = $matches[0][$i][1]; // Offset key thanks to PREG_OFFSET_CAPTURE
            $value = substr($content->toString(), $offset);

            $propertyNode = $this->processContent($value, $currentNode);

            $propertyNode->addExtraData(
                'propertyName',
                u($keyMatches[0][$i])->after('[')->beforeLast(']=>')->toString()
            );
        }
    }
}
