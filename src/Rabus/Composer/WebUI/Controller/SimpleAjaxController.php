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

    protected $composerFile;
    
    /**
     * 
     */
    public function __construct( $composerFile )
    {
        $this->composerFile = $composerFile;
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
        list($errors, $publishErrors, $warnings) = $configValidator->validate($this->composerFile);
        $info = array();
        if (!$errors && !$publishErrors && !$warnings) {
            $info[] = $this->composerFile . ' is valid';
        } elseif (!$errors && !$publishErrors) {
            $info[] = $this->composerFile . ' is valid, but with a few warnings';
            $warnings[] = 'See http://getcomposer.org/doc/04-schema.md for details on the schema';
        } elseif (!$errors) {
            $info[] = $this->composerFile . ' is valid for simple usage with composer but has';
            $info[] = 'strict errors that make it unable to be published as a package:';
            $warnings[] = 'See http://getcomposer.org/doc/04-schema.md for details on the schema';
        } else {
            array_unshift($errors, $this->composerFile . ' is invalid, the following errors/warnings were found:');
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
        $result = array(
            'file' => file_get_contents($this->composerFile),
            'filepath' => realpath($this->composerFile),
        );
        return JsonResponse::create($result);
    }
}
