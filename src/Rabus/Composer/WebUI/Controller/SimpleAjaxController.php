<?php

namespace Rabus\Composer\WebUI\Controller;

use Composer\Command\ValidateCommand;
use Composer\Composer;
use Composer\IO\BufferIO;
use Composer\Package\PackageInterface;
use Composer\Util\ConfigValidator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SimpleAjaxController
 * 
 * a controller which is able to work without an composer Instance
 *
 * @package Rabus\Composer\WebUI\Controller
 */
class SimpleAjaxController
{

    /**
     * 
     */
    public function __construct()
    {
        
    }
    
    
    public function validateAction()
    {
        $result['validation'] = $this->getValidationOutput();

        return JsonResponse::create($result);
    }
    
    protected function getValidationOutput()
    {
        $io = new BufferIO();
        $configValidator = new ConfigValidator($io);
        $file = 'composer.json';
        list($errors, $publishErrors, $warnings) = $configValidator->validate($file);
        $info = array();
        if (!$errors && !$publishErrors && !$warnings) {
            $info[] = $file . ' is valid';
        } elseif (!$errors && !$publishErrors) {
            $info[] = $file . ' is valid, but with a few warnings';
            $warnings[] = 'See http://getcomposer.org/doc/04-schema.md for details on the schema';
        } elseif (!$errors) {
            $info[] = $file . ' is valid for simple usage with composer but has';
            $info[] = 'strict errors that make it unable to be published as a package:';
            $warnings[] = 'See http://getcomposer.org/doc/04-schema.md for details on the schema';
        } else {
            array_unshift($errors, $file . ' is invalid, the following errors/warnings were found:');
        }
        $validationResult = array(
            "errors"        => $errors,
            "publishErrors" => $publishErrors,
            "warnings"      => $warnings,
            "info"          => $info,
        );
        return $validationResult;
    }
    
    
    public function composerJsonAction()
    {
        $file = 'composer.json';
        $result = array(
            'file' => file_get_contents($file),
        );
        return JsonResponse::create($result);
    }
}
