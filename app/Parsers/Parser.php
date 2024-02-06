<?php

namespace App\Parsers;

use App\Interfaces\ParserInterface;
use DOMDocument;
use Illuminate\Support\Collection;
use stdClass;

abstract class Parser implements ParserInterface
{
    protected Collection $collectedData;
    protected string $project;

    abstract public function parse(stdClass $email);
    abstract public function writeData(): void;

    public function __construct()
    {
        $this->collectedData = collect();
    }

    public function setProject(string $project)
    {
        $this->project = $project;
    }

    protected function parseDom(string $html, Collection &$data): void
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors(false);

        $results = collect();
        collect($dom->getElementsByTagName('dt'))
            ->each(function ($node) use (&$results) {
                $key = trim($node->nodeValue);
                $value = trim($node->nextSibling->nodeValue);
                $results->put($key, $value);
            });

        $this->convertData($results, $data);

        $this->collectedData->push($data);
    }

}
