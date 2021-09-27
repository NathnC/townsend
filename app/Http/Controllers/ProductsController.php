<?php

namespace App\Http\Controllers;

use App\store_products;
use App\store_products_og;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Couchbase\defaultDecoder;

class ProductsController extends Controller
{
    public $storeId;
    public $storeProducts;
    public $storeProductsog;

    public function __construct(store_products $storeProducts,store_products_og $storeProductsog)
    {
        /* As the system manages multiple stores a storeBuilder instance would
        normally be passed here with a store object. The id of the example
        store is being set here for the purpose of the test */
        $this->storeId = 3;
        $this->storeProducts = $storeProducts;
        $this->storeProductsog = $storeProductsog;
    }

    public function index()
    {
        try {
            $get = $this->storeProducts->sectionProducts($this->storeId);
            return response()->json(json_encode($get, JSON_THROW_ON_ERROR));
        } catch (\Exception $exception) {
            Log::warning($exception->getMessage());
            return response()->json('sectionProducts search failed!', 500);
        }
    }

    public function show($sectionName)
    {
        try {
            $get = $this->storeProducts->sectionProducts($this->storeId, $sectionName, 20,8,'az');
            return response()->json(json_encode($get, JSON_THROW_ON_ERROR));
        } catch (\Exception $exception) {
            Log::warning($exception->getMessage());
            return response()->json('sectionProducts search failed!', 500);
        }
    }
}
