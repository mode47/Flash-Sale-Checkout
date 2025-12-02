<?php
namespace App\Http\Controllers\API\V1;
use App\Services\Hold\HoldService;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\HoldRequests;
use App\Transformers\API\V1\HoldTransformer;

class HoldController extends Controller
{
    public function __construct(private HoldService $holdService) {}
    public function store(HoldRequests $request)
    {
        $hold = $this->holdService->createHold($request->validated());
        return responder()->success($hold,HoldTransformer::class)
            ->respond(Response::HTTP_CREATED);
        }
    public function show(string $id)
    {
        // TODO: get hold for current user
       $hold= $this->holdService->getHoldDetails($id);
         return responder()->success($hold,HoldTransformer::class)
            ->respond(Response::HTTP_OK);
    }
}



