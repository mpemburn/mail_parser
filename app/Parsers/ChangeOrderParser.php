<?php

namespace App\Parsers;

use App\Interfaces\ParserInterface;
use App\Models\ChangeOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use stdClass;

class ChangeOrderParser extends Parser implements ParserInterface
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

    public function parse(stdClass $email)
    {
        $data = collect();
        $subject = str_replace('Change Management Form: ', '', $email->subject);
        $data->put('subject', $subject);
        $data->put('from', $email->from->emailAddress->address);
        // Extract the description list
        $body = $email->body->content;
        $pre = stripos($body, '<dl class');
        // Skip if this is a reply email
        if (! $pre) {
            return;
        }
        // Get the final </dl> tag
        $post = strrpos($body, '</dl>');
        $list = substr($body, $pre, ($post - $pre) + 5);
        $this->parseDom($list, $data);
    }

    protected function convertData(Collection $results, Collection &$data): void
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
    }
    public function writeData(): void
    {
        $this->collectedData->each(function ($data) {
            // Make sure this one hasn't already been processed
            if(ChangeOrder::where('subject', $data->get('subject'))
                ->where('change_date', $data->get('change_date'))
                ->exists()) {
                return;
            }

            ChangeOrder::create($data->toArray());
        });
    }
}
