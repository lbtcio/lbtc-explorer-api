<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});


$app->group(['namespace' => 'App\Http\Controllers\Block'], function() use ($app)
{
    //Block
    $app->get('listwitnesses', 'BlockController@listwitnesses');

    $app->get('getvotersbywitness', 'BlockController@getvotersbywitness');

    $app->get('getvotebyaddress', 'BlockController@getvotebyaddress');

    $app->get('getwitnessshare', 'BlockController@getwitnessshare');

    $app->get('setredis', 'BlockController@SetRedis');

    $app->get('getactive', 'BlockController@GetActive');

    $app->get('getstandby', 'BlockController@GetStandby');

    $app->get('getlistdelegates', 'BlockController@Getlistdelegates');

    $app->get('search3', 'BlockController@Search3');

    $app->get('getaddressbalance', 'BlockController@GetAddressBalance');

    $app->get('getblockinfo', 'BlockController@GetBlockInfo');

    $app->get('gettxinfo', 'BlockController@GetTxInfo');

    $app->get('index3', 'BlockController@Index3');

    $app->get('setindex', 'BlockController@SetIndex');

    $app->get('getblockbyhash', 'BlockController@GetBlockInfoByHash');

    $app->get('totallbtc', 'BlockController@TotalLbtc');

    $app->get('totallbtcforcap', 'BlockController@TotalLbtcForCap');

    $app->get('gettxbyaddr', 'BlockController@GetTxByAddr');

    $app->get('test', 'BlockController@Test');

    $app->get('newsetblockcount', 'BlockController@SetBlockCount');

    $app->get('getblockcount', 'BlockController@GetBlockCount');

});

$app->group(['namespace' => 'App\Http\Controllers\Pool'], function() use ($app)
{
    //GetNovotedByAddr
    $app->get('getnovotedbyaddr','PoolController@GetNovotedByAddr');

    //setnodestatus
    $app->get('setnodestatus','PoolController@SetNodeStatus');

    //GetNodeStatus
    $app->get('getnodestatus','PoolController@GetNodeStatus');

    //SetBlockCount
    $app->get('setblockcount','PoolController@SetBlockCount');


    /**
     * Version2
     */
    //NewSetNode
    $app->get('v2/newsetnode','PoolController@NewSetNode');

    //NewGetNode
    $app->get('v2/newgetnode','PoolController@NewGetNode');

    //GetTxByAddr
    $app->get('v2/gettxbyaddr','PoolController@GetTxByAddr');

    //LbtcRichList
    $app->get('v2/lbtcrichlist','PoolController@LbtcRichList');

    //GetLbtcRichList
    $app->get('v2/getlbtcrichlist','PoolController@GetLbtcRichList');

    //LbtcRichList
    $app->get('v2/lbtcrichpre','PoolController@LbtcRichPre');

    //GetLbtcRichList
    $app->get('v2/getlbtcrichpre','PoolController@GetLbtcRichPre');

    //ListCommittees
    $app->get('v2/listcommittees','PoolController@ListCommittees');

    //GetListCommittees
    $app->get('v2/getlistcommittees','PoolController@GetListCommittees');

    //GetListCommitteeVotes
    $app->get('v2/getlistcommitteevotes','PoolController@GetListCommitteeVotes');

    //GetListVotedCommittee
    $app->get('v2/getlistvotedcommittee','PoolController@GetListVotedCommittee');

    //SetBillsInfo
    $app->get('v2/setbillsinfo','PoolController@SetBillsInfo');

    //GetBillsInfo
    $app->get('v2/getbillsinfo','PoolController@GetBillsInfo');

    //VoterBillsByAddr
    $app->get('v2/voterbillsbyaddr','PoolController@VoterBillsByAddr');

    //GetListCommitteeBills
    $app->get('v2/getlistcommitteebills','PoolController@GetListCommitteeBills');

});
