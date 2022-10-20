<?php
#---------------------------------------------------------------
require_once realpath(__DIR__ . '/../../../../../') . '/application/config/util/bootstrap.php';
\MVC\Config::init(get($GLOBALS['aConfig'], array()));
\MVC\Cache::init(\MVC\Config::get_MVC_CACHE_CONFIG());
\MVC\Cache::autoDeleteCache('DataType', 0);

#---------------------------------------------------------------
#  Defining DataType Classes

// Classes created by this script are placed into folder:
// `/modules/{module}/DataType/`
// @see https://mymvc.ueffing.net/3.1.x/generating-datatype-classes

$sCurrentModuleName = basename(realpath(__DIR__ . '/../../../'));
$sDataTypeDir = realpath(__DIR__ . '/../../../') . '/DataType/';

// base setup
$aDataType = array(
    'dir' => $sDataTypeDir,
    'unlinkDir' => false
);

// classes
$aDataType['class'][] = array(
    'name' => 'DTValidateRequestResponse',
    'file' => 'DTValidateRequestResponse.php',
    'namespace' => $sCurrentModuleName . '\\DataType',
    'createHelperMethods' => true,
    'constant' => array(),
    'property' => array(
        array(
            'key' => 'bSuccess',
            'var' => 'bool',
            'value' => false,
            'required' => true,
            'forceCasting' => true,
        ),
        array(
            'key' => 'aMessage',
            'var' => 'DTValidateMessage[]',
            'required' => true,
            'forceCasting' => true,
        ),
        array(
            'key' => 'aValidationResult',
            'var' => 'array',
            'required' => true,
            'forceCasting' => true,
        ),
    ),
);

$aDataType['class'][] = array(
    'name' => 'DTValidateMessage',
    'file' => 'DTValidateMessage.php',
    'namespace' => $sCurrentModuleName . '\\DataType',
    'createHelperMethods' => true,
    'constant' => array(),
    'property' => array(
        array(
            'key' => 'sSubject',
            'var' => 'string',
            'required' => true,
            'forceCasting' => true,
        ),
        array(
            'key' => 'sBody',
            'var' => 'string',
            'required' => true,
            'forceCasting' => true,
        ),
    ),
);

#---------------------------------------------------------------
#  create!

\MVC\Generator\DataType::create(56)->initConfigArray($aDataType);