<?php

namespace App\Services;

use App\Interfaces\ParserInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use stdClass;

class EmailParserService
{
    protected ParserInterface $parser;
    protected string $project;
    protected string $directory;

    protected Collection $changeOrderData;

    public function __construct()
    {
        $this->changeOrderData = collect();
    }

    public function setParser(ParserInterface $parser): self
    {
        $this->parser = $parser;

        return $this;
    }

    public function setProject(string $project): self
    {
        $this->project = $project;

        if ($this->parser) {
            $this->parser->setProject($project);
        }

        return $this;
    }

    public function setDirectory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function parseDirectory()
    {
        $files = Storage::disk('local')->files($this->directory);
        collect($files)->each(function ($file) {
            $this->parseFile($file);
        });
    }

    public function parseFile(string $filename): void
    {
        $file = Storage::path($filename);

        if (file_exists($file)) {
            $contents = file_get_contents($file);
            $json = json_decode($contents);
            $this->iterateMessages($json);
        }
    }

    protected function iterateMessages(stdClass $json): void
    {
        collect($json->value)->each(function ($email) {
            $this->parser->parse($email);
        });

        $this->parser->writeData();
    }
}
