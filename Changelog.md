# Changelog

- All notable changes to this project will be documented in this file.
- The format is based on [Keep a Changelog](https://keepachangelog.com/de/1.0.0/)
- This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Types of changes:

**added**  
**changed**
**deprecated**
**removed**  
**fixed**  
**security**

---

## [Unreleased]

### Backlog

### In Progress

**added**  

- `\OpenApi\Model\Route::autoCreateFromOpenApiFile`: auto-creating myMVC Routes from openapi file
- doctypes

**changed**

- Logs are fired to Events
- variable names
- "zircote/swagger-php": "4.7.10"
- "hkarlstrom/openapi-reader": "0.5"
- "hkarlstrom/openapi-validation-middleware": "0.5.2"

**deprecated**
**removed**  
**fixed**  
**security**

---

## [Releases]

### [1.0.0] - 2023-06-19, https://github.com/gueff/myMVC_module_OpenApi/releases/tag/1.0.0

**added**

- check the request content body type "json"
- try/catch blocks with error logs
- `\OpenApi\Model\Validate::request`: parameter `$sYamlFileAbs` may now be either a file or an URL; If URL, it will be cached locally according to `iDeleteAfterMinutes` Cache Settings or 1440 min if no Cache settings exists

**changed**

- check the request content type if there is any content body
- `\OpenApi\Model\Validate::request`: if $sYamlFileAbs is URL, its content is downloaded to cache as file

**fixed**

- `OpenApi/Model/Validate.php, Line: 118, Message: Call to a member function getContent() on null`
- `\OpenApi\Model\Validate::request`: if $sYamlSource is url its source is not properly saved to cache
- `\OpenApi\Model\Validate::request`: if $sYamlFileAbs is url it fails
