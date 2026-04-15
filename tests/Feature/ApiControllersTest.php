<?php

namespace Tests\Feature;

use App\Jobs\SendCampaignEmail;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\ContactList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiControllersTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_can_be_created_listed_and_unsubscribed(): void
    {
        $createResponse = $this->postJson('/api/contacts', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'status' => Contact::STATUS_ACTIVE,
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Alice')
            ->assertJsonPath('data.email', 'alice@example.com')
            ->assertJsonPath('data.status', Contact::STATUS_ACTIVE);

        $contactId = $createResponse->json('data.id');

        $this->getJson('/api/contacts')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->postJson("/api/contacts/{$contactId}/unsubscribe")
            ->assertOk()
            ->assertJsonPath('data.status', Contact::STATUS_UNSUBSCRIBED);
    }

    public function test_contact_validation_failure_returns_message_and_status_code(): void
    {
        $this->postJson('/api/contacts', [])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonValidationErrors(['name', 'email', 'status']);
    }

    public function test_contact_lists_can_be_created_listed_and_have_contacts_added(): void
    {
        $contact = Contact::factory()->create();

        $createResponse = $this->postJson('/api/contact-lists', [
            'name' => 'Customers',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Customers')
            ->assertJsonPath('data.contacts_count', 0);

        $contactListId = $createResponse->json('data.id');

        $this->postJson("/api/contact-lists/{$contactListId}/contacts", [
            'contact_id' => $contact->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.contacts_count', 1);

        $this->getJson('/api/contact-lists')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('contact_contact_list', [
            'contact_id' => $contact->id,
            'contact_list_id' => $contactListId,
        ]);
    }

    public function test_missing_contact_list_returns_api_not_found_message(): void
    {
        $contact = Contact::factory()->create();

        $this->postJson('/api/contact-lists/999/contacts', [
            'contact_id' => $contact->id,
        ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Contact list not found.');
    }

    public function test_campaigns_can_be_created_listed_shown_and_dispatched(): void
    {
        Queue::fake();

        $contact = Contact::factory()->create([
            'status' => Contact::STATUS_ACTIVE,
        ]);
        $contactList = ContactList::factory()->create();
        $contactList->contacts()->attach($contact->id);

        $createResponse = $this->postJson('/api/campaigns', [
            'subject' => 'Spring Sale',
            'body' => 'Campaign body',
            'contact_list_id' => $contactList->id,
            'scheduled_at' => now()->addHour()->toISOString(),
            'reply_to' => 'reply@example.com',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.subject', 'Spring Sale')
            ->assertJsonPath('data.status', Campaign::STATUS_DRAFT)
            ->assertJsonPath('data.stats.total', 0)
            ->assertJsonPath('data.contact_list.id', $contactList->id);

        $campaignId = $createResponse->json('data.id');

        $this->getJson('/api/campaigns')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.stats.total', 0);

        $this->getJson("/api/campaigns/{$campaignId}")
            ->assertOk()
            ->assertJsonPath('data.id', $campaignId)
            ->assertJsonPath('data.contact_list.id', $contactList->id);

        $this->postJson("/api/campaigns/{$campaignId}/dispatch")
            ->assertOk()
            ->assertJsonPath('data.status', Campaign::STATUS_SENDING)
            ->assertJsonPath('data.stats.pending', 1)
            ->assertJsonPath('data.stats.total', 1);

        Queue::assertPushed(SendCampaignEmail::class, 1);

        $this->assertDatabaseHas('campaign_sends', [
            'campaign_id' => $campaignId,
            'contact_id' => $contact->id,
            'status' => 'pending',
        ]);
    }
}
