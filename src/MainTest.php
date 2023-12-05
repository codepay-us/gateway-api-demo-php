

<?php

// 1. Global parameter settings
$appRsaPrivateKeyPem = "<YOUR APP RSA PRIVATE KEY>";
$gatewayRsaPublicKeyPem = "<YOUR GATEWAY RSA PUBLIC KEY>";
$gatewayUrl = "<YOUR GATEWAY URL>";
$appId = "<YOUR APP ID>";

// 2. Set parameters
    // Common parameters
$parameters["app_id"] = $appId;
$parameters["charset"] = "UTF-8";
$parameters["format"] = "JSON";
$parameters["sign_type"] = "RSA2";
$parameters["version"] = "1.0";
$parameters["timestamp"] = getMillisecond();
$parameters["method"] = "order.query";
    // API owned parameters
$parameters["merchant_no"] = "312100000164";
$parameters["merchant_order_no"] = "TEST_1685946062143";

// 3. Build a string to be signed
$stringToBeSigned = buildToBeSignString($parameters);
echo "StringToBeSigned : " . $stringToBeSigned;
echo "</br></br>";

// 4. Calculate signature
$sign = generateSign($stringToBeSigned, $appRsaPrivateKeyPem);
$parameters["sign"] = $sign;

// 5. Send HTTP request
$jsonString = json_encode($parameters);
echo "Request to gateway[" . $gatewayUrl . "] send data  -->> " . $jsonString;
echo "</br></br>";
$responseStr = json_post($gatewayUrl, $jsonString);
echo "Response from gateway[" . $gatewayUrl . "] receive data <<-- " . $responseStr;
echo "</br></br>";

// 6. Verify the signature of the response message
$respObject = json_decode($responseStr, true);
$respStringToBeSigned  = buildToBeSignString($respObject);
echo "RespStringToBeSigned  : " . $respStringToBeSigned;
echo "</br></br>";
$respSignature = $respObject["sign"];
$verified = verify($respStringToBeSigned, $respSignature, $gatewayRsaPublicKeyPem);

echo "SignVerifyResult  : " . $verified;
echo "</br></br>";

/**
 * Build a string to be signed
 * @param $params
 * @return string
 */
 function buildToBeSignString($params)
{
    ksort($params);
    unset($params['sign']);

    $stringToBeSigned = "";
    $i = 0;
    foreach ($params as $k => $v) {
        if ("@" != substr($v, 0, 1)) {

            if ($i == 0) {
                $stringToBeSigned .= "$k" . "=" . "$v";
            } else {
                $stringToBeSigned .= "&" . "$k" . "=" . "$v";
            }
            $i++;
        }
    }

    unset ($k, $v);
    return $stringToBeSigned;
}

function generateSign($data, $priKey)
{
    $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
        wordwrap($priKey, 64, "\n", true) .
        "\n-----END RSA PRIVATE KEY-----";

    ($res) or die('The private key format you are using is incorrect. Please check the RSA private key configuration');

    openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);

    $sign = base64_encode($sign);
    return $sign;
}

/**
 * PHP Post Json
 * @param $url request url
 * @param $data JSON string/array sent
 * @return array
 */
function json_post($url, $data = NULL)
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if(!$data){
        return 'data is null';
    }
    if(is_array($data))
    {
        $data = json_encode($data);
    }
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($curl);
    $errorno = curl_errno($curl);
    if ($errorno) {
        return $errorno;
    }
    curl_close($curl);
    return $res;
}

function getMillisecond() {
  list($t1, $t2) = explode(' ', microtime());
  return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

function verify($data, $sign, $pubKey)
{
    $res = "-----BEGIN PUBLIC KEY-----\n" .
        wordwrap($pubKey, 64, "\n", true) .
        "\n-----END PUBLIC KEY-----";

    ($res) or die('RSA public key error. Please check if the public key file format is correct');

    // Call the openssl built-in method for signature verification and return the bool value
    return (openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256) === 1) ? 'true' : 'false';
}

?>

