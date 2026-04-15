<?php

namespace App\Services;

use App\Jobs\SendCampaignEmail;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class CampaignService
{
    /**
     * Dispatch a campaign to all active contacts in its list.
     */
    public function dispatch(Campaign $campaign): void
    {
        $contactList = $campaign->contactList;
        if (!$contactList) {
            Log::warning('Campaign dispatch skipped: missing contact list', [
                'campaign_id' => $campaign->id,
                'contact_list_id' => $campaign->contact_list_id,
            ]);

            return;
        }

        $contacts = $contactList->contacts()
            ->where('status', Contact::STATUS_ACTIVE)
            ->get();

        foreach ($contacts as $contact) {
            $send = CampaignSend::firstOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'contact_id'  => $contact->id,
                ],
                [
                    'status' => CampaignSend::STATUS_PENDING,
                ],
            );

            if ($send->wasRecentlyCreated) {
                SendCampaignEmail::dispatch($send->id);
            }
        }

        $campaign->update(['status' => Campaign::STATUS_SENDING]);
    }

    public function buildPayload(Campaign $campaign, array $extra = []): array
    {
        $base = [
            'subject' => $campaign->subject,
            'body'    => $campaign->body,
        ];

        return [...$base, ...$extra];
    }

    public function resolveReplyTo(Campaign $campaign)
    {
        if (empty($campaign->reply_to)) {
            return null;
        }

        return $campaign->reply_to;
    }
}
