<?php
namespace App\Providers;

use App\Models\Product;
use App\Repositories\Product\Eloquent\EloquentProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Models\Hold;
use App\Repositories\Hold\Eloquent\EloquentHoldRepository;
use App\Repositories\Hold\HoldRepositoryInterface;
use App\Models\Order;
use App\Repositories\Order\Eloquent\EloquentOrderRepository;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Models\PaymentWebhook;
use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Repositories\Payment\Eloquent\EloquentPaymentRepository;
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductRepositoryInterface::class, function(){
            return new EloquentProductRepository( new Product());
        });

        $this->app->singleton(HoldRepositoryInterface::class, function(){
            return new EloquentHoldRepository(new Hold(), new Product());
        });
        $this->app->singleton(OrderRepositoryInterface::class, function(){
            return new EloquentOrderRepository(new Order(),new Hold());
        });
        $this->app->singleton(PaymentRepositoryInterface::class, function(){
            return new EloquentPaymentRepository(new PaymentWebhook());
        });
    }
    
    public function boot(): void
    {
        //
    }
}