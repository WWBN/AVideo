<?php
declare(strict_types=1);

namespace StubTests\Model;

use Exception;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Link;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\DocBlock\Tags\Since;
use PhpParser\Node;
use StubTests\Model\Tags\RemovedTag;
use StubTests\Parsers\DocFactoryProvider;

trait PHPDocElement
{
    /**
     * @var Link[]
     */
    public array $links = [];

    /**
     * @var See[]
     */
    public array $see = [];

    /**
     * @var Since[]
     */
    public array $sinceTags = [];

    /**
     * @var Deprecated[]
     */
    public array $deprecatedTags = [];

    /**
     * @var RemovedTag[]
     */
    public array $removedTags = [];

    /**
     * @var string[]
     */
    public array $tagNames = [];

    public bool $hasInheritDocTag = false;

    public bool $hasInternalMetaTag = false;

    protected function collectTags(Node $node): void{
        if ($node->getDocComment() !== null) {
            try {
                $phpDoc = DocFactoryProvider::getDocFactory()->create($node->getDocComment()->getText());
                $tags = $phpDoc->getTags();
                foreach ($tags as $tag) {
                    $this->tagNames[] = $tag->getName();
                }
                $this->links = $phpDoc->getTagsByName('link');
                $this->see = $phpDoc->getTagsByName('see');
                $this->sinceTags = $phpDoc->getTagsByName('since');
                $this->deprecatedTags = $phpDoc->getTagsByName('deprecated');
                $this->removedTags = $phpDoc->getTagsByName('removed');
                $this->hasInternalMetaTag = $phpDoc->hasTag('meta');
                $this->hasInheritDocTag = $phpDoc->hasTag('inheritdoc') || $phpDoc->hasTag('inheritDoc') ||
                    stripos($phpDoc->getSummary(), "inheritdoc") > 0;
            } catch (Exception $e) {
                $this->parseError = $e;
            }
        }
    }
}
