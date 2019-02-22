<?php

namespace App\Http\Controllers\Block;
use App\Http\Commons\UniversalTools;
use Illuminate\Queue\Connectors\RedisConnector;
use Illuminate\Support\Facades\Redis;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Model\Block;
use App\Model\Transaction;
use App\Model\Pubkey;
use App\Model\Autosend;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Sunra\PhpSimple\HtmlDomParser;
use App\Http\Commons\CurlOperate;
use App\Http\Commons\RedisOperate;

class BlockController extends BaseController
{
    //GetAddressBalance
    public function GetAddressBalance(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");


        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Addr Empty!'));
        }

        $param = trim($request->input('param'));
        $addrLen = strlen($param);
        if($addrLen < 26 || $addrLen > 34){
            return json_encode(array('error'=>1,'msg'=>'Addr Format Error!'));
        }


        $curlOperate = new CurlOperate();

        $curl = $curlOperate->GetAddressBalance($param);

        $arrRes = json_decode($curl,1);

        
        if(array_key_exists('msg',$arrRes)){
            return json_encode(array('error'=>1,'msg'=>$arrRes['msg']));
        }


        $arrRes['addr'] = $param;
        $response = json_encode($arrRes);
        return $response;
    }

    //GetBlockInfo
    public function GetBlockInfo(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        //Pagination
        $page = $request->input('page',1);
        $count = 20;
        
        if($page < 1){
            $page = 1;
        }

        if($page > 5){
            $page = 5;
        }

        $num = 100;

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Addr Empty!'));
        }

        $param = trim($request->input('param'));
        if(!is_numeric($param)){
            return json_encode(array('error'=>1,'msg'=>'Block Not Num!'));
        }

        if($param < 1){
            return json_encode(array('error'=>1,'msg'=>'Block Too Small!'));
        }


        //search redis
        $redisParam = $param.'-'.$page;
        $redis = new RedisOperate();
        $redisExist = $redis->RedisExist($redisParam);
        
        if($redisExist){
            return $redis->RedisGet($redisParam);
        }else{
            $param = intval($param);

            $curlOperate = new CurlOperate();

            $curl = $curlOperate->GetBlockHash($param);

            $resBlockHash = json_decode($curl,1);

            
            if(array_key_exists('msg',$resBlockHash)){
                return json_encode(array('error'=>1,'msg'=>$resBlockHash['msg']));
            }

            $resHash = $resBlockHash["result"];
            
            $zRes = [];
            
            $blockInfo = $curlOperate->GetBlock($resHash);

            $blockInfoRes = json_decode($blockInfo,1);
            
            if(array_key_exists('msg',$blockInfoRes)){
                return json_encode(array('error'=>1,'msg'=>$blockInfoRes['msg']));
            }


            $txArray = $blockInfoRes['result']['tx'];

            //back 100 hash
            $txArray = array_slice($txArray,0,$num);
            $pageCount = ceil(count($txArray) / $count);
            $pageCount = $pageCount < 1 ? 1 : $pageCount;
            
            if ($page > $pageCount) {
                return json_encode(array('error' => 1, 'msg' => 'Page Too More!'));
            }

            $offset = $count * ($page - 1);
            
            $txArray = array_slice($txArray,$offset,$count);

            $txResArray = array();
            foreach ($txArray as $tx){
                $txInfo = $curlOperate->GetTransactionNew($tx);
                $txInfoArray = json_decode($txInfo,1);

                if(array_key_exists('msg',$txInfoArray)){
                    return json_encode(array('error'=>1,'msg'=>$txInfoArray['msg']));
                }

                $txVin = $txInfoArray['result']['vin'];

                foreach ($txVin as $txkey => $txval){
                    if (!array_key_exists('coinbase',$txval)){
                        $vinHash = $txval['txid'];

                        $voutN = $txval['vout'];
                        $txVout = $curlOperate->GetTransactionNew($vinHash);

                        $txInfoArray2 = json_decode($txVout,1);

                        if(array_key_exists('msg',$txInfoArray2)){
                            return json_encode(array('error'=>1,'msg'=>$txInfoArray2['msg']));
                        }

                        $txVoutArray = $txInfoArray2['result']['vout'];
                        foreach ($txVoutArray as $txOutVal) {
                            if(array_key_exists("addresses", $txOutVal["scriptPubKey"])){
                                if($txOutVal['n'] === $voutN){
                                    $value = $txOutVal['value'];
                                    $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                    $txInfoArray['result']['vin'][$txkey]['value'] = $value;
                                    $txInfoArray['result']['vin'][$txkey]['addr'] = $addr;
                                }
                            }

                        }

                    }
                }
                array_push($txResArray,$txInfoArray);
            }

            array_push($zRes,$blockInfoRes,$txResArray);

            $zRes = json_encode($zRes);
            $redis->RedisSet($redisParam,$zRes);
            return $zRes;
        }

    }

    //GetBlockInfoByHash
    public function GetBlockInfoByHash(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        //Pagination
        $page = $request->input('page',1);
        $count = 20;

        if($page < 1){
            $page = 1;
        }

        if($page > 5){
            $page = 5;
        }

        $num = 100;

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input BlockHash Empty!'));
        }

        $param = trim($request->input('param'));
        $len = strlen($param);
        if($len != 64){
            return json_encode(array('error'=>1,'msg'=>'Block Hash Error!'));
        }

        $redisParam = $param.'-'.$page;
        $redis = new RedisOperate();
        $redisExist = $redis->RedisExist($redisParam);
        $zRes = array();

        if($redisExist){
            return $redis->RedisGet($redisParam);
        }else{
            $curlOperate = new CurlOperate();

            $blockInfo = $curlOperate->GetBlock($param);

            $blockInfoRes = json_decode($blockInfo,1);

            if(array_key_exists('msg',$blockInfoRes)){
                return json_encode(array('error'=>1,'msg'=>$blockInfoRes['msg']));
            }

            $txArray = $blockInfoRes['result']['tx'];

            $txArray = array_slice($txArray,0,$num);

            $pageCount = ceil(count($txArray) / $count);
            $pageCount = $pageCount < 1 ? 1 : $pageCount;

            if ($page > $pageCount) {
                return json_encode(array('error' => 1, 'msg' => 'Page Too More'));
            }

            $offset = $count * ($page - 1);

            $txArray = array_slice($txArray,$offset,$count);

            $txResArray = array();
            foreach ($txArray as $tx){
                $txInfo = $curlOperate->GetTransactionNew($tx);
                $txInfoArray = json_decode($txInfo,1);
                if(array_key_exists('msg',$txInfoArray)){
                    return json_encode(array('error'=>1,'msg'=>$txInfoArray['msg']));
                }

                $txVin = $txInfoArray['result']['vin'];
                foreach ($txVin as $txkey => $txval){
                    if (!array_key_exists('coinbase',$txval)){
                        $vinHash = $txval['txid'];
                        $voutN = $txval['vout'];
                        $txVout = $curlOperate->GetTransactionNew($vinHash);
                        $txInfoArray2 = json_decode($txVout,1);

                        if(array_key_exists('msg',$txVout)){
                            return json_encode(array('error'=>1,'msg'=>$txVout['msg']));
                        }

                        $txVoutArray = $txInfoArray2['result']['vout'];
                        foreach ($txVoutArray as $txOutVal) {
                            if(array_key_exists("addresses", $txOutVal["scriptPubKey"])){
                                if($txOutVal['n'] === $voutN){
                                    $value = $txOutVal['value'];
                                    $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                    $txInfoArray['result']['vin'][$txkey]['value'] = $value;
                                    $txInfoArray['result']['vin'][$txkey]['addr'] = $addr;
                                }
                            }

                        }

                    }
                }
                array_push($txResArray,$txInfoArray);
            }

            array_push($zRes,$blockInfoRes,$txResArray);

            $zRes = json_encode($zRes);
            $redis->RedisSet($redisParam,$zRes);
            return $zRes;
        }
    }

    //GetTxInfo
    public function GetTxInfo(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))) {
            return json_encode(array('error' => 1, 'msg' => 'Input TX Empty!'));
        }

        $param = trim($request->input('param'));
        $len = strlen($param);
        if ($len != 64) {
            return json_encode(array('error' => 1, 'msg' => 'TX Format Error'));
        }

        $redisParam = $param;
        $redis = new RedisOperate();
        $redisExist = $redis->RedisExist($redisParam);

        if($redisExist){
            return $redis->RedisGet($redisParam);
        }else{
            $curlOperate = new CurlOperate();

            $txInfo = $curlOperate->GetTransactionNew($redisParam);

            $txInfoArray = json_decode($txInfo,1);

            if(array_key_exists('msg',$txInfoArray)){
                return json_encode(array('error'=>1,'msg'=>$txInfoArray['msg']));
            }

            $txResult = $txInfoArray['result'];
            //confirmations 50
            if(array_key_exists('confirmations',$txResult)){
                $confirmations = $txResult['confirmations'];
                $flagSum = 50;
                $expire = 30;

                if($confirmations >= $flagSum){
                    $txTemp = $txInfoArray['result']['vin'];
                    foreach ($txTemp as $txkey => $txval) {
                        if (!array_key_exists('coinbase', $txval)) {
                            $vinHash = $txval['txid'];
                            $voutN = $txval['vout'];
                            $txVout = $curlOperate->GetTransactionNew($vinHash);
                            $txVoutArray = json_decode($txVout, 1);

                            if(array_key_exists('msg',$txVoutArray)){
                                return json_encode(array('error'=>1,'msg'=>$txVoutArray['msg']));
                            }

                            $txVoutResArray = $txVoutArray['result']['vout'];
                            foreach ($txVoutResArray as $k=>$txOutVal) {
                                if($txOutVal['n'] === $voutN){
                                    $value = $txOutVal['value'];
                                    $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                    $txInfoArray['result']['vin'][$txkey]['value'] = $value;
                                    $txInfoArray['result']['vin'][$txkey]['addr'] = $addr;
                                }
                            }
                        }

                    }

                    $txInfo = json_encode($txInfoArray);
                    $redis->RedisSet($redisParam,$txInfo);
                    return $txInfo;
                }
                $txTemp = $txInfoArray['result']['vin'];
                foreach ($txTemp as $txkey => $txval) {
                    if (!array_key_exists('coinbase', $txval)) {
                        $vinHash = $txval['txid'];
                        $voutN = $txval['vout'];

                        $txVout = $curlOperate->GetTransactionNew($vinHash);

                        $txVoutArray = json_decode($txVout, 1);

                        if(array_key_exists('msg',$txVoutArray)){
                            return json_encode(array('error'=>1,'msg'=>$txVoutArray['msg']));
                        }

                        $txVoutResArray = $txVoutArray['result']['vout'];

                        foreach ($txVoutResArray as $k=>$txOutVal) {
                            if($txOutVal['n'] === $voutN){
                                $value = $txOutVal['value'];
                                $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                $txInfoArray['result']['vin'][$txkey]['value'] = $value;
                                $txInfoArray['result']['vin'][$txkey]['addr'] = $addr;
                            }
                        }
                    }

                }

                $txInfo = json_encode($txInfoArray);
                $redis->RedisSet($redisParam,$txInfo,$expire);
                return $txInfo;
            }else{
                $txTemp = $txInfoArray['result']['vin'];
                foreach ($txTemp as $txkey => $txval) {
                    if (!array_key_exists('coinbase', $txval)) {
                        $vinHash = $txval['txid'];
                        $voutN = $txval['vout'];

                        $txVout = $curlOperate->GetTransactionNew($vinHash);

                        $txVoutArray = json_decode($txVout, 1);

                        if(array_key_exists('msg',$txVoutArray)){
                            return json_encode(array('error'=>1,'msg'=>$txVoutArray['msg']));
                        }

                        $txVoutResArray = $txVoutArray['result']['vout'];

                        foreach ($txVoutResArray as $k=>$txOutVal) {
                            if($txOutVal['n'] === $voutN){
                                $value = $txOutVal['value'];
                                $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                $txInfoArray['result']['vin'][$txkey]['value'] = $value;
                                $txInfoArray['result']['vin'][$txkey]['addr'] = $addr;
                            }
                        }
                    }

                }

                $txInfo = json_encode($txInfoArray);
                return $txInfo;
            }
        }

    }

    //Search
    public function Search3(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1, 'msg'  =>'Input Empty!'));
        }

        $param = trim($request->input('param'));

        //blockheight
        if (is_numeric($param)){
            //Pagination
            $page = $request->input('page',1);
            $count = 20;

            if($page < 1){
                $page = 1;
            }

            if($page > 5){
                $page = 5;
            }

            $num = 100;

            if($param < 1){
                return json_encode(array('error'=>1,'msg'=>'Block Too Small!'));
            }

            //search redis
            $redisParam = $param.'-'.$page;
            $redis = new RedisOperate();
            $redisExist = $redis->RedisExist($redisParam);

            if($redisExist){
                return $redis->RedisGet($redisParam);
            }else{
                $param = intval($param);

                $curlOperate = new CurlOperate();

                $curl = $curlOperate->GetBlockHash($param);

                $resBlockHash = json_decode($curl,1);

                if(array_key_exists('msg',$resBlockHash)){
                    return json_encode(array('error'=>1,'msg'=>$resBlockHash['msg']));
                }

                $resHash = $resBlockHash["result"];

                $zRes = [];

                $blockInfo = $curlOperate->GetBlock($resHash);

                $blockInfoRes = json_decode($blockInfo,1);

                if(array_key_exists('msg',$blockInfoRes)){
                    return json_encode(array('error'=>1,'msg'=>$blockInfoRes['msg']));
                }

                $txArray = $blockInfoRes['result']['tx'];

                $txArray = array_slice($txArray,0,$num);
                $pageCount = ceil(count($txArray) / $count);
                $pageCount = $pageCount < 1 ? 1 : $pageCount;

                if ($page > $pageCount) {
                    return json_encode(array('error' => 1, 'msg' => 'Page Too More!'));
                }

                $offset = $count * ($page - 1);

                $txArray = array_slice($txArray,$offset,$count);

                $txResArray = array();
                foreach ($txArray as $tx){
                    $txInfo = $curlOperate->GetTransactionNew($tx);
                    $txInfoArray = json_decode($txInfo,1);

                    if(array_key_exists('msg',$txInfoArray)){
                        return json_encode(array('error'=>1,'msg'=>$txInfoArray['msg']));
                    }

                    $txVin = $txInfoArray['result']['vin'];

                    foreach ($txVin as $txkey => $txval){
                        if (!array_key_exists('coinbase',$txval)){
                            $vinHash = $txval['txid'];
                            $voutN = $txval['vout'];
                            $txVout = $curlOperate->GetTransactionNew($vinHash);
                            $txInfoArray2 = json_decode($txVout,1);

                            if(array_key_exists('msg',$txInfoArray2)){
                                return json_encode(array('error'=>1,'msg'=>$txInfoArray2['msg']));
                            }

                            $txVoutArray = $txInfoArray2['result']['vout'];
                            foreach ($txVoutArray as $txOutVal) {
                                if(array_key_exists("addresses", $txOutVal["scriptPubKey"])){
                                    if($txOutVal['n'] === $voutN){
                                        $value = $txOutVal['value'];
                                        $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                        $txInfoArray['result']['vin'][$txkey]['value'] = $value;
                                        $txInfoArray['result']['vin'][$txkey]['addr'] = $addr;
                                    }
                                }

                            }

                        }
                    }
                    array_push($txResArray,$txInfoArray);
                }

                array_push($zRes,$blockInfoRes,$txResArray);

                $zRes = json_encode($zRes);
                $redis->RedisSet($redisParam,$zRes);
                return $zRes;
            }
        }


        //tx hash
        if (strlen($param) == 64 ) {
            $redisParam = $param;
            $redis = new RedisOperate();
            $redisExist = $redis->RedisExist($redisParam);

            if ($redisExist) {
                return $redis->RedisGet($redisParam);
            } else {
                $curlOperate = new CurlOperate();
                $txInfo = $curlOperate->SearchHash($redisParam);
                $txInfoArray = json_decode($txInfo, 1);

                if (array_key_exists('msg', $txInfoArray)) {
                    return json_encode(array('error' => 1, 'msg' => $txInfoArray['msg']));
                }

                $backType = $txInfoArray['type'];

                if($backType == 'TXHash'){
                    $txResultTemp = $txInfoArray['data'];
                    $txResultArray = json_decode($txResultTemp,1);
                    $txResult = $txResultArray['result'];

                    if (array_key_exists('confirmations', $txResult)) {
                        $confirmations = $txResult['confirmations'];
                        $flagSum = 50;
                        $expire = 30;

                        if ($confirmations >= $flagSum) {
                            $txTemp = $txResultArray['result']['vin'];
                            foreach ($txTemp as $txkey => $txval) {
                                if (!array_key_exists('coinbase', $txval)) {
                                    $vinHash = $txval['txid'];
                                    $voutN = $txval['vout'];
                                    $txVout = $curlOperate->GetTransactionNew($vinHash);
                                    $txVoutArray = json_decode($txVout, 1);

                                    if (array_key_exists('msg', $txVoutArray)) {
                                        return json_encode(array('error' => 1, 'msg' => $txVoutArray['msg']));
                                    }
                                    $txVoutResArray = $txVoutArray['result']['vout'];
                                    foreach ($txVoutResArray as $k => $txOutVal) {
                                        if ($txOutVal['n'] === $voutN) {
                                            $value = $txOutVal['value'];
                                            $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                            $txResultArray['result']['vin'][$txkey]['value'] = $value;
                                            $txResultArray['result']['vin'][$txkey]['addr'] = $addr;
                                        }
                                    }
                                }

                            }

                            $txInfo = json_encode($txResultArray);
                            $redis->RedisSet($redisParam, $txInfo);
                            return $txInfo;
                        }

                        $txTemp = $txResultArray['result']['vin'];
                        foreach ($txTemp as $txkey => $txval) {
                            if (!array_key_exists('coinbase', $txval)) {
                                $vinHash = $txval['txid'];
                                $voutN = $txval['vout'];
                                $txVout = $curlOperate->GetTransactionNew($vinHash);
                                $txVoutArray = json_decode($txVout, 1);

                                if (array_key_exists('msg', $txVoutArray)) {
                                    return json_encode(array('error' => 1, 'msg' => $txVoutArray['msg']));
                                }

                                $txVoutResArray = $txVoutArray['result']['vout'];
                                foreach ($txVoutResArray as $k => $txOutVal) {
                                    if ($txOutVal['n'] === $voutN) {
                                        $value = $txOutVal['value'];
                                        $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                        $txResultArray['result']['vin'][$txkey]['value'] = $value;
                                        $txResultArray['result']['vin'][$txkey]['addr'] = $addr;
                                    }
                                }
                            }
                        }
                        $txInfo = json_encode($txResultArray);
                        $redis->RedisSet($redisParam, $txInfo, $expire);
                        return $txInfo;
                    }
                }
            }


                //blockhash
                if($backType == 'BlockHash'){
                $page = $request->input('page',1);
                $count = 20;

                if($page < 1){
                    $page = 1;
                }

                if($page > 5){
                    $page = 5;
                }

                $num = 100;

                if (empty($request->input('param'))){
                    return json_encode(array('error'=>1,'msg'=>'Input BlockHash Empty!'));
                }

                $param = trim($request->input('param'));
                $len = strlen($param);
                if($len != 64){
                    return json_encode(array('error'=>1,'msg'=>'Block Hash Error!'));
                }

                $param = json_decode($txInfoArray['data'],1)['result']['hash'];

                $redisParam = $param.'-'.$page;
                $redisExist = $redis->RedisExist($redisParam);
                $zRes = array();

                if($redisExist){
                    return $redis->RedisGet($redisParam);
                }else{
                    $blockInfo = $curlOperate->GetBlock($param);
                    $blockInfoRes = json_decode($blockInfo,1);

                    if(array_key_exists('msg',$blockInfoRes)){
                        return json_encode(array('error'=>1,'msg'=>$blockInfoRes['msg']));
                    }

                    $txArray = $blockInfoRes['result']['tx'];

                    $txArray = array_slice($txArray,0,$num);

                    $pageCount = ceil(count($txArray) / $count);
                    $pageCount = $pageCount < 1 ? 1 : $pageCount;

                    if ($page > $pageCount) {
                        return json_encode(array('error' => 1, 'msg' => 'Page Too More'));
                    }

                    $offset = $count * ($page - 1);
                    $txArray = array_slice($txArray,$offset,$count);
                    $txResArray = array();
                    foreach ($txArray as $tx){
                        $txInfo = $curlOperate->GetTransactionNew($tx);
                        $txInfoArray = json_decode($txInfo,1);
                        if(array_key_exists('msg',$txInfoArray)){
                            return json_encode(array('error'=>1,'msg'=>$txInfoArray['msg']));
                        }

                        $txVin = $txInfoArray['result']['vin'];
                        foreach ($txVin as $txkey => $txval){
                            if (!array_key_exists('coinbase',$txval)){
                                $vinHash = $txval['txid'];
                                $voutN = $txval['vout'];
                                $txVout = $curlOperate->GetTransactionNew($vinHash);
                                $txInfoArray2 = json_decode($txVout,1);

                                if(array_key_exists('msg',$txInfoArray2)){
                                    return json_encode(array('error'=>1,'msg'=>$txVout['msg']));
                                }

                                $txVoutArray = $txInfoArray2['result']['vout'];
                                foreach ($txVoutArray as $txOutVal) {
                                    if(array_key_exists("addresses", $txOutVal["scriptPubKey"])){
                                        if($txOutVal['n'] === $voutN){
                                            $value = $txOutVal['value'];
                                            $addr = $txOutVal["scriptPubKey"]["addresses"][0];
                                            $txInfoArray['result']['vin'][$txkey]['value'] = $value;
                                            $txInfoArray['result']['vin'][$txkey]['addr'] = $addr;
                                        }
                                    }

                                }

                            }
                        }
                        array_push($txResArray,$txInfoArray);
                    }

                    array_push($zRes,$blockInfoRes,$txResArray);

                    $zRes = json_encode($zRes);
                    $redis->RedisSet($redisParam,$zRes);
                    return $zRes;
                }
            }

        }


        //addr
        if(strlen($param) >= 26 && strlen($param) <= 34){
            $curlOperate = new CurlOperate();
            $curl = $curlOperate->GetAddressBalance($param);
            $arrRes = json_decode($curl,1);

            if(array_key_exists('msg',$arrRes)){
                return json_encode(array('error'=>1,'msg'=>$arrRes['msg']));
            }

            $arrRes['addr'] = $param;
            $response = json_encode($arrRes);
            return $response;
        }

        //serach name by redis
        $redis = new RedisOperate();
        $lbtcname = 'lbtc-'.$param;
        $address = $redis->RedisGet($lbtcname);

        if($address){
            $curlOperate = new CurlOperate();
            $curl = $curlOperate->GetAddressBalance($address);
            $arrRes = json_decode($curl,1);

            if(array_key_exists('msg',$arrRes)){
                return json_encode(array('error'=>1,'msg'=>$arrRes['msg']));
            }

            $arrRes['addr'] = $address;
            $response = json_encode($arrRes);
            return $response;
        }
        return json_encode(array('error'=>1,'msg'=>'Input Error'));
    }

    //index
    public function Index3()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();
        $indexInfo = $redis->RedisGet('index');
        return $indexInfo;
    }

    //setindex Timed task
    public function SetIndex()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $height = $curlOperate->GetBlockCount();
        $heightArray = json_decode($height, 1);

        if(array_key_exists('msg',$heightArray)){
            return json_encode(array('error'=>1,'msg'=>$heightArray['msg']));
        }

        $height = intval($heightArray['result']);

        $count = 20;

        $heightArray = array();
        for ($i = $height; $i > $height - $count; $i--) {
            $blockHash = $curlOperate->GetBlockHash($i);
            $blockHashArray = json_decode($blockHash, 1);

            if(array_key_exists('msg',$blockHashArray)){
                return json_encode(array('error'=>1,'msg'=>$blockHashArray['msg']));
            }

            $blockHash = $blockHashArray['result'];
            $blockInfo = $curlOperate->GetBlock($blockHash);
            $blockInfoArray = json_decode($blockInfo, 1);

            if(array_key_exists('msg',$blockInfoArray)){
                return json_encode(array('error'=>1,'msg'=>$blockInfoArray['msg']));
            }
            array_push($heightArray,$blockInfoArray);
        }

        $indexStr =  json_encode($heightArray);
        $redis = new RedisOperate();
        $redis->RedisSet('index',$indexStr);
    }

    //listdelegates Timed task
    public function listwitnesses()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $listDelegates = $curlOperate->ListDelegates();
        $listDelegatesArray = json_decode($listDelegates, 1);

        if(array_key_exists('msg',$listDelegatesArray)){
            return json_encode(array('error'=>1,'msg'=>$listDelegatesArray['msg']));
        }

        $redis = new RedisOperate();
        $resListDelegates = $listDelegatesArray['result'];

        foreach ($resListDelegates as $delegatesVal){
            $redis->RedisSet($delegatesVal['address'],$delegatesVal['name']);
            $redis->RedisSet('lbtc-'.$delegatesVal['name'],$delegatesVal['address']);
        }
    }

    //listreceivedvotes name
    public function getvotersbywitness(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Empty!'));
        }

        $param = trim($request->input('param'));
        $addrLen = strlen($param);
        if($addrLen < 26 || $addrLen > 34){
            $resParam = $param;
        }else{
            $redis = new RedisOperate();
            $resParam = $redis->RedisGet($param);
            if(empty($resParam)){
                return json_encode(array('error'=>1,'msg'=>'Input information Error!'));
            }
        }

        $curlOperate = new CurlOperate();
        $listReceivedVotes = $curlOperate->ListReceivedVotes($resParam);
        $listReceivedVotesArray = json_decode($listReceivedVotes,1);

        if(array_key_exists('msg',$listReceivedVotesArray)){
            return json_encode(array('error'=>1,'msg'=>$listReceivedVotesArray['msg']));
        }

        return $listReceivedVotes;
    }

    //listvoteddelegates
    public function getvotebyaddress(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Empty!'));
        }

        $param = trim($request->input('param'));
        $addrLen = strlen($param);
        if($addrLen < 26 || $addrLen > 34){
            $redis = new RedisOperate();
            $resParam = $redis->RedisGet('lbtc-'.$param);
            if(empty($resParam)){
                return json_encode(array('error'=>1,'msg'=>'Input information Error!'));
            }
        }else{
            $resParam = $param;
        }

        $curlOperate = new CurlOperate();
        $response = $curlOperate->ListVotedDelegates($resParam);
        $responseArray = json_decode($response,1);

        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }

        return $response;
    }

    //getdelegatevotes name
    public function getwitnessshare(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Empty!'));
        }

        $param = $request->input('param');
        $curlOperate = new CurlOperate();
        $response = $curlOperate->GetDelegateVotes($param);
        $responseArray = json_decode($response,1);

        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }
        return $response;
    }

    //map addr=>name Timed task
    public function SetRedis()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $response = $curlOperate->ListDelegates();
        $responseArray = json_decode($response,1);

        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }

        $delegatesArray = $responseArray['result'];

        $redis = new RedisOperate();
        foreach ($delegatesArray as $key => $val){
            $response = $curlOperate->GetDelegateVotes($val['name']);
            $responseArray = json_decode($response,1);

            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }
            $delegatesArray[$key]['count'] = $responseArray['result'];
        }

        if($delegatesArray){
            foreach ($delegatesArray as $val){
                $result[] = $val;
            }

            array_multisort(array_column($result,'count'),SORT_DESC,$result);

            $delegatesJson = json_encode($result);
            $redis->RedisSet('nodesort',$delegatesJson);
        }
    }

    //getactive nodesort
    public function GetActive()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();
        $redisExist = $redis->RedisExist('nodesort');

        if($redisExist){
            $nodeSort = $redis->RedisGet('nodesort');
            return $nodeSort;
        }
    }

    //Getlistdelegates
    public function Getlistdelegates()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();
        $redisExist = $redis->RedisExist('nodesort');
        if($redisExist){
            $redisJson = $redis->RedisGet('nodesort');
            return $redisJson;
        }
        return json_encode(array('error'=>1,'msg'=>'Key NoExist'));
    }

    //SetBlockCount Timed task
    public function SetBlockCount()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $response = $curlOperate->GetBlockCount();
        $responseArray = json_decode($response,1);

        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }

        $blockInt = $responseArray['result'];
        $blcokHashJson = $curlOperate->GetBlockHash($blockInt);
        $blcokHashArray = json_decode($blcokHashJson,1);


        if(array_key_exists('msg',$blcokHashArray)){
            return json_encode(array('error'=>1,'msg'=>$blcokHashArray['msg']));
        }

        $blockHash = $blcokHashArray['result'];
        $blockInfo = json_encode(array('blockcount' => $blockInt,'blockhash' =>$blockHash));
        $redis = new RedisOperate();
        $redis->RedisSet('blockcount',$blockInfo);
    }

    //GetBlockCount
    public function GetBlockCount()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();
        $redisBlockCount = $redis->RedisGet('blockcount');

        return $redisBlockCount;
    }


    //GetTokenInfo
    public function GetTokenInfo(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $param = trim($request->input('param'));

        if ($param){
            $paramLen = strlen($param);
            if($paramLen < 26 || $paramLen > 34){
                return json_encode(array('error'=>1,'msg'=>'Addr Format Error!'));
            }

            $curlOperate = new CurlOperate();

            $response = $curlOperate->GetTokenInfo($param);

            $responseArray = json_decode($response,1);

            //判断请求是否发生错误
            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];

            return json_encode(array('error'=>0,'msg'=>$res));

        }else{
            $curlOperate = new CurlOperate();

            $response = $curlOperate->GetTokenInfo();

            $responseArray = json_decode($response,1);

            //判断请求是否发生错误
            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];

            return json_encode(array('error'=>0,'msg'=>$res));
        }

    }


    //GetTokenBalance
    public function GetTokenBalance(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $userAddr = trim($request->input('uAddr'));
        $tokenAddr = trim($request->input('tAddr'));

        if (empty($userAddr)){
            return json_encode(array('error'=>1,'msg'=>'userAddress is empty!'));
        }

        $paramLen = strlen($userAddr);
        if($paramLen < 26 || $paramLen > 34){
            return json_encode(array('error'=>1,'msg'=>'userAddress Format Error!'));
        }

        //如果tokenaddr存在
        if ($tokenAddr){
            $paramLen = strlen($tokenAddr);
            if($paramLen < 26 || $paramLen > 34){
                return json_encode(array('error'=>1,'msg'=>'tokenAddress Format Error!'));
            }

            $curlOperate = new CurlOperate();
            $response = $curlOperate->GetTokenBalance($userAddr,$tokenAddr);

            $responseArray = json_decode($response,1);

            //判断请求是否发生错误
            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];

            return json_encode(array('error'=>0,'msg'=>$res));
        }else{
            $curlOperate = new CurlOperate();
            $response = $curlOperate->GetTokenBalance($userAddr);

            $responseArray = json_decode($response,1);

            //判断请求是否发生错误
            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];

            return json_encode(array('error'=>0,'msg'=>$res));
        }
    }


    //SetOwenToToken
    public function SetOwenToToken(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $resTokenInfo = $this->GetTokenInfo($request);

        $resArray = json_decode($resTokenInfo,1);

        if($resArray["error"] != 0){
            $msg =$resArray["msg"];
            return json_encode(["error"=>1,"msg"=>$msg]);
        }

        $resMsg = $resArray["msg"];

        $redis = new RedisOperate();

        foreach ($resMsg as $v){
            $ownerAddress = $v["ownerAddress"];
            $tokenSymbol = $v["tokenSymbol"];

            $redis->RedisRPush('ownerAddr-'.$ownerAddress,$tokenSymbol);
        }

    }


    //SetTokenToOwen
    public function SetTokenToOwen(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $resTokenInfo = $this->GetTokenInfo($request);

        $resArray = json_decode($resTokenInfo,1);

        if($resArray["error"] != 0){
            $msg =$resArray["msg"];
            return json_encode(["error"=>1,"msg"=>$msg]);
        }

        $resMsg = $resArray["msg"];

        $redis = new RedisOperate();

        foreach ($resMsg as $v){
            $ownerAddress = $v["ownerAddress"];
            $tokenSymbol = $v["tokenSymbol"];

            $redis->RedisSet('token-'.$tokenSymbol,$ownerAddress);
        }
    }

    //GetTokenOrOwner
    public function GetTokenOrOwner(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();

        $ownPreFix = 'ownerAddr-';
        $tokenPreFix = 'token-';

        $param = $request->input('param');

        if (empty($param)){
            return json_encode(["error"=>1,"msg" => "param is empty!"]);
        }

        $ownExpire = $redis->RedisExist($ownPreFix.$param);
        $tokenExpire = $redis->RedisExist($tokenPreFix.$param);

        if ($ownExpire){
            $res = $redis->RedisLRange($ownPreFix.$param);
            return json_encode(["error"=>0,"msg" => array_unique($res)]);
        }

        if ($tokenExpire){
            $res = $redis->RedisGet($tokenPreFix.$param);
            return json_encode(["error"=>0,"msg" => $res]);
        }

        return json_encode(["error"=>1,"msg" => "ownerAddr and token is expire!"]);
    }


    //GetAddressName
    public function GetAddressName(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $param = trim($request->input('param'));

        $paramLen = strlen($param);
        if($paramLen < 26 || $paramLen > 34){
            return json_encode(array('error'=>1,'msg'=>'Addr Format Error!'));
        }

        $curlOperate = new CurlOperate();

        $response = $curlOperate->GetAddressName($param);

        $responseArray = json_decode($response,1);

        //判断请求是否发生错误
        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }

        $res = $responseArray['result'];

        if(empty($res)){
            return json_encode(array('error'=>1,'msg'=>'Address has no registered name'));
        }

        return json_encode(array('error'=>0,'msg'=>$res));
    }


    //GetNameAddress
    public function GetNameAddress(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $param = trim($request->input('param'));

        $curlOperate = new CurlOperate();

        $response = $curlOperate->GetNameAddress($param);

        $responseArray = json_decode($response,1);

        //判断请求是否发生错误
        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }

        $res = $responseArray['result'];

        if(empty($res)){
            return json_encode(array('error'=>1,'msg'=>'This name does not exist'));
        }

        return json_encode(array('error'=>0,'msg'=>$res));
    }


    //GetAddressTokenTxids
    public function GetAddressTokenTxids(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $addr = trim($request->input('addr'));
        if (empty($addr)){
            return json_encode(array('error'=>1,'msg'=>'Addr is empty!'));
        }

        $addrLen = strlen($addr);
        if($addrLen < 26 || $addrLen > 34){
            return json_encode(array('error'=>1,'msg'=>'Addr Format Error!'));
        }


        $startBlock = trim($request->input('start'));
        if (empty($startBlock)){
            $startBlock = '0';
        }

        if (!is_numeric($startBlock)){
            return json_encode(array('error'=>1,'msg'=>'StartBlock is not Num!'));
        }

        if ($startBlock < 0){
            return json_encode(array('error'=>1,'msg'=>'StartBlock must >= 0!'));
        }

        $addrToken = trim($request->input('addrtoken'));

        if ($addrToken){
            $addrTokenLen = strlen($addrToken);
            if($addrTokenLen < 26 || $addrTokenLen > 34){
                return json_encode(array('error'=>1,'msg'=>'AddrTokenLen Format Error!'));
            }
        }

        $curlOperate = new CurlOperate();

        $response = $curlOperate->Getaddresstokentxids($addr,$startBlock,$addrToken);

        $responseArray = json_decode($response,1);

        //判断请求是否发生错误
        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }

        $res = $responseArray['result'];

        if(empty($res)){
            return json_encode(array('error'=>1,'msg'=>'This name does not exist'));
        }

        return json_encode(array('error'=>0,'msg'=>$res));
    }


    //test
    public function test()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        echo "just test";
    }
}
