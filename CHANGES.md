# CHANGES

## Migrations

### Issue: Duplicate contact emails were allowed
**Issue**: `email` column in `contacts` table had no unique constraint.\
**Impact**: Multiple contact rows could share the same email address, which can cause the same campaign to be sent more than once to the same inbox.\
**Fix**: Added migration `2026_04_15_192411_add_unique_to_contacts_email.php` to enforce unique values on `email`.

### Issue: Duplicate `(contact_id, contact_list_id)` pairs were allowed in `contact_contact_list` table
**Issue**: The `contact_contact_list` table had no unique constraint for `(contact_id, contact_list_id)` pair.\
**Impact**: Same `contact` could be present multiple times in the same `contact_list`.\
**Fix**: Added `2026_04_15_192411_add_unique_to_contact_contact_list.php` to enforce a unique index on `(contact_id, contact_list_id)` pair.

### Issue: Duplicate `(campaign_id, contact_id)` pairs were allowed in `campaigns` table
• **Issue**: `campaign_sends` table had no unique constraint for the `(campaign_id, contact_id)` pair.\
**Impact**: The same `contact` could be added more than once to the same `campaign`.\
**Fix**: Added `2026_04_15_192411_add_unique_to_campaign_sends.php` to enforce a unique index on `(campaign_id, contact_id)` pair.

### Issue: `scheduled_at` column in `campaigns` table was stored as a string
**Issue**: The original campaign migration defined `scheduled_at` as `string` even though sccampaign_sendsheduling logic compares it to `now()`.\
**Impact**: String comparison to `now()` can schedule campaigns at the wrong time or skip them entirely.\
**Fix**: Added `2026_04_15_192411_alter_campaigns_scheduled_at_type.php` to change `scheduled_at` to `dateTime`.

### Issue: `reply_to` existed in code but not in the campaign schema
**Issue**: Campaign service referenced `reply_to`, but the column was not in the database schema.\
**Impact**: The app can fail or ignore the reply-to value because the code expects a field that does not exist in the database.\
**Fix**: Added `2026_04_15_210227_add_reply_to_to_campaigns_table.php` to add a nullable `reply_to` column to `campaigns`.

***

## Models

### `Contact`
**Issue**: Model was not present.\
**Impact**: When the app tries to load campaign recipients or send relations, it can break and stop the campaign from being dispatched.\
**Fix**: Added missing `Contact.php`.

### `ContactList`
**Issue**: Model was not present.\
**Impact**: When the app tries to load contact lists for a campaign, it can break and stop the dispatch flow.\
**Fix**: Added missing `ContactList.php`.

### `CampaignSend`
**Issue**: Model was not present.\
**Impact**: When the app tries to load campaign send records or related contact/campaign data, it can break during job processing.\
**Fix**: Added missing `CampaignSend.php`.

### `Campaign`

**Issue**: After changing `scheduled_at` to a datetime column, `Campaign` need to treat it as a datetime instead of a string.\
**Impact**:`scheduled_at` will be treated like a normal string instead of a datetime.\
**Fix**: Updated `Campaign.php` to cast `scheduled_at` as `datetime`.

**Issue**: `Campaign` needed relation updates for `ContactList` and `CampaignSend`, and it needed `reply_to` included in model configuration.\
**Impact**: If the model does not match its related models and database fields, parts like dispatch and payload building may not work correctly.\
**Fix**: Updated `Campaign.php` to use the correct relation classes and added `reply_to`.

**Issue**: `Campaign::getStatsAttribute()` counted results in PHP instead of asking the database for aggregated counts.\
**Impact**: Campaign list loading can load too many `campaign_sends` rows into memory (n+1).\
**Fix**: Used `scopeWithSendStats()` and `withCount(...)` to fetch count from db with aliases, then made `getStatsAttribute()` read from those values.

### `General`
### Issue: Status values were duplicated as raw strings
**Issue**: Status checks and updates were spread across the codebase.\
**Impact**: Typos or drift between code and database can break filtering, transitions, and updates without obvious failures.\
**Fix**: Added constants for status in `Campaign`, `CampaignSend`, and `Contact` models and updated callers to reference them.

***

## Service

### Issue: Campaign dispatch wasnt checking for null contactList
**Issue**: `$campaign->contactList->contacts()` didnt check if `$campaign->contactList` existed.\
**Impact**: Dispatch can crash for that campaign.\
**Fix**: Added a null check for the related contact list, logged and returned early if `null`.

### Issue: Campaign dispatch could queue the same send more than once
**Issue**: Dispatch logic could queue a send again for the same `(campaign_id, contact_id)` pair.\
**Impact**: The same contact could get duplicate send jobs for the same campaign.\
**Fix**: Changed send creation to use `CampaignSend::firstOrCreate()` with `campaign_id` and `contact_id`, and only queued the job when the send was newly created.

***

## Job

### Issue: Job processing wasn't checking for valid state
**Issue**: `SendCampaignEmail` could continue processing when `CampaignSend` record was in `sent` state.\
**Impact**: The job could run again for a send that was already processed, which can lead to unnecessary work or wrong send status updates.\
**Fix**: Added an early-return so only rows in `pending` or `failed` state continue processing.

*** 

## Middleware

### Issue: `EnsureCampaignIsDraft` had inverted validation logic
**Issue**: The middleware logic was inverted for draf-only actions.\
**Impact**: Valid draft actions were blocked by the middleware.\
**Fix**: Inverted the condition so the middleware now rejects only when the campaign status is not `draft`.

*** 

## Scheduler

### Issue: Scheduler was not filtering campaigns by state and `scheduled_at`
**Issue**: The scheduler query did not check that the campaign status was `draft` and did not exclude campaigns with `scheduled_at = null`.\
**Impact**: Campaigns in the wrong state, or campaigns without a scheduled time, could be picked up for dispatch.\
**Fix**: Updated the query to only load campaigns with `status = draft`, a non-null `scheduled_at`, and `scheduled_at <= now()`.
