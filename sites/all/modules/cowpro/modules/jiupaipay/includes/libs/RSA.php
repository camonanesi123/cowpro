<?php  
/** 
 * RSA算法类 
 * 签名及密文编码：base64字符串/十六进制字符串/二进制字符串流 
 * 填充方式: PKCS1Padding（加解密）/NOPadding（解密） 
 * 
 * Notice:Only accepts a single block. Block size is equal to the RSA key size!  
 * 如密钥长度为1024 bit，则加密时数据需小于128字节，加上PKCS1Padding本身的11字节信息，所以明文需小于117字节 
 * 
 * @author: linvo 
 * @version: 1.0.0 
 * @date: 2013/1/23 
 */  
class RSA
{
  
    private $pubKey = null;
    private $priKey = null;
    private $noresource_pubKey = null;
    private $noresource_priKey = null;
  
    /** 
     * 自定义错误处理 
     */  
    private function _error($msg){  
        die('RSA Error:' . $msg); //TODO  
    }  
  
    /** 
     * 构造函数 
     * 
     * @param string 公钥（验签和加密时传入） 
     * @param string 私钥（签名和解密时传入） 
     */  
    public function __construct($private_key, $public_key)
    {  
       	$pemPriKey = chunk_split($private_key, 64, "\n");
		$pemPriKey = "-----BEGIN PRIVATE KEY-----\n".$pemPriKey."-----END PRIVATE KEY-----\n";
		$this->priKey = openssl_get_privatekey($pemPriKey); 

		$pemPubKey = chunk_split($public_key, 64, "\n");
		$pemPubKey = "-----BEGIN PUBLIC KEY-----\n".$pemPubKey."-----END PUBLIC KEY-----\n";
        $this->pubKey = openssl_get_publickey($pemPubKey);
    }
  
  
    /** 
     * 生成签名 
     * 生成时使用SHA256算法加密
     * @param string 签名材料 
     * @param string 签名编码（base64/hex/bin） 
     * @return 签名值 
     */  
    public function sign($data, $code = 'base64'){
        $ret = false;
        if (openssl_sign($data, $ret, $this->priKey, OPENSSL_ALGO_SHA256)){
            $ret = $this->_encode($ret, $code);
        }  
        return $ret;
    }  
  
    /** 
     * 验证签名 
     * 验证时使用SHA256算法解密
     * @param string $data 签名材料 
     * @param string $sign 签名值 
     * @param string $alg 验签算法
     * @param string $code 签名编码（base64/hex/bin） 
     * @return bool  
     */  
    public function verify($data, $sign, $alg=OPENSSL_ALGO_SHA256, $code='base64'){
        $ret = false; 
        $sign = $this->_decode($sign, $code);
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $this->pubKey, $alg)){
                case 1: $ret = true; break;
                case 0:
                case -1:
                default: $ret = false;
            }
        }
        
        return $ret;
    }

    /** 
     * 加密 
     * 
     * @param string 明文 
     * @param string 密文编码（base64/hex/bin） 
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING） 
     * @return string 密文 
     */  
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING){  
        $ret = false;      
        if (!$this->_checkPadding($padding, 'en')) $this->_error('padding error');  
        if (openssl_public_encrypt($data, $result, $this->pubKey, $padding)){  
            $ret = $this->_encode($result, $code);  
        }  
        return $ret;  
    }  
  
    /** 
     * 解密 
     * 
     * @param string 密文 
     * @param string 密文编码（base64/hex/bin） 
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING） 
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block） 
     * @return string 明文 
     */  
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false){  
        $ret = false;  
        $data = $this->_decode($data, $code);  
        if (!$this->_checkPadding($padding, 'de')) $this->_error('padding error');  
        if ($data !== false){  
            if (openssl_private_decrypt($data, $result, $this->priKey, $padding)){  
                $ret = $rev ? rtrim(strrev($result), "\0") : ''.$result;  
            }   
        }  
        return $ret;  
    }
    
    /**
     * 生成密钥
     */
    public function GenerateKey($dn=NULL, $config=NULL, $passphrase=NULL)
    {
    	
        if(!$dn)
        {
            /* $dn = array(
                "countryName" => "CN",
                "stateOrProvinceName" => "JIANGSU",
                "localityName" => "Suzhou",
                "organizationName" => "95epay",
                "organizationalUnitName" => "Moneymoremore",
                "commonName" => "www.moneymoremore.com",
                "emailAddress" => "csreason@95epay.com"
            ); */
            $dn = array(
                "countryName" => "CN",
                "stateOrProvinceName" => "Beijing",
                "localityName" => "Beijing",
                "organizationName" => "FaHuiDai",
                "organizationalUnitName" => "BeiJingRongDingHuiFengCapitalManagementLimited",
                "commonName" => "www.fahuidai.com",
                "emailAddress" => "fahuidai@souhu.com"
            );
        }
        $privkey = openssl_pkey_new();
        echo "private key:";
        echo "<br>";
        if($passphrase != NULL)
        {
            openssl_pkey_export($privkey, $privatekey, $passphrase);
        }
        else
        {
            openssl_pkey_export($privkey, $privatekey);
        }
        echo $privatekey;
        echo "<br><br>";
        
        $publickey = openssl_pkey_get_details($privkey);
        $publickey = $publickey["key"];
        
		echo "public key:";
        echo "<br>";
        echo $publickey;
        
        $this->noresource_pubKey=$publickey;
        $this->noresource_priKey=$privatekey;
    }
	/**
     * 生成证书文件
     */
public function GenerateKeytwo($dn=NULL, $config=NULL, $passphrase=NULL)
    {
        if(!$dn)
        {
            /* $dn = array(
                "countryName" => "CN",
                "stateOrProvinceName" => "JIANGSU",
                "localityName" => "Suzhou",
                "organizationName" => "95epay",
                "organizationalUnitName" => "Moneymoremore",
                "commonName" => "www.moneymoremore.com",
                "emailAddress" => "csreason@95epay.com"
            ); */
            $dn = array(
                "countryName" => "CN",
                "stateOrProvinceName" => "Beijing",
                "localityName" => "Beijing",
                "organizationName" => "FaHuiDai",
                "organizationalUnitName" => "BeiJingRongDingHuiFengCapitalManagementLimited",
                "commonName" => "www.fahuidai.com",
                "emailAddress" => "fahuidai@souhu.com"
            );
        }
       
        
        $privkey = openssl_pkey_new(array('digest_alg' => 'sha256', 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 1024));
        echo "private key:";
        echo "<br>";
        if($passphrase != NULL)
        {
            openssl_pkey_export($privkey, $privatekey, $passphrase);
        }
        else
        {
            openssl_pkey_export($privkey, $privatekey);
        }
        echo $privatekey;
        echo "<br><br>";
        
        $publickey = openssl_pkey_get_details($privkey);
        $publickey = $publickey["key"];
        
		echo "public key:";
        echo "<br>";
        echo $publickey;
        
        $this->noresource_pubKey=$publickey;
        $this->noresource_priKey=$privatekey;
        
        $pubpath = "./Key/publicKey.cer"; // 生成公钥证书路径  
        $pfxpath = "./Key/test.pfx"; // 密钥文件路径 
		$pripath = "./Key/privateKey.cer"; // 生成私钥证书路径
        //生成证书    
        $privkey = openssl_pkey_new();    
        $csr = openssl_csr_new($dn, $privkey);    
        $sscert = openssl_csr_sign($csr, null, $privkey,365);    
        openssl_x509_export($sscert, $csrkey); //导出证书$csrkey    
        openssl_pkcs12_export($sscert, $privatekey, $privkey, $passphrase); //导出密钥$privatekey 
        dump($csrkey);
        dump($privatekey); 
         
        //生成生成公钥证书文件    
        $fp = fopen($pubpath, "w");    
        fwrite($fp, $publickey);    
        fclose($fp);
		// 生成私钥证书文件
		$fp = fopen($pripath, "w");    
        fwrite($fp, $this->noresource_priKey);    
        fclose($fp);
        //生成密钥文件    
        $fp = fopen($pfxpath, "w");    
        fwrite($fp, $privatekey);    
        fclose($fp);
    }
  
  
    // 私有方法  
  
    /** 
     * 检测填充类型 
     * 加密只支持PKCS1_PADDING 
     * 解密支持PKCS1_PADDING和NO_PADDING 
     *  
     * @param int 填充模式 
     * @param string 加密en/解密de 
     * @return bool 
     */  
    private function _checkPadding($padding, $type){  
        if ($type == 'en'){  
            switch ($padding){  
                case OPENSSL_PKCS1_PADDING:  
                    $ret = true;  
                    break;  
                default:  
                    $ret = false;  
            }  
        } else {  
            switch ($padding){  
                case OPENSSL_PKCS1_PADDING:  
                case OPENSSL_NO_PADDING:  
                    $ret = true;  
                    break;  
                default:  
                    $ret = false;  
            }  
        }  
        return $ret;  
    }  
  
    private function _encode($data, $code){  
        switch (strtolower($code)){  
            case 'base64':  
                $data = base64_encode(''.$data);  
                break;  
            case 'hex':  
                $data = bin2hex($data);  
                break;  
            case 'bin':  
            default:  
        }  
        return $data;  
    }  
  
    private function _decode($data, $code){  
        switch (strtolower($code)){  
            case 'base64':  
                $data = base64_decode($data);  
                break;  
            case 'hex':  
                $data = $this->_hex2bin($data);  
                break;  
            case 'bin':  
            default:  
        }  
        return $data;  
    }  
  
    private function _getPublicKey($file){
        $key_content = $this->_readFile($file);  
        if ($key_content){  
            $this->pubKey = openssl_get_publickey($key_content);  
        }  
    }  
  
    private function _getPrivateKey($file){  
        $key_content = $this->_readFile($file);  
        if ($key_content){  
            $this->priKey = openssl_get_privatekey($key_content);  
        }  
    }  
  
    private function _readFile($file){  
        $ret = false;  
        if (!file_exists($file)){  
            $this->_error("The file {$file} is not exists");  
        } else {  
            $ret = file_get_contents($file);  
        }  
        return $ret;  
    }  
  
  
    private function _hex2bin($hex = false){  
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }  
  
  
  
}