<?php
/**
 * Created by PhpStorm.
 * User: insen
 * Date: 2018/3/16
 * Time: 16:18
 */

namespace App\Http\Commons;

use Illuminate\Http\Request;

class UniversalTools
{
    //access limit
    public function AccessLimit(Request $request,$limit,$time,Array $previousArray=[],Array $whiteArray=[])
    {
        $redis = new RedisOperate();

        //set ip allow
        $request->setTrustedProxies($previousArray);
        $ipKey = $request->getClientIp();

        if(in_array($ipKey,$whiteArray)){
            return 1;
        }

        $check = $redis->RedisExist($ipKey);
        if($check){
            $redis->RedisIncr($ipKey);
            $count = $redis->RedisGet($ipKey);
            if($count > $limit){
                return 0;
            }else{
                return 1;
            }
        }else{
            $redis->RedisIncr($ipKey);
            //limit time
            $redis->RedisExpire($ipKey,$time);
            return 1;
        }
    }


    //filter words
    public function FilterBadWords($content)
    {

        $words = ["*"];
        $content_filter = mb_strtolower($content);
        $flag_arr=array('？','！','￥','（','）','：','‘','’','“','”','《','》','，','…','。','、','nbsp','】','【','～','—');

        $content_filter=preg_replace('/\s/','',preg_replace("/[[:punct:]]/",'',strip_tags(html_entity_decode(str_replace($flag_arr,'',$content_filter),ENT_QUOTES,'UTF-8'))));


        foreach ($words as $word)
        {
            $res = strpos($content_filter, $word);
            if($res !== false){
                return "-_-";
            }
            $preg_letter = '/^[A-Za-z]+$/';
            if (preg_match($preg_letter, $content_filter))
            {
                $content_filter = strtolower($content_filter);
                $pattern_1 = '/([^A-Za-z]+' . $word . '[^A-Za-z]+)|([^A-Za-z]+' . $word . '\s+)|(\s+' . $word . '[^A-Za-z]+)|(^' . $word . '[^A-Za-z]+)|([^A-Za-z]+' . $word.'$)/';
                if (preg_match($pattern_1, $content_filter))
                {
                    return "-_-";
                }
                $pattern_2 = '/(^' . $word . '\s+)|(\s+' . $word . '\s+)|(\s+' . $word . '$)|(^' . $word . '$)/';

                if (preg_match($pattern_2, $content_filter))
                {
                    return "-_-";
                }
            }else{
                $pattern = '/\s*' . $word . '\s*/';
                if (preg_match($pattern, $content_filter))
                {
                    return "-_-";
                }
            }
        }

        $word = require 'badwords.php';
        $lexicon = array_combine($word,array_fill(0,count($word),':-)'));
        $str = strtr($content, $lexicon);
        return $str;
    }


    //back Status Code
    public function HttpStatus($num)
    {
        static $http = array (
            100 => "HTTP/1.1 100 Continue",
            101 => "HTTP/1.1 101 Switching Protocols",
            200 => "HTTP/1.1 200 OK",
            201 => "HTTP/1.1 201 Created",
            202 => "HTTP/1.1 202 Accepted",
            203 => "HTTP/1.1 203 Non-Authoritative Information",
            204 => "HTTP/1.1 204 No Content",
            205 => "HTTP/1.1 205 Reset Content",
            206 => "HTTP/1.1 206 Partial Content",
            300 => "HTTP/1.1 300 Multiple Choices",
            301 => "HTTP/1.1 301 Moved Permanently",
            302 => "HTTP/1.1 302 Found",
            303 => "HTTP/1.1 303 See Other",
            304 => "HTTP/1.1 304 Not Modified",
            305 => "HTTP/1.1 305 Use Proxy",
            307 => "HTTP/1.1 307 Temporary Redirect",
            400 => "HTTP/1.1 400 Bad Request",
            401 => "HTTP/1.1 401 Unauthorized",
            402 => "HTTP/1.1 402 Payment Required",
            403 => "HTTP/1.1 403 Forbidden",
            404 => "HTTP/1.1 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.1 406 Not Acceptable",
            407 => "HTTP/1.1 407 Proxy Authentication Required",
            408 => "HTTP/1.1 408 Request Time-out",
            409 => "HTTP/1.1 409 Conflict",
            410 => "HTTP/1.1 410 Gone",
            411 => "HTTP/1.1 411 Length Required",
            412 => "HTTP/1.1 412 Precondition Failed",
            413 => "HTTP/1.1 413 Request Entity Too Large",
            414 => "HTTP/1.1 414 Request-URI Too Large",
            415 => "HTTP/1.1 415 Unsupported Media Type",
            416 => "HTTP/1.1 416 Requested range not satisfiable",
            417 => "HTTP/1.1 417 Expectation Failed",
            500 => "HTTP/1.1 500 Internal Server Error",
            501 => "HTTP/1.1 501 Not Implemented",
            502 => "HTTP/1.1 502 Bad Gateway",
            503 => "HTTP/1.1 503 Service Unavailable",
            504 => "HTTP/1.1 504 Gateway Time-out"
        );
        header($http[$num]);
        exit();
    }
}