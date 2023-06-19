<?php

namespace OpenApi\Model;

use HKarlstrom\OpenApiReader\OpenApiReader;

class Route
{
    /**
     * @usage \OpenApi\Model\Route::autoCreateFromOpenApiFile( OPENAPI_FILE_ABSOLUTE );
     * @param string $sOpenApiFileAbs
     * @return void
     */
    public static function autoCreateFromOpenApiFile(string $sOpenApiFileAbs = '')
    {
        // read openapi file and convert to array
        $aOpenApiReader = current(
            \MVC\Convert::objectToArray(
                new OpenApiReader($sOpenApiFileAbs)
            )
        );

        // read PATHs from openapi
        $aRawPath = get($aOpenApiReader['raw']['paths'], array());

        // finally: dynamically create routes from openapi
        foreach ($aRawPath as $sPath => $aPath)
        {
            $sMethod = trim(strtoupper(current(array_keys($aPath))));
            \MVC\Route::$sMethod(
                $sPath,
                '\Api\Controller\Api::delegate',
                $sOpenApiFileAbs
            );
        }
    }
}