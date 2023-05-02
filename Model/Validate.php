<?php

namespace OpenApi\Model;

use HKarlstrom\Middleware\OpenApiValidation;
use HKarlstrom\Middleware\OpenApiValidation\Exception\FileNotFoundException;
use HKarlstrom\Middleware\OpenApiValidation\Exception\InvalidOptionException;
use HKarlstrom\OpenApiReader\OpenApiReader;
use MVC\Cache;
use MVC\Config;
use MVC\Convert;
use MVC\DataType\DTRequestCurrent;
use MVC\Debug;
use MVC\Error;
use MVC\Log;
use MVC\Request;
use MVC\Route;
use MVC\Strings;
use OpenApi\DataType\DTValidate;
use OpenApi\DataType\DTValidateMessage;
use OpenApi\DataType\DTValidateRequestResponse;

class Validate
{
    /**
     * @param \MVC\DataType\DTRequestCurrent|null $oDTRequestCurrent
     * @param                                     $sYamlFileAbs file | URL
     * @return \OpenApi\DataType\DTValidateRequestResponse
     * @example {"bSuccess":true,"aMessage":[],"aValidationResult":[]}
     * @example {"bSuccess":false,"aMessage":[],"aValidationResult":[{"name":"data.1.contact.city","code":"error_type","value":123,"in":"body","expected":"string","used":"integer"}]}
     * @throws \ReflectionException
     */
    public static function request(DTRequestCurrent $oDTRequestCurrent = null, $sYamlFileAbs = '')
    {
        // Response
        $oDTValidateRequestResponse = DTValidateRequestResponse::create();

        // Fallback
        if (null === $oDTRequestCurrent)
        {
            $sMessage = 'no object of type DTRequestCurrent passed; creating object DTRequestCurrent on Request::getCurrentRequest()';
            Error::notice($sMessage);
            $oDTRequestCurrent = Request::getCurrentRequest();
            $oDTValidateRequestResponse->add_aMessage(
                DTValidateMessage::create()
                    ->set_sSubject('Notice')
                    ->set_sBody($sMessage)
            );
        }

        // $sYamlFileAbs is URL
        if (true === (boolean) filter_var($sYamlFileAbs, FILTER_VALIDATE_URL))
        {
            $sCacheKey = Strings::seofy($sYamlFileAbs);
            Cache::autoDeleteCache($sCacheKey, get(Config::get_MVC_CACHE_CONFIG()['iDeleteAfterMinutes'], 1440));
            $sContent = Cache::getCache($sCacheKey);

            if (true === empty($sContent))
            {
                $sContent = file_get_contents($sYamlFileAbs);
                Cache::saveCache($sCacheKey, $sContent);
            }

            // finally overwrite and wrap into array, as OpenApiValidation requires it that way
            $sYamlFileAbs = array($sContent);
        }
        // $sYamlFileAbs is file, but missing
        elseif (false === file_exists($sYamlFileAbs))
        {
            $sMessage = 'file does not exist: ' . $sYamlFileAbs;
            Error::error($sMessage);
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Error')
                        ->set_sBody($sMessage)
                );

            return $oDTValidateRequestResponse;
        }

        // check request method
        $bMethodsMatch = (Request::getCurrentRequest()->get_requestmethod() === Route::getCurrent()->get_method());

        if (false === $bMethodsMatch)
        {
            $sMessage = 'wrong request method `' . $oDTRequestCurrent->get_requestmethod() . '`. It has to be: `' . Route::getCurrent()->get_method() . '`';
            Error::notice($sMessage);
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Notice')
                        ->set_sBody($sMessage)
                );

            return $oDTValidateRequestResponse;
        }

        // check the request content type
        try {
            $oOpenApiReader = new OpenApiReader($sYamlFileAbs);

            // get the expected type of request
            $sExpectedType = $oOpenApiReader->getOperationRequestBody(
                $oDTRequestCurrent->get_path(),
                strtolower($oDTRequestCurrent->get_requestmethod())
            )->getContent()->type;

            // check content type "json"
            if (true === (boolean) stristr($sExpectedType, 'json') && false === Strings::isJson($oDTRequestCurrent->get_input()))
            {
                $sMessage = 'content type has to be valid `' . $sExpectedType . '`';
                Error::error(json_last_error_msg() . ' on RequestBody of ' . $oDTRequestCurrent->get_path() . ': ' . $sMessage);
                Error::notice('abort validation of request due to error');
                $oDTValidateRequestResponse
                    ->set_bSuccess(false)
                    ->add_aMessage(
                        DTValidateMessage::create()
                            ->set_sSubject('Error')
                            ->set_sBody(json_last_error_msg())
                    )
                    ->add_aMessage(
                        DTValidateMessage::create()
                            ->set_sSubject('Notice')
                            ->set_sBody($sMessage)
                    );

                return $oDTValidateRequestResponse;
            }
        } catch (\Exception $oException) {
            Error::exception($oException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Exception')
                        ->set_sBody($oException->getMessage())
                );

            return $oDTValidateRequestResponse;
        }

        // OpenApiValidation
        try {
            $oOpenApiValidation = new OpenApiValidation($sYamlFileAbs);
        } catch (FileNotFoundException $oFileNotFoundException) {
            Error::exception($oFileNotFoundException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Exception')
                        ->set_sBody($oFileNotFoundException->getMessage())
                );

            return $oDTValidateRequestResponse;
        } catch (InvalidOptionException $oInvalidOptionException) {
            Error::exception($oInvalidOptionException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Exception')
                        ->set_sBody($oInvalidOptionException->getMessage())
                );

            return $oDTValidateRequestResponse;
        }

        // requirement: it has to be Psr7
        $oPsrRequest = new PsrRequest($oDTRequestCurrent);
        $aValidationResult = $oOpenApiValidation->validateRequest(
            // PSR7 Request Object
            $oPsrRequest,
            // path as expected in route
            Route::getCurrent()->get_path(),
            // Request Method; has to be lowercase
            strtolower(Route::getCurrent()->get_method()),
            // remove "_tail" from PathParam Array
            $oPsrRequest->withoutAttribute('_tail')
        );

        $oDTValidateRequestResponse
            ->set_bSuccess((true === empty($aValidationResult)))
            ->set_aValidationResult($aValidationResult);

        return $oDTValidateRequestResponse;
    }
}
