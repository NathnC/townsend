<?php

namespace App;

use App\Models\StoreProduct;
use phpDocumentor\Reflection\Types\Array_;

class store_products
{
    public string $imagesDomain;

    /**
     * store_products constructor.
     */
    public function __construct()
    {
        $this->imagesDomain = config('app.image_domain');
    }

    /**
     * @param int $store_id
     * @param null $section
     * @param int $paginate
     * @param int $limit
     * @param string $sort
     * @return array
     */
    public function sectionProducts(int $store_id = null, $section = null, int $paginate = 20, int $limit = 8, string $sort = "position"): array
    {
        if ($store_id === null) {
            die;
        }

        $products = [];

        $query = StoreProduct::with('sections', 'artist');
        $query->where('deleted', 0)->where('available', 1);
        $query->paginate($paginate);
        $query->limit($limit);

        $orderBy = $this->getOrderBy($sort, $section);
        $query->orderBy($orderBy[0], $orderBy[1]);

        // Only return products of sections if set
        if (isset($section)) {
            $query->whereHas('sections', function($q) use($section){
                if (is_numeric($section)) {
                    $q->where('id', $section);
                } else {
                    $q->where('description', ucfirst($section));
                }
            })->orderBy('position', 'ASC')->orderByDesc('release_date');
        }

        $storeProducts = $query->orderBy('position', 'ASC')->orderByDesc('release_date')->get();
        foreach ($storeProducts as $product)  {

            //check territories
            $price = $product->getProductCurrencyToUse(session(['currency']));

            $productDataToFill = [
                'id' => $product->id,
                'artist' => $product->artist,
                'name' => $product->name,
                'title' => strlen($product->display_name) > 3 ? $product->display_name : $product->name,
                'description' => $product->description,
                'price' => $price,
                'format' => $product->type,
                'release_date' => $product->release_date,
                'sections' => $product->sections,
            ];

            if ($product->checkProductInDisabledCountries() === true
                && $product->isProductAvailableToDisplay() === true) {
                $productDataToFill['image'] = $product->getImage($this->imagesDomain);
            }

            ($product->artist !== null) ?: $productDataToFill['artist'] = $product->artist;

            $products[] = $productDataToFill;
        }
        return $products;
    }

    /**
     * @param $sort
     * @param null $section
     * @return string[]
     */
    public function getOrderBy($sort, $section = null)
    {
        switch ($sort) {
            case "az":
                return ['name', 'ASC'];
                break;
            case "za":
                return ['name', 'DESC'];
                break;
            case "low":
                return ['price', 'ASC'];
                break;
            case "high":
                return ['price', 'DESC'];
                break;
            case "old":
                return ['release_date', 'ASC'];
                break;
            case "new":
                return ['release_date', 'DESC'];
                break;
        }
        return ['id', 'ASC'];
    }

    /**
     * @return string[]
     */
    public function getGeocode()
    {
        //Return GB default for the purpose of the test
        return ['country' => 'GB'];
    }
}
