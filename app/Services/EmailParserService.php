<?php

namespace App\Services;

use App\Models\ChangeOrder;
use DOMDocument;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use stdClass;

class EmailParserService
{
    const CHANGE_ORDER_FIELDS = [
        'Project Name:' => 'project',
        'ITS Sponsor:' => 'sponsor',
        'ITS Implementer:' => 'implementer',
        'System Changes:' => 'system_changes',
        'Description:' => 'description',
        'Effect of Changes:' => 'effect',
        'Reason for Changes:' => 'reason',
        'Date of Changes:' => 'change_date',
        'Expected Downtime:' => 'downtime',
        'Testing/Back-out Plan:' => 'back_out_plan',
        'Communication Recommendation:' => 'communication',
        'Comments/Other:' => 'comments',
    ];

    protected Collection $changeOrderData;

    public function __construct()
    {
        $this->changeOrderData = collect();
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

    protected function iterateMessages(stdClass $json)
    {
        collect($json->value)->each(function ($email) {
            $data = collect();
            $subject = str_replace('Change Management Form: ', '', $email->subject);
            $data->put('subject', $subject);
            $data->put('from', $email->from->emailAddress->address);
            $body = $email->body->content;
            // Extract the description list
            $pre = stripos($body, '<dl class');
            // Skip if this is a reply email
            if (! $pre) {
                return;
            }
            // Get the final </dl> tag
            $post = strrpos($body, '</dl>');
            $list = substr($body, $pre, ($post - $pre) + 5);

            $this->parseDom($list, $data);
        });

        $this->writeData();
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

        $this->changeOrderData->push($data);
    }

    protected function convertData(Collection $results, Collection &$data): Collection
    {
        $results->each(function ($value, $key) use ($data) {
            $field = self::CHANGE_ORDER_FIELDS[$key] ?? null;
            if ($field === 'change_date') {
                $value = Carbon::parse($value);
            }
            if ($field) {
                $data->put($field, $value);
            }
        });

        return $data;
    }

    protected function writeData(): void
    {
        $this->changeOrderData->each(function ($data) {
            ChangeOrder::create($data->toArray());
        });
    }
}
