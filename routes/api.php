<?php

use App\Http\Controllers\Api\ApiCampaignController;
use App\Http\Controllers\Api\ApiContactController;
use App\Http\Controllers\Api\ApiContactListController;
use Illuminate\Support\Facades\Route;

Route::prefix('contacts')->group(function () {
    Route::get('/', [ApiContactController::class, 'list']);
    Route::post('/', [ApiContactController::class, 'create']);
    Route::post('{contact}/unsubscribe', [ApiContactController::class, 'unsubscribe'])
        ->whereNumber('contact');
});

Route::prefix('contact-lists')->group(function () {
    Route::get('/', [ApiContactListController::class, 'list']);
    Route::post('/', [ApiContactListController::class, 'create']);
    Route::post('{contactList}/contacts', [ApiContactListController::class, 'addContact'])
        ->whereNumber('contactList');
});

Route::prefix('campaigns')->group(function () {
    Route::get('/', [ApiCampaignController::class, 'list']);
    Route::post('/', [ApiCampaignController::class, 'create']);
    Route::get('{campaign}', [ApiCampaignController::class, 'show'])
        ->whereNumber('campaign');
    Route::post('{campaign}/dispatch', [ApiCampaignController::class, 'dispatch'])
        ->middleware('campaign.draft')
        ->whereNumber('campaign');
});
