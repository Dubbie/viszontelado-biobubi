<?php

namespace App;

use App\Subesz\ShoprenterService;
use Illuminate\Database\Eloquent\Model;

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
}
