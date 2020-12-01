<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Liga extends Model
{
    public function product() {
        return $this->hasMany(Product::class, 'liga_id', 'id');
    }
}
