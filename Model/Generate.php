<?php

namespace OpenApi\Model;

use cebe\openapi\Reader;
use MVC\Config;
use MVC\Convert;
use MVC\DataType\DTArrayObject;
use MVC\DataType\DTKeyValue;
use MVC\Debug;
use MVC\File;
use MVC\Request;
use Webbixx\Controller\Api;

class Generate
{
    /**
     * @param $sOpenApiFile
     * @return false|mixed
     */
    public static function getYaml($sOpenApiFile = '')
    {
        if ('' === $sOpenApiFile)
        {
            return false;
        }

        $sOpenApiFileContent = file_get_contents($sOpenApiFile);
        $aYaml = \Symfony\Component\Yaml\Yaml::parse($sOpenApiFileContent);

        return $aYaml;
    }

    /**
     * @param array $aYaml
     * @return mixed|null
     */
    public static function getAllSchemas(array $aYaml = array())
    {
        $aSchema = get($aYaml['components']['schemas']);

        return $aSchema;
    }

    /**
     * @example
     * Generate::dataTypeClasses(
     *      'https://raw.githubusercontent.com/OAI/OpenAPI-Specification/main/examples/v3.0/petstore.yaml',
     *      'PET'
     * );
     * @param $sOpenApiFile
     * @param $sSubDirName
     * @param $bUnlinkDir
     * @return void
     * @throws \ReflectionException
     */
    public static function dataTypeClasses($sOpenApiFile = '', $sSubDirName = '', $bUnlinkDir = true)
    {
        $aYaml = self::getYaml($sOpenApiFile);
        $aSchema = self::getAllSchemas($aYaml);

        $sNamespace = substr(
            str_replace('/', '\\', str_replace(Config::get_MVC_MODULES_DIR(), '', Config::get_MVC_MODULE_CURRENT_DATATYPE_DIR())
            . (('' !== $sSubDirName) ? '/' . $sSubDirName : ''))
            , 1
        );

        #---------------------------

        // base setup
        $aDataType = array(
            'dir' => File::secureFilePath(Config::get_MVC_MODULE_CURRENT_DATATYPE_DIR() . '/' . $sSubDirName),
            'unlinkDir' => $bUnlinkDir
        );

        foreach ($aSchema as $sName => $aValue)
        {
            $aDataType['class'][$sName] = array(
                'name' => $sName,
                'file' => $sName . '.php',
                'namespace' => $sNamespace,
                'createHelperMethods' => true,
                'constant' => array(),
            );

            $aProperty = get($aValue['properties'], []);

            foreach ($aProperty as $sPropertyName => $aPropertySpecs)
            {
                $mVar = get($aPropertySpecs['type']);
                ('boolean' === $mVar) ? $mVar = 'bool' : false;
                ('integer' === $mVar) ? $mVar = 'int' : false;
                $mValue = get($aPropertySpecs['example']);

                if (gettype($mValue) != gettype($mVar))
                {
                    $mValue = null;
//                    $aDataType['class'][$sName]['property'][$sPropertyName]['value'] = $mValue;
                }

                $sRef = get($aPropertySpecs['$ref']);
                $sItems = get($aPropertySpecs['items']);
                $sItemsRef = get($aPropertySpecs['items']['$ref']);

                // var is type $ref
                if (null !== $sRef)
                {
                    $sNameOfRef = current(array_reverse(explode('/', $sRef)));
                    $mVar = '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sNameOfRef;
                    $mValue = "$mVar::create()";
//                    $aDataType['class'][$sName]['property'][$sPropertyName]['value'] = $mValue;
                }

                // var is array of type $ref
                if ($mVar === 'array' && null !== $sItemsRef)
                {
                    $sNameOfRef = current(array_reverse(explode('/', $sItemsRef)));
                    $mVar = '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sNameOfRef . '[]';
                    $mValue = '$this->add_' . $sPropertyName . '(' . '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sNameOfRef . '::create());';
//                    $aDataType['class'][$sName]['property'][$sPropertyName]['value'] = $mValue;
                }

                $aDataType['class'][$sName]['property'][$sPropertyName]['key'] = $sPropertyName;
                $aDataType['class'][$sName]['property'][$sPropertyName]['var'] = $mVar;
                $aDataType['class'][$sName]['property'][$sPropertyName]['value'] = $mValue;
                $aDataType['class'][$sName]['property'][$sPropertyName]['required'] = true;
                $aDataType['class'][$sName]['property'][$sPropertyName]['forceCasting'] = true;
            }
        }

//        Debug::display($aDataType);

        \MVC\Generator\DataType::create(56)->initConfigArray($aDataType);
    }

    protected static function getSchemaItem($sItemName = '')
    {

    }
}