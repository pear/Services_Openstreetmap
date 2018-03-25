<?php

Class OAuthHelper{


	public static function getOauthTimestamp(){
		return time();
	}
	
	public static function getOauthNonce(){
		return md5(uniqid());
	}

	private static function hmac_sha1($key, $data){
        return base64_encode( hash_hmac('sha1', $data, $key, true));
    }

    public static function getOauthSignature($key,$data){
    	return rawurlencode(self::hmac_sha1($key,$data));
    }


    public static function assocArrayToString($arr,$glue='=',$sep='&',$wrap=''){
	    $str = '';
	    $i=0;
	    if (is_array($arr))
	    {
	    	$count=count($arr);
	        foreach ($arr as $key=>$value)
	        {
	        	$i++;
	            $str .= $key.$glue.$wrap.$value.$wrap;
	            if($i<$count){
	            	$str.=$sep;
	            }
	  
	        }
	        return $str;
	    } else {
	        return false;
	    }
	}


}