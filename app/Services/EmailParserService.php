<?php

namespace App\Services;

use App\Interfaces\ParserInterface;
use App\Models\ChangeOrder;
use DOMDocument;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use stdClass;

class EmailParserService
{
    protected ParserInterface $parser;

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

    public function parseFile(string $filename): void
    {
        $file = Storage::path($filename);
//        echo $file;

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
//            echo '<br>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
//            echo $email->subject;
//            echo '<br>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~';
//            $body = $email->body->content;
//            echo $body;
        });
//        !d($this->changeOrderData);
        $this->parser->writeData();
    }



}
