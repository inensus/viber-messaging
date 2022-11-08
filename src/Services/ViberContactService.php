<?php

namespace Inensus\ViberMessaging\Services;

use Inensus\ViberMessaging\Models\ViberContact;

class ViberContactService
{

    public function __construct(private ViberContact $viberContact)
    {
    }

    public function createContact($personId, $viberId)
    {
        return $this->viberContact->newQuery()->firstOrCreate(['person_id' => $personId], [
            'viber_id' => $viberId,
        ]);
    }
}