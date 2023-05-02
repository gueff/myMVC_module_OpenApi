
# myMVC_module_OpenApi

## Requirements

- Linux
- php >=7.4
- myMVC
    - myMVC 3.2.x: https://github.com/gueff/myMVC/tree/3.2.x
    - ZIP: https://github.com/gueff/myMVC/archive/refs/heads/3.2.x.zip
    - Doku: https://mymvc.ueffing.net/

## Installation

_git clone_  
~~~bash
cd /modules/;

git clone --branch dev-master \
https://github.com/gueff/myMVC_module_OpenApi.git \
OpenApi;
~~~

## Usage

_validate against openapi file_
~~~php
use OpenApi\Model\Validate;

$oDTValidateRequestResponse = Validate::request(
    $oDTRequestCurrent,
    Config::get_MVC_PUBLIC_PATH(). '/openapi/api.yaml'
);

header('Content-Type: application/json');
echo json_encode(Convert::objectToArray($oDTValidateRequestResponse));
~~~

_validate against openapi URL_
~~~php
use OpenApi\Model\Validate;

// validate against openapi URL
$oDTValidateRequestResponse = Validate::request(
    $oDTRequestCurrent,
    'https://example.com/api/openapi.yaml'
);

header('Content-Type: application/json');
echo json_encode(Convert::objectToArray($oDTValidateRequestResponse));
~~~
