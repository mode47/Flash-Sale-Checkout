<?php
namespace App\Http\Controllers\API\V1;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use App\Transformers\API\V1\ProductTransformer;
use Symfony\Component\HttpFoundation\Response;
class ProductController extends Controller
{   
    public function __construct(private ProductService $productService){}
    public function show($id)
    {         
        $data = $this->productService->getProductById($id);
        dd($data);
        return responder()->success($data, ProductTransformer::class)
            ->respond(Response::HTTP_OK);
    }
}
