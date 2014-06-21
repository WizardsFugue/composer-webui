<?php

namespace Rabus\Composer\WebUI\Controller;

use Composer\Composer;
use Composer\Package\PackageInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxController
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @param Composer $composer
     */
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * @return Response
     */
    public function indexAction()
    {
        $repository = $this->composer->getRepositoryManager()->getLocalRepository();

        $result = array();
        
        $result['name'] = $this->composer->getPackage()->getName();

        $result['packages'] = array();
        foreach ($repository->getPackages() as $currentPackage) {
            /** @var PackageInterface $currentPackage */
            $result['packages'][] = array(
                'prettyName'    => $currentPackage->getPrettyName(),
                'prettyVersion' => $currentPackage->getPrettyVersion(),
            );
        }


        return JsonResponse::create($result);
    }
}
