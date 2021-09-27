<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\String_;

class StoreProduct extends Model
{
    use HasFactory;

    public $table = 'store_products';
    /**
     * @var mixed
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(
            Section::class,
            'store_products_section',
            'store_product_id',
            'section_id',
            'id',
            'id'
        )
            ->withPivot('position')
            ->orderBy('position', 'ASC');
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id', 'id');
    }

    /**
     * @param $imgDomain
     * @return string
     */
    public function getImage($imgDomain): string
    {
        if (strlen($this->image_format) > 2) {
            return $imgDomain."/$this->id.".$this->image_format;
        }
        return $imgDomain."noimage.jpg";
    }

    /**
     * @param $product
     * @param $sessionCurrency
     * @return mixed
     */
    public function getProductCurrencyToUse($sessionCurrency)
    {
        switch ($sessionCurrency) {
            case "USD":
                $price = $this->dollar_price;
                break;
            case "EUR":
                $price = $this->euro_price;
                break;
            default:
                $price = $this->price;
                break;
        }
        return $price;
    }

    /**
     * @return bool
     */
    public function isProductAvailableToDisplay(): bool
    {
        if ($this->launch_date !== "0000-00-00 00:00:00" && !isset($_SESSION['preview_mode'])) {
            $launch = strtotime($this->launch_date);
            if ($launch > time()) {
                return false;
            }
        }

        if ($this->remove_date !== "0000-00-00 00:00:00") {
            $remove = strtotime($this->remove_date);
            if ($remove < time()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function checkProductInDisabledCountries(): bool
    {
        if ($this->disabled_countries !== '') {
            $countries = explode(',', $this->disabled_countries);
            $geocode = $this->getGeocode();
            $country_code = $geocode['country'];

            if (in_array($country_code, $countries, true)) {
                return false;
            }
        }
        return true;
    }
}
