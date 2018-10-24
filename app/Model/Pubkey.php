<?php
namespace  App\Model;

use Illuminate\Database\Eloquent\Model;


class Pubkey extends  Model
{
    protected  $table = 'pubkey';

    protected  $primaryKey = 'pubkey_id';

    public $timestamps = false;

}