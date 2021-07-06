<?php

namespace App\Service;

use App\Entity\Formatter\Node;
use App\Entity\UserVarDumpModel;
use App\Exception\FormatterResultCheckFailedException;
use App\Exception\UnknownTypeException;
use function Symfony\Component\String\u;
use Symfony\Component\String\UnicodeString;

class UserVarDumpModelFormatter
{
    private Node $root;

    private FormatterResultChecker $formatterResultChecker;

    public function __construct(FormatterResultChecker $formatterResultChecker)
    {
        $this->root = new Node();
        $this->root->setDepth(0);
        $this->formatterResultChecker = $formatterResultChecker;
    }

    public function format(UserVarDumpModel $model): Node
    {
        $content = $model->getContent();

        $this->processContent($content, $this->root);

        if (!$this->formatterResultChecker->checkFormatResult($this->root, $model)) {
            throw new FormatterResultCheckFailedException($this->root);
        }

        return $this->root;
    }

    public function processContent(string $rawContent, Node $currentNode): Node
    {
        $content = u($rawContent);

        if ($content->startsWith(Node::TYPE_ARRAY)) {
            $node = new Node();
            $node
                ->setType(Node::TYPE_ARRAY)
                ->setValue($this->extractValue(Node::TYPE_ARRAY, $content))
                ->setDepth($currentNode->getDepth() + 1);

            $this->processProperties($this->extractProperties($content), $node, $node->getValue());
        } elseif ($content->startsWith(Node::TYPE_FLOAT)) {
            $node = $this->createScalarNode(Node::TYPE_FLOAT, $content);
        } elseif ($content->startsWith(Node::TYPE_BOOLEAN)) {
            $node = $this->createScalarNode(Node::TYPE_BOOLEAN, $content);
        } elseif ($content->startsWith(Node::TYPE_INT)) {
            $node = $this->createScalarNode(Node::TYPE_INT, $content);
        } elseif ($content->startsWith(Node::TYPE_ENUM)) {
            $node = $this->createScalarNode(Node::TYPE_ENUM, $content);
        } elseif ($content->startsWith(Node::TYPE_NULL) || $content->startsWith(strtolower(Node::TYPE_NULL))) {
            $node = (new Node())
                ->setType(Node::TYPE_NULL);
        } elseif ($content->startsWith(Node::TYPE_RESOURCE)) {
            $node = (new Node())
                ->setType(Node::TYPE_RESOURCE)
                ->setValue($content->after('of type (')->before(')')->toString())
                ->addExtraData('internalId', $content->after('resource(')->before(')')->toString());
        } elseif ($content->startsWith(Node::TYPE_STRING)) {
            $node = (new Node())
                ->setType(Node::TYPE_STRING)
                ->setExtraData([
                    'length' => (int) $this->extractValue(Node::TYPE_STRING, $content)->toString(),
                ]);

            $node->setValue($this->extractStringValue($content, (int) $node->getExtraData()['length']));
        } elseif ($content->startsWith(Node::TYPE_OBJECT)) {
            $node = (new Node())
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

    private function createScalarNode(string $type, UnicodeString $content): Node
    {
        if (!\in_array($type, [Node::TYPE_INT, Node::TYPE_FLOAT, Node::TYPE_BOOLEAN, Node::TYPE_ENUM], true)) {
            throw new \InvalidArgumentException(sprintf('Type %s is not scalar.', $type));
        }

        return (new Node())
            ->setType($type)
            ->setValue(
                $this->extractValue($type, $content)
            );
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

    private function processProperties(UnicodeString $content, Node $currentNode, int $propertiesCount): void
    {
        if (0 === $propertiesCount) {
            return;
        }

        $trimmedContent = $content->trim();
        $sanitizedContent = $trimmedContent->toString();

        // Todo don't pay attention to brackets when in string context (to crash it : add one opening bracket without its closing one)
        $openingBracket = 0;
        for ($i = 0, $iMax = strlen($sanitizedContent); $i < $iMax; ++$i) {
            if ('{' === $sanitizedContent[$i]) {
                ++$openingBracket;

                if (1 === $openingBracket) {
                    continue;
                }
            } elseif ('}' === $sanitizedContent[$i]) {
                --$openingBracket;

                if (0 === $openingBracket) {
                    continue;
                }
            }

            if ($openingBracket >= 1) {
                $sanitizedContent[$i] = '_';
            }
        }

        preg_match_all('/(int\([-+]?\d+\))|(float\([-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\))|(string\(\d+\) ".*")|(bool\((true|false)\))|(NULL)|(array\(\d+\))|(object\(.+\)#(\d+) \(\d+\))|(resource\(\d+\))|(enum\(.+\))/U', $sanitizedContent, $matches, PREG_OFFSET_CAPTURE);
        preg_match_all('/\[(\d+|.+)]=>/U', $sanitizedContent, $keyMatches);

        for ($i = 0; $i < count($matches[0]) && $i < $propertiesCount; ++$i) {
            $offset = $matches[0][$i][1]; // Offset key thanks to PREG_OFFSET_CAPTURE
            $value = substr($trimmedContent->toString(), $offset);

            $propertyNode = $this->processContent($value, $currentNode);

            $propertyNode->addExtraData(
                'propertyName',
                u($keyMatches[0][$i])->after('[')->beforeLast(']=>')->toString()
            );
        }
    }
}
