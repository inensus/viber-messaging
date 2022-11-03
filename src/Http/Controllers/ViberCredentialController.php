<?php

namespace Inensus\ViberMessaging\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Inensus\ViberMessaging\Http\Requests\ViberCredentialRequest;
use Inensus\ViberMessaging\Http\Resources\ViberResource;
use Inensus\ViberMessaging\Services\ViberCredentialService;

class ViberCredentialController extends Controller
{

    public function __construct(private ViberCredentialService $credentialService)
    {

    }

    public function show(): ViberResource
    {
        return ViberResource::make($this->credentialService->getCredentials());
    }

    public function update(ViberCredentialRequest $request): ViberResource
    {
        $apiToken = $request->input('api_token');
        $id = $request->input('id');
        $webhookUrl = URL::to('/') . '/api/viber-messaging/webhook';

        /*
         * this is for mpm cloud
        $webhookUrl = URL::to('/') . '/api/viber-messaging/webhook/' + companyName;
        */

        $credentials = $this->credentialService->updateCredentials([
            'id' => $id,
            'api_token' => $apiToken,
            'webhook_url' => $webhookUrl,
        ]);
        return ViberResource::make($credentials);
    }
}
