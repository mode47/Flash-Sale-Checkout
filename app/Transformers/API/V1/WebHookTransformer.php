<?php

namespace App\Transformers\API\V1;

use App\Models\PaymentWebhook;
use Flugg\Responder\Transformers\Transformer;

class WebHookTransformer extends Transformer
{
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * List of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Transform the model.
     *
     * @param  \App\Models\WebHook $webHook
     * @return array
     */
    public function transform(PaymentWebhook $webhook):array
    {
        return [
           'id'              => $webhook->id,
            'order_id'        => $webhook->order_id,
            'provider'        => $webhook->provider,
            'event'           => $webhook->event,
            'status'          => $webhook->status,
            'idempotency_key' => $webhook->idempotency_key,
            'payload'         => $webhook->payload ? json_decode($webhook->payload, true) : null,
            'processed_at'    => $webhook->processed_at,
            'created_at'      => $webhook->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
