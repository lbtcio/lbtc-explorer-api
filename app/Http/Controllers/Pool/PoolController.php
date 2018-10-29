<?php

namespace App\Http\Controllers\Pool;
use App\Http\Commons\Bitcoind;
use App\Http\Commons\CurlOperate;
use App\Http\Commons\RedisOperate;
use App\Http\Commons\test;
use App\Http\Commons\UniversalTools;
use App\Model\Lbtc;
use App\Model\Node;
use App\Model\PoolUser;
use App\Model\PoolVer;
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
use App\Http\Commons\RedislOperate;

class PoolController extends BaseController
{
    //setblockcount for node status Timed task
    public function SetBlockCount()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $redis = new RedisOperate();

        $response = $curlOperate->GetBlockCount();
        $blockCount = json_decode($response,1);

        if(array_key_exists('msg',$blockCount)){
            return json_encode(array('error'=>1,'msg'=>$blockCount['msg']));
        }

        //blockheight
        $blockInt = $blockCount['result'];
        $blockHash = $curlOperate->GetBlockHash($blockInt);
        $blockHashArray = json_decode($blockHash, 1);

        if (array_key_exists('msg', $blockHashArray)) {
            return json_encode(array('error' => 1, 'msg' => $blockHashArray['msg']));
        }

        $blcokHash = $blockHashArray['result'];

        $blockInfo = $curlOperate->GetBlock($blcokHash);
        $blockInfoArray = json_decode($blockInfo, 1);

        if (array_key_exists('msg', $blockInfoArray)) {
            return json_encode(array('error' => 1, 'msg' => $blockInfoArray['msg']));
        }

        $blockInfo = $blockInfoArray['result'];
        $tx = $blockInfo['tx'][0];

        $txs = $curlOperate->GetTransactionNew($tx);
        $txArray = json_decode($txs, 1);

        if (array_key_exists('msg', $txArray)) {
            return json_encode(array('error' => 1, 'msg' => $txArray['msg']));
        }

        $nodeDelegates = $txArray['result']['vout'][1];

        if(array_key_exists('type', $nodeDelegates) && $nodeDelegates['type'] == 'CoinbaseDelegateInfo'){
            $redis->RedisSet('blockcount2',$blockInt);
            $redis->RedisDel('nodedelegetsnew');
        }
    }

    //GetTxByAddr
    public function GetTxByAddr(Request $request)
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

        $limit = trim($request->input('limit'));
        $sBlock = trim($request->input('sBlock'));
        $eBlock = trim($request->input('eBlock'));

        $curlOperate = new CurlOperate();
        $redis = new RedisOperate();

        $timeOut = 15;
        $redisParam = 'tx-'.$param;
        $redisTx = $redis->RedisGet($redisParam);

        if($redisTx){
            $txArray = json_decode($redisTx,1);
            return json_encode(array('error'=>0,'msg'=>$txArray));
        }else{
            $response = $curlOperate->GetTxByAddr($param,$limit,$sBlock,$eBlock);
            $txRes = json_decode($response,1);

            if(array_key_exists('msg',$txRes)){
                return json_encode(array('error'=>1,'msg'=>$txRes['msg']));
            }

            $txArray = $txRes['result'];
            if(empty($txArray)){
                return json_encode(array('error'=>1,'msg'=>'Transaction information can not be found!'));
            }

            $txJson = json_encode($txArray);
            $redis->RedisSet($redisParam,$txJson,$timeOut);
            return json_encode(array('error'=>0,'msg'=>$txArray));
        }
    }

    //NewSetNode
    public function NewSetNode()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $redis = new RedisOperate();

        //restart redis:del blockcountnew
        $response = $curlOperate->GetBlockCount();
        $blockCount = json_decode($response,1);

        if(array_key_exists('msg',$blockCount)){
            return json_encode(array('error'=>1,'msg'=>$blockCount['msg']));
        }

        $blockInt = $blockCount['result'];
        $blockRedis = $redis->RedisGet('blockcountnew2');

        //blockcount increase
        if($blockRedis){
            if($blockInt > $blockRedis){
                $redis->RedisSet('blockcountnew2',$blockInt);
                $blockInt = intval($blockInt);
                $blockHash = $curlOperate->GetBlockHash($blockInt);
                $blockHashArray = json_decode($blockHash, 1);

                if (array_key_exists('msg', $blockHashArray)) {
                    return json_encode(array('error' => 1, 'msg' => $blockHashArray['msg']));
                }

                $blcokHash = $blockHashArray['result'];

                $blockInfo = $curlOperate->GetBlock($blcokHash);

                $blockInfoArray = json_decode($blockInfo, 1);

                if (array_key_exists('msg', $blockInfoArray)) {
                    return json_encode(array('error' => 1, 'msg' => $blockInfoArray['msg']));
                }

                $blockInfo = $blockInfoArray['result'];
                $tx = $blockInfo['tx'][0];

                $txs = $curlOperate->GetTransactionNew($tx);
                $txArray = json_decode($txs, 1);

                if (array_key_exists('msg', $txArray)) {
                    return json_encode(array('error' => 1, 'msg' => $txArray['msg']));
                }

                $nodeDelegates = $txArray['result']['vout'][1];
                $coinbaseAddr = $txArray['result']['vout'][0]["scriptPubKey"]["addresses"][0];

                if (array_key_exists('type', $nodeDelegates) && $nodeDelegates['type'] == 'CoinbaseDelegateInfo') {
                    $nodeDelegatesArray = $nodeDelegates['delegates'];

                    $coinbaseKey = array_keys($nodeDelegatesArray,$coinbaseAddr)[0];

                    $res = $redis->RedisHGetAll('1');

                    if($res){
                        $tempArray = unserialize($redis->RedisGet('nodetemp2'));
                        $addr = $redis->RedisGet('nodenow2');
                        $number = $tempArray[$addr];
                        $arrayCount = count($nodeDelegatesArray);

                        if($number < $arrayCount){
                            for($i = $number+1;$i<=$arrayCount;$i++){
                                $iSum = $redis->RedisHGet($i,'sum');
                                $redis->RedisHSet($i,'status',-1);
                                $redis->RedisHSet($i,'sum',$iSum+1);
                            }
                        }

                        $redisArray = array();

                        foreach ($nodeDelegatesArray as $nKey => $nVal){
                            $redisKey = $nKey + 1;
                            $redisArray[$nVal] = $redisKey;
                            $resTemp = $redis->RedisHVals($redisKey);
                            $addrTemp = $resTemp[0];

                            $res1 = DB::table('nodeinfosave')
                                ->select('s_id')
                                ->where('s_addr', $addrTemp)
                                ->get();

                            if($res1){
                                $res1 = $res1[0];
                                $s_id = $res1->s_id;
                                DB::table('nodeinfosave')
                                    ->where('s_id', $s_id)
                                    ->update(['s_status' => $resTemp[1],'s_count' => $resTemp[3],'s_sum' => $resTemp[4]]);
                            }else{
                                DB::table('nodeinfosave')
                                    ->insert(['s_addr' => $addrTemp,'s_status' => $resTemp[1],'s_count' => $resTemp[3],'s_sum' => $resTemp[4]]);
                            }

                            $redis->RedisDel($redisKey);

                            $res2 = DB::table('nodeinfosave')
                                ->select('s_addr','s_status','s_count','s_sum')
                                ->where('s_addr', $nVal)
                                ->get();

                            if($res2){
                                $res2 = $res2[0];
                                if($nKey < $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',-1);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',$res2->s_count);
                                    $redis->RedisHSet($redisKey,'sum',$res2->s_sum+1);
                                }elseif ($nKey == $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',1);
                                    $redis->RedisHSet($redisKey,'now',1);
                                    $redis->RedisHSet($redisKey,'count',$res2->s_count+1);
                                    $redis->RedisHSet($redisKey,'sum',$res2->s_sum+1);
                                }else{
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',$res2->s_status);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',$res2->s_count);
                                    $redis->RedisHSet($redisKey,'sum',$res2->s_sum);
                                }
                            }else{
                                if($nKey < $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',-1);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',0);
                                    $redis->RedisHSet($redisKey,'sum',1);
                                }elseif ($nKey == $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',1);
                                    $redis->RedisHSet($redisKey,'now',1);
                                    $redis->RedisHSet($redisKey,'count',1);
                                    $redis->RedisHSet($redisKey,'sum',1);
                                }else{
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',0);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',0);
                                    $redis->RedisHSet($redisKey,'sum',0);
                                }
                            }
                        }
                        $redis->RedisSet('nodetemp2',serialize($redisArray));
                        $redis->RedisSet('nodenow2', $coinbaseAddr);
                    }else{
                        $redisArray = array();

                        $coinbaseKey = array_keys($nodeDelegatesArray,$coinbaseAddr)[0];

                        foreach ($nodeDelegatesArray as $nKey => $nVal){
                            $redisKey = $nKey + 1;
                            $redisArray[$nVal] = $redisKey;

                            $res2 = DB::table('nodeinfosave')
                                ->select('s_addr','s_status','s_count','s_sum')
                                ->where('s_addr', $nVal)
                                ->get();

                            if($res2){
                                $res2 = $res2[0];
                                if($nKey < $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',-1);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',$res2->s_count);
                                    $redis->RedisHSet($redisKey,'sum',$res2->s_sum+1);
                                }elseif ($nKey == $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',1);
                                    $redis->RedisHSet($redisKey,'now',1);
                                    $redis->RedisHSet($redisKey,'count',$res2->s_count+1);
                                    $redis->RedisHSet($redisKey,'sum',$res2->s_sum+1);
                                }else{
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',$res2->s_status);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',$res2->s_count);
                                    $redis->RedisHSet($redisKey,'sum',$res2->s_sum);
                                }
                            }else{
                                if($nKey < $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',-1);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',0);
                                    $redis->RedisHSet($redisKey,'sum',1);
                                }elseif ($nKey == $coinbaseKey){
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',1);
                                    $redis->RedisHSet($redisKey,'now',1);
                                    $redis->RedisHSet($redisKey,'count',1);
                                    $redis->RedisHSet($redisKey,'sum',1);
                                }else{
                                    $redis->RedisHSet($redisKey,'addr',$nVal);
                                    $redis->RedisHSet($redisKey,'status',0);
                                    $redis->RedisHSet($redisKey,'now',0);
                                    $redis->RedisHSet($redisKey,'count',0);
                                    $redis->RedisHSet($redisKey,'sum',0);
                                }
                            }
                        }
                        $redis->RedisSet('nodetemp2',serialize($redisArray));
                        $redis->RedisSet('nodenow2', $coinbaseAddr);
                    }

                }
                else {
                    $tempArray = unserialize($redis->RedisGet('nodetemp2'));
                    $addr1 = $redis->RedisGet('nodenow2');

                    $redis->RedisSet('nodenow2',$coinbaseAddr);

                    $first = $tempArray[$addr1];
                    $second = $tempArray[$coinbaseAddr];
                    $flag = $second - $first;

                    $count = $redis->RedisHGet($second,'count');
                    $sum = $redis->RedisHGet($second,'sum');

                    if($flag == 1){
                        $redis->RedisHSet($first,'now',0);
                        $redis->RedisHSet($second,'now',1);
                        $redis->RedisHSet($second,'status',1);
                        $redis->RedisHSet($second,'count',$count+1);
                        $redis->RedisHSet($second,'sum',$sum+1);
                    }else{
                        for($i=$first+1;$i<$second;$i++){
                            $iSum = $redis->RedisHGet($i,'sum');
                            $redis->RedisHSet($i,'status',-1);
                            $redis->RedisHSet($i,'sum',$iSum+1);
                        }

                        $redis->RedisHSet($first,'now',0);
                        $redis->RedisHSet($second,'now',1);
                        $redis->RedisHSet($second,'status',1);
                        $redis->RedisHSet($second,'count',$count+1);
                        $redis->RedisHSet($second,'sum',$sum+1);
                    }

                }
            }else{
                return json_encode(array('error'=>1,'msg'=>'block waiting index...'));
            }
        }else{
            $redis->RedisSet('blockcountnew2',$blockInt);
            return json_encode(array('error'=>1,'msg'=>'redis adding...'));
        }
    }

    //NewGetNode
    public function NewGetNode()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $superNode = '166D9UoFdPcDEGFngswE226zigS8uBnm3C';
        $fakeNode  = '1111111111111111111114oLvT2';
        $nodeCount = 101;

        $redis = new RedisOperate();

        $voteInfo = file_get_contents('http://172.31.239.200/getactive');

        $voteInfoArray = json_decode($voteInfo,1);

        $temp1 = array();
        foreach ($voteInfoArray as $tKey => $tVal){
            $temp1[$tVal['address']]['address'] =  $tVal['address'];
            $temp1[$tVal['address']]['name'] =  $tVal['name'];
            $temp1[$tVal['address']]['count'] =  $tVal['count'];
        }

        $mysqlRes = DB::table('nodeinfosave')
            ->select('s_addr','s_count','s_sum')
            ->get();

        foreach ($mysqlRes as $mVal){
            $mAddr = $mVal->s_addr;
            $temp1[$mAddr]['ratio'] = round($mVal->s_count/$mVal->s_sum,4);
        }

        $temp1[$superNode]['address'] = $superNode;
        $temp1[$superNode]['name'] = 'LBTCSuperNode';
        $temp1[$superNode]['count'] = 2100000000000000;

        $tempArray = unserialize($redis->RedisGet('nodetemp2'));

        $temp2 = array();
        $addrArray = array();
        foreach ($tempArray as $val){
            $res = $redis->RedisHVals($val);
            $addr = $res[0];
            $temp2[$addr]['address'] = $addr;
            $temp2[$addr]['status'] = $res[1];
            $temp2[$addr]['now'] = $res[2];

            $temp2[$addr]['ratio'] = round($res[3]/$res[4],4);

            $lastAddr = $addr;
            array_push($addrArray,$addr);
        }

        $nodeFlag = 0;
        foreach ($addrArray as $aKey => $aVal){
            $nodeFlag = $nodeFlag + 1;
            if($temp2[$aVal]['now'] === '1' && $temp2[$aVal]['address'] != $lastAddr){
                //status 0
                $temp2[$aVal]['now'] = 0;
                //status 1
                $temp2[$addrArray[$aKey+1]]['now'] = 1;
            }

            if($aVal == $fakeNode){
                $fakeNodeNew = $fakeNode.'0';
                $temp2[$fakeNodeNew]['address'] = $fakeNodeNew;
                $temp2[$fakeNodeNew]['status'] = '-1';
                $temp2[$fakeNodeNew]['now'] = '0';
                $temp2[$fakeNodeNew]['name'] = 'Empty Node';
                $temp2[$fakeNodeNew]['count'] = 0;
            }else{
                $temp2[$aVal]['name'] = $temp1[$aVal]['name'];
                $temp2[$aVal]['count'] = $temp1[$aVal]['count'];
            }

            unset($temp1[$aVal]);
        }

        unset($temp2[$fakeNode]);

        $tempNum = $nodeCount - $nodeFlag;
        if($tempNum){
            for($i = 1;$i <= $tempNum;$i ++){
                $tempAddr = $fakeNode.$i;
                $temp2[$tempAddr]['address'] = $tempAddr;
                $temp2[$tempAddr]['status'] = '-1';
                $temp2[$tempAddr]['now'] = '0';
                $temp2[$tempAddr]['name'] = 'Empty Node';
                $temp2[$tempAddr]['count'] = 0;
            }
        }

        $resArray = array_merge($temp2,$temp1);
        return json_encode(array('error' => 0,'msg' => array_values($resArray)));
    }

    //lbtc rich list redis Timed task
    public function LbtcRichList()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $param = '300';

        $curlOperate = new CurlOperate();
        $redis = new RedisOperate();

        $redisParam = 'rich-list';

        $response = $curlOperate->GetRichList($param);
        $richRes = json_decode($response,1);

        if(array_key_exists('msg',$richRes)){
            return json_encode(array('error'=>1,'msg'=>$richRes['msg']));
        }

        $richArray = $richRes['result'];

        if(empty($richArray)){
            return json_encode(array('error'=>1,'msg'=>'Transaction information can not be found!'));
        }

        $richJson = json_encode($richArray);

        $redis->RedisSet($redisParam,$richJson);

        return json_encode(array('error'=>0,'msg'=>'redis rich-list save OK!'));

    }

    //get rich list
    public function GetLbtcRichList(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();
        $redisRich = $redis->RedisGet('rich-list');

        if($redisRich){
            return json_encode(array('error'=>0,'msg'=>json_decode($redisRich,1),'timestamp'=>$redis->RedisGet('timestamp'),'allcoins'=>$redis->RedisGet('all-coins')));
        }else{
            return json_encode(array('error'=>1,'msg'=>'rich-list is empty!'));
        }
    }

    //lbtc rich pre redis Timed task
    public function LbtcRichPre()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $flag = 6;

        $curlOperate = new CurlOperate();
        $redis = new RedisOperate();

        $big = 100000000;

        $redisParam = 'rich-pre';
        $resArray = [];

        $response = $curlOperate->GetRichPer(strval(1 * $big), strval(10 * $big));
        $richRes = json_decode($response, 1);

        if (array_key_exists('msg', $richRes)) {
            return json_encode(array('error' => 1, 'msg' => $richRes['msg']));
        }

        $richArray = $richRes['result'];
        if (empty($richArray)) {
            return json_encode(array('error' => 1, 'msg' => 'RichPre information can not be found!'));
        }

        $resArray[] = $richArray[0];
        $resArray[] = $richArray[1];

        $allCoins = $richArray[0]['coins'] + $richArray[1]['coins'];
        $allAddr = $richArray[0]['addresses'] + $richArray[1]['addresses'];

        for($i = 2;$i <= $flag;$i ++) {
            $limit = pow(10, $i) * $big;
            $response = $curlOperate->GetRichPer(strval(pow(10, $i - 1) * $big), strval($limit));

            $richRes = json_decode($response, 1);

            if (array_key_exists('msg', $richRes)) {
                return json_encode(array('error' => 1, 'msg' => $richRes['msg']));
            }

            $richArray = $richRes['result'];
            if (empty($richArray)) {
                return json_encode(array('error' => 1, 'msg' => 'RichPre information can not be found!'));
            }

            $resArray[] = $richArray[1];
            $allCoins = $allCoins + $richArray[1]['coins'];
            $allAddr = $allAddr + $richArray[1]['addresses'];
        }


        $richJson = json_encode($resArray);

        $redis->RedisSet($redisParam,$richJson);
        $redis->RedisSet('all-coins',$allCoins);
        $redis->RedisSet('all-addrs',$allAddr);
        $redis->RedisSet('timestamp',time());
        return json_encode(array('error'=>0,'msg'=>'redis rich-pre save OK!'));
    }

    //get rich pre
    public function GetLbtcRichPre(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();
        $redisRich = $redis->RedisGet('rich-pre');

        if($redisRich){
            return json_encode(array('error'=>0,'msg'=>json_decode($redisRich,1),'timestamp'=>$redis->RedisGet('timestamp'),'allcoins'=>$redis->RedisGet('all-coins'),'alladdrs'=>$redis->RedisGet('all-addrs')));
        }else{
            return json_encode(array('error'=>1,'msg'=>'rich-pre is empty!'));
        }
    }

    //set committees redis Timed task
    public function ListCommittees()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $redis = new RedisOperate();

        $redisParam = 'com-list';

        $response = $curlOperate->GetCommitteesList();
        $comRes = json_decode($response,1);

        if(array_key_exists('msg',$comRes)){
            return json_encode(array('error'=>1,'msg'=>$comRes['msg']));
        }

        $comArray = $comRes['result'];

        if(empty($comArray)){
            return json_encode(array('error'=>1,'msg'=>'Committees Data Empty!'));
        }

        $uTool = new UniversalTools();
        foreach ($comArray as $cKey => $cVal){
            $addr = $cVal['address'];
            $comArray[$cKey]["name"] = $uTool->FilterBadWords($comArray[$cKey]["name"]);
            $comArray[$cKey]["url"] = $uTool->FilterBadWords($comArray[$cKey]["url"]);
            $aRes = $curlOperate->GetCommitteeVotes($addr);

            $votes = json_decode($aRes,1)['result']['votes'];
            $comArray[$cKey]['votes'] = $votes;
        }

        $comJson = json_encode($comArray);

        $timeOut = 20;
        $redis->RedisSet($redisParam,$comJson,$timeOut);

        return json_encode(array('error'=>0,'msg'=>'redis com-list save OK!'));
    }

    //get committees redis
    public function GetListCommittees()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();

        $redisCom = $redis->RedisGet('com-list');

        if($redisCom){
            return json_encode(array('error'=>0,'msg'=>json_decode($redisCom,1)));
        }else{
            return json_encode(array('error'=>1,'msg'=>'com-list is empty!'));
        }
    }

    //get listcommitteevotes
    public function GetListCommitteeVotes(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Empty!'));
        }

        $param = trim($request->input('param'));

        if(strlen($param) > 32){
            return json_encode(array('error'=>1,'msg'=>'Name Too Long!'));
        }

        $redis = new RedisOperate();
        $resRedis = $redis->RedisGet('cv-'.$param);

        if($resRedis){
            $redisArray = json_decode($resRedis,1);
            $res = $redisArray['result'];

            if($res){
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                return json_encode(array('error'=>1,'msg'=>'No one voted for this council member!'));
            }
        }else{
            $curlOperate = new CurlOperate();
            $response = $curlOperate->GetCommitteeVotesList($param);
            $responseArray = json_decode($response,1);

            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];
            $timeOut = 6;
            if($res){
                $redis->RedisSet('cv-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                $redis->RedisSet('cv-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>1,'msg'=>'No one voted for this council member!'));
            }
        }
    }

    //get listvotedcommittee
    public function GetListVotedCommittee(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Empty!'));
        }

        $param = trim($request->input('param'));

        $paramLen = strlen($param);

        if($paramLen < 26 || $paramLen > 34){
            return json_encode(array('error'=>1,'msg'=>'Addr Format Error!'));
        }

        $redis = new RedisOperate();
        $resRedis = $redis->RedisGet('vc-'.$param);

        if($resRedis){
            $redisArray = json_decode($resRedis,1);
            $res = $redisArray['result'];

            if($res){
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                return json_encode(array('error'=>1,'msg'=>'This address did not vote for any committee members!'));
            }
        }else{
            $curlOperate = new CurlOperate();
            $response = $curlOperate->GetVotedCommitteeList($param);
            $responseArray = json_decode($response,1);

            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];
            $timeOut = 6;
            if($res){
                $redis->RedisSet('vc-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                $redis->RedisSet('vc-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>1,'msg'=>'This address did not vote for any committee members!'));
            }
        }
    }

    //SetBillsInfo
    public function SetBillsInfo()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $curlOperate = new CurlOperate();
        $redis = new RedisOperate();

        $response = $curlOperate->GetListBills();

        $responseArray = json_decode($response,1);

        if(array_key_exists('msg',$responseArray)){
            return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
        }

        $res = $responseArray['result'];

        if(!$res){
            return json_encode(array('error'=>1,'msg'=>'No bills!'));
        }

        $timeOut = 9;
        $redis->RedisSet('billsid',$response,$timeOut);

        $uTool = new UniversalTools();

        foreach ($res as $bid){
            $billId = $bid['id'];
            $billInfo = $curlOperate->GetBill($billId);
            $billArray = json_decode($billInfo,1);

            if(array_key_exists('msg',$billArray)){
                return json_encode(array('error'=>1,'msg'=>$billArray['msg']));
            }

            $billRes = $billArray['result'];

            $billRes["title"] = $uTool->FilterBadWords($billRes["title"]);
            $billRes["detail"] = $uTool->FilterBadWords($billRes["detail"]);
            $billRes["url"] = $uTool->FilterBadWords($billRes["url"]);

            $bVoters = $curlOperate->GetListBillVoters($billId);
            $bVotersArray = json_decode($bVoters,1);

            if(array_key_exists('msg',$bVotersArray)){
                return json_encode(array('error'=>1,'msg'=>$bVotersArray['msg']));
            }

            $bVotersRes = $bVotersArray['result'];

            $bOptions = $billRes["options"];
            foreach ($bOptions as $bKey => $bVal){
                $billRes["options"][$bKey]['address'] = $bVotersRes[$bKey]['addresses'];
                $billRes["options"][$bKey]['option'] = $uTool->FilterBadWords($billRes["options"][$bKey]['option']);
            }

            $redis->RedisSet('billid-'.$billId,json_encode($billRes),$timeOut);
        }
        return json_encode(array('error'=>0,'msg'=>'redis bill-info save OK!'));
    }

    //GetBillsInfo
    public function GetBillsInfo()
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        $redis = new RedisOperate();
        $billIds = $redis->RedisGet('billsid');

        if(empty($billIds)){
            return json_encode(array('error'=>1,'msg'=>'redis billids no exist!'));
        }

        $billIdsArray = json_decode($billIds,1);
        $billIdsArray = $billIdsArray['result'];

        $resArray = array();
        foreach ($billIdsArray as $bVal){
            $bId = $bVal['id'];
            $billInfo = $redis->RedisGet('billid-'.$bId);

            if(empty($billInfo)){
                echo 'billid-'.$bId.'isEmpty';
            }

            $billInfoArray = json_decode($billInfo,1);
            $billInfoArray['id'] = $bId;
            $resArray[] = $billInfoArray;
        }
        return json_encode(array('error'=>0,'msg'=>$resArray));
    }

    //VoterBillsByAddr
    public function VoterBillsByAddr(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Empty!'));
        }

        $param = trim($request->input('param'));

        $paramLen = strlen($param);

        if($paramLen < 26 || $paramLen > 34){
            return json_encode(array('error'=>1,'msg'=>'Addr Format Error!'));
        }

        $redis = new RedisOperate();
        $resRedis = $redis->RedisGet('vb-'.$param);

        if($resRedis){
            $redisArray = json_decode($resRedis,1);
            $res = $redisArray['result'];

            if($res){
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                return json_encode(array('error'=>1,'msg'=>'This address did not vote for any bills!'));
            }
        }else{
            $curlOperate = new CurlOperate();
            $response = $curlOperate->GetListVoterBills($param);
            $responseArray = json_decode($response,1);

            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];
            $timeOut = 6;
            if($res){
                $redis->RedisSet('vb-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                $redis->RedisSet('vb-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>1,'msg'=>'This address did not vote for any bills!'));
            }
        }
    }

    //GetListCommitteeBills
    public function GetListCommitteeBills(Request $request)
    {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");

        if (empty($request->input('param'))){
            return json_encode(array('error'=>1,'msg'=>'Input Empty!'));
        }

        $param = trim($request->input('param'));
        $paramLen = strlen($param);

        if($paramLen > 32){
            return json_encode(array('error'=>1,'msg'=>'Name Format Error!'));
        }

        $redis = new RedisOperate();
        $resRedis = $redis->RedisGet('lcb-'.$param);

        if($resRedis){
            $redisArray = json_decode($resRedis,1);
            $res = $redisArray['result'];

            if($res){
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                return json_encode(array('error'=>1,'msg'=>'This council member did not propose any motion!'));
            }
        }else{
            $curlOperate = new CurlOperate();
            $response = $curlOperate->GetListCommitteeBills($param);

            $responseArray = json_decode($response,1);

            if(array_key_exists('msg',$responseArray)){
                return json_encode(array('error'=>1,'msg'=>$responseArray['msg']));
            }

            $res = $responseArray['result'];
            $timeOut = 6;
            if($res){
                $redis->RedisSet('lcb-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>0,'msg'=>$res));
            }else{
                $redis->RedisSet('lcb-'.$param,json_encode($responseArray),$timeOut);
                return json_encode(array('error'=>1,'msg'=>'This council member did not propose any motion!'));
            }
        }
    }

    //test
    public function Test()
    {
        echo "just test";
    }
}
