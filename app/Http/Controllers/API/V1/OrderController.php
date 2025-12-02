<?php
namespace App\Http\Controllers\API\V1;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Order\OrderService;
use Symfony\Component\HttpFoundation\Response;
use App\Transformers\API\V1\OrderTransformer;
class OrderController extends Controller
{
   public function __construct(private OrderService $orderService) {}
        public function store(OrderRequest $request)
    {  
        $data = $request->validated();
        $order = $this->orderService->createOrder($data['hold_id']);
        return responder()->success($order,OrderTransformer::class)
            ->respond(Response::HTTP_CREATED);
    }   
}
