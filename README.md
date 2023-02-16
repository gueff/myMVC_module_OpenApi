
# myMVC_module_OpenApi

<!--
<img src="https://github.com/gueff/myMVC_module_OpenApi/actions/workflows/super-linter.yml/badge.svg">
-->

## Installation

_git clone_  
~~~bash
cd /modules/;

git clone \
https://foo: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@github.com/gueff/myMVC_module_OpenApi.git \
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
    'https://api.mediafinanz.de/api/openapi.yaml'
);

header('Content-Type: application/json');
echo json_encode(Convert::objectToArray($oDTValidateRequestResponse));
~~~
