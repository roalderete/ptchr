<?php

namespace OrangeHRM\ORM;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use OrangeHRM\Config\Config;
use OrangeHRM\Core\Traits\ServiceContainerTrait;
use OrangeHRM\Framework\Cache\FilesystemAdapter;
use OrangeHRM\Framework\Framework;
use OrangeHRM\Framework\Services;
use OrangeHRM\ORM\Exception\ConfigNotFoundException;
use OrangeHRM\ORM\Functions\TimeDiff;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class Doctrine
{
    use ServiceContainerTrait;

    /**
     * @var null|Doctrine
     */
    protected static ?Doctrine $instance = null;
    /**
     * @var null|EntityManager
     */
    protected static ?EntityManager $entityManager = null;

    /**
     * @throws ConfigNotFoundException
     */
    private function __construct()
    {
        $conf = Config::getConf();

        $isDevMode = $this->isDevMode();
        $proxyDir = Config::get(Config::DOCTRINE_PROXY_DIR);
        $cache = new ArrayAdapter();
        $paths = $this->getPaths();
        $config = ORMSetup::createAnnotationMetadataConfiguration(
            $paths,
            $isDevMode,
            $proxyDir,
            $cache
        );
        if (!$isDevMode) {
            $metadataCache = new FilesystemAdapter('doctrine_metadata', 0, Config::get(Config::CACHE_DIR));
            $queryCache = new FilesystemAdapter('doctrine_queries', 0, Config::get(Config::CACHE_DIR));
            $config->setMetadataCache($metadataCache);
            $config->setQueryCache($queryCache);
        }

        $config->setAutoGenerateProxyClasses(
            $isDevMode
                ? AbstractProxyFactory::AUTOGENERATE_ALWAYS
                : AbstractProxyFactory::AUTOGENERATE_NEVER
        );
        $config->addCustomStringFunction('TIME_DIFF', TimeDiff::class);

        $connectionParams = [
            'dbname' => $conf->getDbName(),
            'user' => $conf->getDbUser(),
            'password' => $conf->getDbPass(),
            'host' => $conf->getDbHost(),
            'port' => $conf->getDbPort(),
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4'
        ];

        self::$entityManager = EntityManager::create($connectionParams, $config);
        self::$entityManager->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * @return bool
     */
    private function isDevMode(): bool
    {
        try {
            /** @var Framework $kernel */
            $kernel = $this->getContainer()->get(Services::HTTP_KERNEL);
            return $kernel->isDebug();
        } catch (ServiceNotFoundException $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    private function getPaths(): array
    {
        $paths = [];
        $pluginPaths = Config::get('ohrm_plugin_paths');
        foreach ($pluginPaths as $pluginPath) {
            $entityPath = realpath($pluginPath . '/entity');
            if ($entityPath) {
                $paths[] = $entityPath;
            }
        }
        return $paths;
    }

    /**
     * @return Doctrine
     */
    protected static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager(): EntityManager
    {
        self::getInstance();
        return self::$entityManager;
    }
}
