<?php
/**
 * Created by PhpStorm.
 * User: insen
 * Date: 2018/3/16
 * Time: 16:18
 */

namespace App\Http\Commons;


class CurlOperate
{
    const rpc_url = 'http://127.0.0.1';
    const rpc_user = '*';
    const rpc_pwd  = '*';

    public function GetAddressBalance($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getaddressbalance","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);
            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){
                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);
                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){
                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);
                }
            }
        }

    }

    public function GetBlockHash($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getblockhash","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);
            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){
                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);
                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){
                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);
                }
            }
        }
    }

    public function GetBlock($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getblock","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetTransactionNew($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"gettransactionnew","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

           $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetBlockCount()
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getblockcount","params"=>[],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

           $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function ListDelegates()
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listdelegates","params"=>[],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

           $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function ListReceivedVotes($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listreceivedvotes","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

           $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function ListVotedDelegates($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listvoteddelegates","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

           $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetDelegateVotes($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getdelegatevotes","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

           $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function SearchHash($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"gettransactionnew","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

            //search blockhash
            if($backStatusTemp != 200 && $flag >2){

                $backStatus1 = 888;

                //Record requests
                $flag1 = 1;

                while($backStatus1 != 200){
                    $jsonArr1 = ["method"=>"getblock","params"=>[$params],"id"=>1];
                    $jsonStr1 = json_encode($jsonArr1);
                    $Authorization = base64_encode($Author);

                    $ch1 = curl_init();
                    curl_setopt($ch1, CURLOPT_POST, 1);
                    curl_setopt($ch1, CURLOPT_URL, $url);
                    curl_setopt($ch1, CURLOPT_POSTFIELDS, $jsonStr1);
                    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length: ' . strlen($jsonStr1),
                            'Authorization:Basic '.$Authorization
                        )
                    );

                    $response1 = curl_exec($ch1);

                    $backStatusTemp1= curl_getinfo($ch1,CURLINFO_HTTP_CODE);

                    $flag1++;


                    if($flag1 > 3){
                        curl_close($ch1);
                        $uTool = new UniversalTools();
                        $uTool->HttpStatus(503);
                    }

                    if($backStatusTemp1 == 200){
                        curl_close($ch1);
                        $resArray1 = array(
                            'type' => 'BlockHash',
                            'data' => $response1
                        );
                        return json_encode($resArray1);
                    }
                }
            }

            $flag++;

            if($flag > 3){
                curl_close($ch);
                $uTool = new UniversalTools();
                $uTool->HttpStatus(503);
            }

            if($backStatusTemp == 200){
                curl_close($ch);
                $resArray = array(
                    'type' => 'TXHash',
                    'data' => $response
                );
                return json_encode($resArray);
            }
        }
    }

    public function GetTxByAddr($addr,$limit = "100",$startBlock = "",$endBlock = "")
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        if(empty($startBlock) || empty($endBlock)){
            $jsonArr = ["method"=>"getaddresstxids","params"=>[$addr],"id"=>1];
        }else{
            $jsonArr = ["method"=>"getaddresstxids","params"=>[$addr,$limit,$startBlock,$endBlock],"id"=>1];
        }

        while($backStatus != 200){
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

           $responseArray = json_decode($response,1);

            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }


    public function GetRichList($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getcoinrank","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetRichPer($p1,$p2)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getcoindistribution","params"=>[$p1,$p2],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }


    public function GetCommitteesList()
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listcommittees","params"=>[],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetCommitteeVotesList($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listcommitteevoters","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetVotedCommitteeList($params)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listvotercommittees","params"=>[$params],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetListBills()
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listbills","params"=>[],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetBill($param)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getbill","params"=>[$param],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetListBillVoters($param)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listbillvoters","params"=>[$param],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetListVoterBills($param)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listvoterbills","params"=>[$param],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetCommitteeVotes($param)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"getcommittee","params"=>[$param],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

    public function GetListCommitteeBills($param)
    {
        $url  = self::rpc_url;
        $user = self::rpc_user;
        $pwd  = self::rpc_pwd;
        $Author = $user.':'.$pwd;

        $backStatus = 888;

        //Record requests
        $flag = 1;

        while($backStatus != 200){
            $jsonArr = ["method"=>"listcommitteebills","params"=>[$param],"id"=>1];
            $jsonStr = json_encode($jsonArr);
            $Authorization = base64_encode($Author);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonStr),
                    'Authorization:Basic '.$Authorization
                )
            );

            $response = curl_exec($ch);

            $responseArray = json_decode($response,1);

            
            if($responseArray){
                if(array_key_exists('error',$responseArray) && $responseArray['error'] != null){
                    curl_close($ch);
                    return json_encode(array('error'=>1,'msg'=>$responseArray['error']['message']));
                }

                $backStatusTemp= curl_getinfo($ch,CURLINFO_HTTP_CODE);

                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }

                if($backStatusTemp == 200){
                    curl_close($ch);
                    return $response;
                }
            }else{
                $flag++;

                if($flag > 3){

                    curl_close($ch);
                    $uTool = new UniversalTools();
                    $uTool->HttpStatus(503);

                }
            }
        }
    }

}
