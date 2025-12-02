<?php
namespace App\Http\Controllers\API\V1;
use Illuminate\Http\Request;
use App\Http\Requests\WebHookRequest;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentService;
use App\Http\Controllers\Controller;
use App\Transformers\API\V1\WebHookTransformer;
class WebHookController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}
    public function handle(WebHookRequest $request)
    {
       
        $payload = $request->validated();
        $result = $this->paymentService->processWebhook($payload);
        return response()->json($result);
        
       
    }
}