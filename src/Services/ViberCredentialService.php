<?php

namespace Inensus\ViberMessaging\Services;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Inensus\ViberMessaging\Models\ViberCredential;

class ViberCredentialService
{
    private $rootUrl;

    public function __construct(
        private ViberCredential $credential,
        private WebhookService $webhookService

    ) {

    }

    /**
     * This function uses one time on installation of the package.
     *
     */
    public function createCredentials()
    {
        return $this->credential->newQuery()->firstOrCreate(['id' => 1], [
            'api_url' => null,
            'api_token' => null
        ]);
    }

    public function getCredentials()
    {
        return $this->credential->newQuery()->first();
    }

    public function updateCredentials($data)
    {
        $credential = $this->credential->newQuery()->find($data['id']);

        $credential->update([
            'api_token' => $data['api_token'],
            'webhook_url' => $data['webhook_url'],
        ]);
        $credential->save();

        if (!$credential->has_webhook_created) {
            $this->webhookService->createWebHook($credential);
        }

        return $credential->fresh();
    }
}
