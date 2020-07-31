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
    const TYPE_ARRAY = 'array';
    const TYPE_FLOAT = 'float';
    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_OBJECT = 'object';

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

        if ($content->startsWith(self::TYPE_ARRAY)) {
            $node = new Node();
            $node
                ->setType(self::TYPE_ARRAY)
                ->setValue($this->extractValue(self::TYPE_ARRAY, $content));

            $node->setDepth($currentNode->getDepth() + 1);

            $this->processProperties($this->extractArrayProperties($content), $node);
        } else if ($content->startsWith(self::TYPE_FLOAT)) {
            $node = $this->createPrimitiveNode(self::TYPE_FLOAT, $content);
        } else if ($content->startsWith(self::TYPE_INT)) {
            $node = $this->createPrimitiveNode(self::TYPE_INT, $content);
        } else if ($content->startsWith(self::TYPE_STRING)) {
            $node = new Node();

            $node
                ->setType(self::TYPE_STRING)
                ->setExtraData([
                    'length' => intval($this->extractValue(self::TYPE_STRING, $content)->toString())
                ])
                ->setValue($this->extractStringValue($content));
        } else if ($content->startsWith(self::TYPE_OBJECT)) {
            // todo :)
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
     * @return UnicodeString
     */
    private function extractStringValue(UnicodeString $content): UnicodeString
    {
        return $content
            ->after('"')
            ->beforeLast('"');
    }

    /**
     * @param UnicodeString $content
     * @return UnicodeString
     */
    private function extractArrayProperties(UnicodeString $content): UnicodeString
    {
        return $content
            ->after('{')
            ->beforeLast('}');
    }

    /**
     * @param UnicodeString $content
     * @param Node $currentNode
     * @return \Iterator
     * @throws UnknownTypeException
     */
    private function processProperties(UnicodeString $content, Node $currentNode): void
    {
        // While it contains an opening bracket, there might be new properties
        $matches = preg_split('/\[(\d+|\".+\")\]\=\>/', $content->trim()->toString(), -1, PREG_SPLIT_NO_EMPTY);
        preg_match_all('/\[(\d+|\".+\")\]\=\>/', $content->trim()->toString(), $keysMatch);

        for ($i = 0; $i < count($matches); ++$i) {
            $match = u($matches[$i])->trim();
            $node = $this->processContent($match, $currentNode);
            $node
                ->setExtraData([
                    // What if the property contains these tokens ?
                    'propertyName' => u($keysMatch[0][$i])->after('[')->beforeLast(']=>')->toString()
                ]);
        }
    }
}