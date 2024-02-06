<?php

namespace App\Parsers;

use App\Models\ChangeOrder;
use App\Models\PagelyMessage;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use stdClass;

class PagelyParser extends Parser
{

    public function parse(stdClass $email)
    {
        $data = collect();
        $data->put('project', $this->project);
        $data->put('subject', $email->subject);
        $messageDate = Carbon::parse($email->sentDateTime);
        $data->put('message_date', $messageDate);
        $ticket = preg_replace('/(.*)(#)([\d]+)/', '$3', $email->subject);
        $data->put('ticket', $ticket);
        $data->put('from', $email->from->emailAddress->address);
        $body = $email->body->content;
        $data->put('body', strip_tags($body));


        $this->collectedData->push($data);
    }

    public function writeData(): void
    {
        $this->collectedData->each(function ($data) {
            // Make sure this one hasn't already been processed
            if(PagelyMessage::where('subject', $data->get('subject'))
                ->where('message_date', $data->get('message_date'))
                ->exists()) {
                return;
            }

            echo 'Writing...<br>';
            PagelyMessage::create($data->toArray());
        });
    }
}
