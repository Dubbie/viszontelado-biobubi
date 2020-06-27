<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Document
 * @package App
 * @mixin Document
 */
class Document extends Model
{
    public function download() {
        return \Storage::download($this->path, $this->name);
    }
}
