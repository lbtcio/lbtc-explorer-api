<?php
namespace  App\Model;

use Illuminate\Database\Eloquent\Model;


class LaTest extends  Model
{
    protected  $table = 'Latest';

    protected  $primaryKey = 'id';

    public $timestamps = false;

}