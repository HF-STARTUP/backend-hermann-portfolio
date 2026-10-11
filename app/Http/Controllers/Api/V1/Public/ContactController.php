<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Contact\StoreContactMessageRequest;
use App\Models\ContactMessage;

/**
 * Route #12 — POST /api/v1/contact : création d'un message non lu.
 */
class ContactController extends Controller
{
    use ApiResponse;

    private const SUCCESS_MESSAGE = "Votre message a bien été envoyé. Je vous répondrai dans les plus brefs délais.";

    public function store(StoreContactMessageRequest $request)
    {
        ContactMessage::create($request->validated());

        return $this->success(null, self::SUCCESS_MESSAGE, 201);
    }
}
