<?php

namespace App\Providers;

use App\Repositories\Contracts\BitRepositoryInterface;
use App\Repositories\Contracts\BladeRepositoryInterface;
use App\Repositories\Contracts\RatchetRepositoryInterface;
use App\Repositories\Contracts\SeriesRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\BeybladeRepositoryInterface;
use App\Repositories\Eloquent\BitRepository;
use App\Repositories\Eloquent\BladeRepository;
use App\Repositories\Eloquent\RatchetRepository;
use App\Repositories\Eloquent\SeriesRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\BeybladeRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BladeRepositoryInterface::class, BladeRepository::class);
        $this->app->bind(RatchetRepositoryInterface::class, RatchetRepository::class);
        $this->app->bind(BitRepositoryInterface::class, BitRepository::class);
        $this->app->bind(SeriesRepositoryInterface::class, SeriesRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(BeybladeRepositoryInterface::class, BeybladeRepository::class);
    }
}
