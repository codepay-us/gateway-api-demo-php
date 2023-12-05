# PHP sample instructions for use

The sample is suitable for the PHP programming language and can call all OpenAPI interfaces. Sample encapsulates the process of executing integration, including adding and verifying signatures that call the payment gateway API. This is just a example, and we do not guarantee that the code will be applicable to any programming environment. It only demonstrates how to call our API. You can refer to this code to write your own program.

## Condition

Suitable for development environments above PHP 5.5.

## Download address

-   <a href="https://github.com/codepay-us/gateway-api-demo-php/" target="_blank">
        GitHub project home page
    </a>

## Usage steps

### 1. Add dependencies, use 'Composer' to download the following package

```JSON
{
    "require": {
        "ext-json": "*",
        "ext-openssl": "*",
        "ext-curl": "*"
    }
}
```

'ext-curlL' is used for packets sent over the HTTP network, 'ext-json' parses and processes JSON packets, and 'ext-openssl' is used for RSA signature calculation packets.

### 2. Refer to the GitHub example code and write your program. The following are the key codes

```PHP
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
```
