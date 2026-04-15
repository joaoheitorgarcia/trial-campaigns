<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCampaignRequest;
use App\Http\Resources\Campaign as CampaignResource;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiCampaignController extends Controller
{
    public function __construct(
        private readonly CampaignService $campaignService
    ) {}

    public function list(Request $request)
    {
        $campaigns = Campaign::query()
            ->with('contactList')
            ->withSendStats()
            ->latest()
            ->paginate($this->perPage($request));

        return CampaignResource::collection($campaigns);
    }

    public function create(CreateCampaignRequest $request)
    {
        $campaign = Campaign::create([
            ...$request->validated(),
            'status' => Campaign::STATUS_DRAFT,
        ]);

        return (new CampaignResource($this->campaignForResponse($campaign)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Campaign $campaign)
    {
        return new CampaignResource($this->campaignForResponse($campaign));
    }

    public function dispatch(Campaign $campaign)
    {
        $this->campaignService->dispatch($campaign);

        return new CampaignResource($this->campaignForResponse($campaign));
    }

    private function campaignForResponse(Campaign $campaign): Campaign
    {
        return Campaign::query()
            ->with('contactList')
            ->withSendStats()
            ->findOrFail($campaign->id);
    }
}
