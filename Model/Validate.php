<?php

namespace OpenApi\Model;

use HKarlstrom\Middleware\OpenApiValidation;
use MVC\DataType\DTRequestCurrent;
use MVC\Error;
use MVC\Request;
use MVC\Route;
use OpenApi\DataType\DTValidate;
use OpenApi\DataType\DTValidateMessage;
use OpenApi\DataType\DTValidateRequestResponse;

class Validate
{
    /**
     * @param \MVC\DataType\DTRequestCurrent|null $oDTRequestCurrent
     * @param                                     $sYamlFileAbs
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

        // yaml missing
        if (false === file_exists($sYamlFileAbs))
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

        // requirement: it has to be Psr7
        $oPsrRequest = new PsrRequest($oDTRequestCurrent);

        // OpenApiValidation
        $oOpenApiValidation = new OpenApiValidation($sYamlFileAbs);
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