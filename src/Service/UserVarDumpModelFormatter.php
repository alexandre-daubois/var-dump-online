<?php


namespace App\Service;


use App\Entity\Formatter\Node;
use App\Entity\UserVarDumpModel;
use App\Exception\UnknownTypeException;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\VarDumper\VarDumper;
use function Symfony\Component\String\u;

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
     * @param string $content
     * @param Node|null $currentNode
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

            $this->processProperties($this->extractProperties($content), $node);
        } else if ($content->startsWith(Node::TYPE_FLOAT)) {
            $node = $this->createPrimitiveNode(Node::TYPE_FLOAT, $content);
        } else if ($content->startsWith(Node::TYPE_INT)) {
            $node = $this->createPrimitiveNode(Node::TYPE_INT, $content);
        } else if ($content->startsWith(Node::TYPE_STRING)) {
            $node = new Node();

            $node
                ->setType(Node::TYPE_STRING)
                ->setExtraData([
                    'length' => intval($this->extractValue(Node::TYPE_STRING, $content)->toString())
                ])
                ->setValue($this->extractStringValue($content, intval($node->getExtraData()['length'])));
        } else if ($content->startsWith(Node::TYPE_OBJECT)) {
            $node = new Node();
            $node
                ->setType(Node::TYPE_OBJECT)
                ->setValue($this->extractValue(Node::TYPE_OBJECT, $content))
                ->setDepth($currentNode->getDepth() + 1);

            $node->addExtraData('internalId', $content->after('#')->before(' ')->toString());
            $this->processProperties($this->extractProperties($content), $node);
        } else {
            throw new UnknownTypeException($content->toString());
        }

        $currentNode->addChild($node);

        return $node;
    }

    /**
     * @param string $type
     * @param UnicodeString $content
     * @return Node
     */
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

    /**
     * @param string $type
     * @param UnicodeString $content
     * @return UnicodeString
     */
    private function extractValue(string $type, UnicodeString $content): UnicodeString
    {
        return $content
            ->after($type . '(')
            ->before(')');
    }

    /**
     * @param UnicodeString $content
     * @param int $length
     * @return UnicodeString
     */
    private function extractStringValue(UnicodeString $content, int $length): UnicodeString
    {
        $subString = $content
            ->after('"');

        return u(substr($subString, 0, $length));
    }

    /**
     * @param UnicodeString $content
     * @return UnicodeString
     */
    private function extractProperties(UnicodeString $content): UnicodeString
    {
        return $content
            ->after('{')
            ->beforeLast('}');
    }

    /**
     * @param UnicodeString $content
     * @param Node $currentNode
     * @throws UnknownTypeException
     */
    private function processProperties(UnicodeString $content, Node $currentNode): void
    {
        preg_match_all('/(int\([-+]?\d+\))|(float\([-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\))|(string\(\d+\) ".*")/U', $content->trimStart()->toString(), $matches, PREG_OFFSET_CAPTURE);
        preg_match_all('/\[(\d+|\".+\")]=>/U', $content->trim()->toString(), $keyMatches);

        for ($i = 0; $i < count($matches[0]); ++$i) {
            $offset = $matches[0][$i][1]; // Offset key thanks to PREG_OFFSET_CAPTURE
            $value = substr($content->trim()->toString(), $offset);
            $propertyNode = $this->processContent($value, $currentNode);

            $propertyNode->addExtraData(
                'propertyName',
                u($keyMatches[0][$i])->after('[')->beforeLast(']=>')->toString()
            );
        }
    }
}