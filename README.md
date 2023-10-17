
# myMVC_module_OpenApi

## Requirements

- Linux
- php >= 8
  - `pdo` extension
- myMVC 3.3.x
  - `git clone --branch 3.3.x https://github.com/gueff/myMVC.git myMVC_3.3.x`
  - Docs: <https://mymvc.ueffing.net/>
  - github: <https://github.com/gueff/myMVC/tree/3.3.x>

---

## Installation

_git clone_
~~~bash
cd /modules/;

git clone --branch 1.1.x \
https://github.com/gueff/myMVC_module_OpenApi.git \
OpenApi;
~~~

---

## Usage

_validate against openapi **file**_
~~~php
use OpenApi\Model\Validate;

$oDTValidateRequestResponse = Validate::request(
    $oDTRequestCurrent,
    Config::get_MVC_PUBLIC_PATH() . '/openapi/api.yaml'
);

header('Content-Type: application/json');
echo json_encode(Convert::objectToArray($oDTValidateRequestResponse));
~~~

_validate against openapi **URL**_
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

**auto-creating myMVC Routes from openapi file**

_All Routes lead to their given `operationId`, set in openapi_    
~~~php
\OpenApi\Model\Route::autoCreateFromOpenApiFile(
    Config::get_MVC_PUBLIC_PATH() . '/openapi/api.yaml',
    '\Foo\Controller\Api'
);
~~~

_All Routes lead explicitely to `Api::delegate()`_      
~~~php
\OpenApi\Model\Route::autoCreateFromOpenApiFile(
    Config::get_MVC_PUBLIC_PATH() . '/openapi/api.yaml',
    '\Foo\Controller\Api',
    'delegate'
);
~~~

---

## Get Logs

Logs are fired to Events.

Available events are:

- `myMVC_module_OpenApi::sYamlSource`

_listen to event and write its content to a logfile_    
~~~php
\MVC\Event::bind('myMVC_module_OpenApi::sYamlSource', function($sContent){
    \MVC\Log::write($sContent, 'openapi.log');
});
~~~