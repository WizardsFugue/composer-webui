<?php

namespace Rabus\Composer\WebUI\Controller;

use Composer\Composer;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Operation\SolverOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Rule;
use Composer\DependencyResolver\Solver;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\CompositeRepository;
use Composer\Repository\InstalledArrayRepository;
use Composer\Repository\PlatformRepository;
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
                'releaseDate'   => $currentPackage->getReleaseDate(),
                'license'       => $currentPackage->getLicense(),
            );
        }

        // not working yet
        //$result['update'] = $this->fetchUpdateableVersions();

        return JsonResponse::create($result);
    }
    
    protected function fetchUpdateableVersions()
    {
        $package = $this->composer->getPackage();

        $installedRootPackage = clone $package;
        $installedRootPackage->setRequires(array());
        $installedRootPackage->setDevRequires(array());
        // create installed repo, this contains all local packages + platform packages (php & extensions)
        $localRepo = $this->composer->getRepositoryManager()->getLocalRepository();
        $platformRepo = new PlatformRepository();
        $repos = array(
            $localRepo,
            new InstalledArrayRepository(array($installedRootPackage)),
            $platformRepo,
        );
        $installedRepo = new CompositeRepository($repos);
        
        $pool = new Pool($package->getMinimumStability(), $package->getStabilityFlags());
        $pool->addRepository($installedRepo);

        $request = $this->createRequest($pool, $package, $platformRepo);
        $request->updateAll();
            
            
        $solver = new Solver( new DefaultPolicy($package->getPreferStable()), $pool, $installedRepo);
        $ops = $solver->solve($request);
        $result = array();
        foreach ($ops as $op) {
            /** @var SolverOperation|UpdateOperation $op */
            $jobType = $op->getJobType();
            $target  = null;
            if ($op->getJobType() === 'update') {
                $target = $op->getTargetPackage()->getPrettyVersion();
            }elseif ($op->getJobType() === 'install') {
                $target = $op->getTargetPackage()->getPrettyVersion();
            }
            $result[] = array(
                'jobType' => $jobType,
                'target'  => $target,
                'message' => $op->__toString(),
            );
        }
        
        return $result;
        
    }

    private function createRequest(Pool $pool, RootPackageInterface $rootPackage, PlatformRepository $platformRepo)
    {
        $request = new Request($pool);

        $constraint = new VersionConstraint('=', $rootPackage->getVersion());
        $constraint->setPrettyString($rootPackage->getPrettyVersion());
        $request->install($rootPackage->getName(), $constraint);

        $fixedPackages = $platformRepo->getPackages();

        // fix the version of all platform packages + additionally installed packages
        // to prevent the solver trying to remove or update those
        $provided = $rootPackage->getProvides();
        foreach ($fixedPackages as $package) {
            $constraint = new VersionConstraint('=', $package->getVersion());
            $constraint->setPrettyString($package->getPrettyVersion());

            // skip platform packages that are provided by the root package
            if ($package->getRepository() !== $platformRepo
                || !isset($provided[$package->getName()])
                || !$provided[$package->getName()]->getConstraint()->matches($constraint)
            ) {
                $request->install($package->getName(), $constraint);
            }
        }

        return $request;
    }
}
