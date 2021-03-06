<?php

namespace App;

use App\Subesz\ShoprenterService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Delivery
 * @package App
 * @mixin Delivery
 */
class Delivery extends Model
{
    /** @var ShoprenterService */
    private $shoprenterApi;

    /**
     * Delivery constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->shoprenterApi = resolve('App\Subesz\ShoprenterService');
    }

    /** @var bool */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order() {
        return $this->belongsTo(Order::class);
    }

    /**
     * @param $value
     * @return Carbon
     */
    public function getDeliveredAtAttribute($value){
        return Carbon::parse($value);
    }
}
