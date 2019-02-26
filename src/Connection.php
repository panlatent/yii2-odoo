<?php
/**
 * @link https://github.com/panlatent/yii2-odoo
 * @license http://opensource.org/licenses/MIT
 */

namespace panlatent\odoo;

use Graze\GuzzleHttp\JsonRpc\Client;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\helpers\Json;

/**
 * Odoo Connection
 *
 * The extension need Odoo 10.0 or higher.
 *
 * @package heqiauto\erp\components
 * @property-read Client $client
 * @property-read Db $db
 * @property-read Schema $schema
 * @property-read bool $isGuest
 * @property-read bool $isRequested
 * @property-read string $version
 * @property CacheInterface $cache
 * @property QueryBuilder $queryBuilder
 */
class Connection extends Component
{
    /**
     * Common service.
     */
    const COMMON = 'common';

    /**
     * Object service
     */
    const OBJECT = 'object';

    /**
     * Db service.
     */
    const DB = 'db';

    /**
     * Report service.
     */
    const REPORT = 'report';

    /**
     * @var string Odoo server dsn e.g. http://localhost:8000/jsonprc
     */
    public $dsn;

    /**
     * @var string Odoo available database.
     */
    public $database;

    /**
     * @var string Odoo username
     */
    public $username;

    /**
     * @var string Odoo password
     */
    public $password;

    /**
     * @var string|null JSON-RPC client options
     */
    public $options;

    /**
     * @var string
     */
    public $commandClass = Command::class;

    /**
     * @var string
     */
    public $cacheComponent = 'cache';

    /**
     * @var bool
     */
    public $enableAuthenticateCache = true;

    /**
     * @var int
     */
    public $authenticateCacheDuration = 7200;

    /**
     * @var bool
     */
    public $enableLogging = true;

    /**
     * @var bool
     */
    public $enableProfiling = true;

    /**
     * @var string
     */
    public $tablePrefix = '';

    /**
     * @var int|null
     */
    protected $uid;

    /**
     * @var bool
     */
    protected $requested = false;

    /**
     * @var Client|null
     */
    private $_client;

    /**
     * @var CacheInterface|null
     */
    private $_cache;

    /**
     * @var Context|null
     */
    private $_context;

    /**
     * @var Db|null
     */
    private $_db;

    /**
     * @var Schema|null
     */
    private $_schema;

    /**
     * @var string|null
     */
    private $_version;

    /**
     * @var QueryBuilder|null
     */
    private $_queryBuilder = QueryBuilder::class;

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if ($this->_client !== null) {
            return $this->_client;
        }

        return $this->_client = Client::factory($this->dsn, $this->options ?: []);
    }

    /**
     * @return Db
     */
    public function getDb(): Db
    {
        if ($this->_db !== null) {
            return $this->_db;
        }

        return $this->_db = new Db($this);
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        if ($this->_schema !== null) {
            return $this->_schema;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->_schema = Yii::createObject(['class' => Schema::class, 'odoo' => $this]);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getTableSchema(string $name)
    {
        return $this->getSchema()->getTableSchema($name);
    }

    /**
     * @return bool
     */
    public function getIsGuest(): bool
    {
        return !$this->uid;
    }

    /**
     * @return bool
     */
    public function getIsRequested(): bool
    {
        return $this->requested;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        if ($this->_version !== null) {
            return $this->_version;
        }

        $results = $this->sendCallRequest(static::COMMON, 'version', [], __METHOD__);

        return $this->_version = $results['server_version'];
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        if ($this->_context !== null) {
            return $this->_context;
        }

        return $this->_context = new Context();
    }

    /**
     * @param Context|array|string $context
     */
    public function setContext($context)
    {
        if (is_array($context) && !isset($context['class'])) {
            $context['class'] = Context::class;
        }

        if (!$context instanceof Context) {
            $context = Yii::createObject($context);
        }

        $this->_context = $context;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        if (is_object($this->_queryBuilder)) {
            return $this->_queryBuilder;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->_queryBuilder = Yii::createObject($this->_queryBuilder, [$this]);
    }

    /**
     * @param QueryBuilder|array|string|null $queryBuilder
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->_queryBuilder = $queryBuilder;
    }

    /**
     * @return CacheInterface|null
     */
    public function getCache()
    {
        if ($this->_cache instanceof CacheInterface) {
            return $this->_cache;
        }

        if ($this->_cache) {
            $cache =  Yii::createObject($this->_cache ?: $this->cacheComponent);
            if (!$cache instanceof CacheInterface) {
                throw new InvalidConfigException('Cache component must be implements yii\caching\CacheInterface');
            }
        } else {
            $cache = Yii::$app->get($this->cacheComponent);
        }

        return $this->_cache = $cache;
    }

    /**
     * @param CacheInterface|array|string|null $cache
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
    }

    /**
     * Creates a command for execution.
     *
     * @param array $domain
     * @param array $keywords
     * @param string|null $modelName
     * @return Command|mixed
     */
    public function createCommand(array $domain, array $keywords = [], string $modelName = null)
    {
        return Yii::createObject($this->commandClass, [
            [
                'odoo' => $this,
                'modelName' => $modelName,
                'domain' => $domain,
                'keywords' => $keywords,
            ]
        ]);
    }

    /**
     * Authenticate Odoo JSON-RPC client.
     *
     * @param string $database
     * @param string $username
     * @param string $password
     * @return int
     */
    public function authenticate(string $database, string $username, string $password)
    {
        $uid = false;
        if ($this->enableAuthenticateCache) {
            if ($this->getCache()->exists(__METHOD__)) {
                $uid = $this->getCache()->get(__METHOD__);
            }
        }

        $message = "Authenticate Odoo database $database with $username";
        $uid and $message .= ' (using cache)';

        $enableProfiling = $this->enableLogging && $this->enableProfiling;

        if ($enableProfiling) {
            Yii::beginProfile($message,  __METHOD__);
        }

        if (!$uid) {
            $result = $this->sendCallRequest(static::COMMON, 'authenticate', [$database, $username, $password, []], false);
            $uid = (int)$result;

            if ($this->enableAuthenticateCache) {
                $this->getCache()->set(__METHOD__, $uid, $this->authenticateCacheDuration);
            }
        }

        $this->enableLogging and Yii::info($message, __METHOD__);

        if ($enableProfiling) {
            Yii::endProfile($message, __METHOD__);
        }

        return $uid;
    }

    /**
     * Execute Odoo JsonRPC execute method
     *
     * @param string $model
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function execute(string $model, string $method, array $params)
    {
        if ($this->getIsGuest()) {
            $this->uid = $this->authenticate($this->database, $this->username, $this->password);
        }

        $args = array_merge([
            $this->database,
            $this->uid,
            $this->password,
            $model,
            $method
        ], $params);

        return $this->sendCallRequest(static::OBJECT, 'execute', $args, __METHOD__);
    }

    /**
     * @param string $model
     * @param string $method
     * @param array $params
     * @param array $keywords
     * @return mixed
     */
    public function executeKw(string $model, string $method, array $params, array $keywords = [])
    {
        if ($this->getIsGuest()) {
            $this->uid = $this->authenticate($this->database, $this->username, $this->password);
        }

        $args = [
            $this->database,
            $this->uid,
            $this->password,
            $model,
            $method,
            $params,
            $keywords,
        ];

        return $this->sendCallRequest(static::OBJECT, 'execute_kw', $args, __METHOD__);
    }

    /**
     * @param string $model
     * @param array $attributes
     * @return mixed
     */
    public function fieldsGet(string $model, array $attributes = ['string', 'type', 'help'])
    {
        return $this->executeKw($model, 'fields_get', [], compact($attributes));
    }

    /**
     * @param string $model
     * @param array $domain
     * @param array $keywords
     * @return int[]
     */
    public function search(string $model, array $domain = [], array $keywords = []): array
    {
        return $this->executeKw($model, 'search', [$domain], $keywords) ?? [];
    }

    /**
     * @param string $model
     * @param array $domain
     * @return mixed
     */
    public function searchCount(string $model, array $domain = [])
    {
        return $this->executeKw($model, 'search_count', [$domain]);
    }

    /**
     * @param string $model
     * @param array $domain
     * @param array $keywords
     * @return mixed
     */
    public function searchRead(string $model, array $domain = [], array $keywords = [])
    {
        return $this->executeKw($model, 'search_read', [$domain], $keywords);
    }

    /**
     * @param string $model
     * @param int[]|int $ids
     * @param array $keywords
     * @return array
     */
    public function read(string $model, array $ids, array $keywords = []): array
    {
        return $this->executeKw($model, 'read', [$ids], $keywords);
    }

    /**
     * Execute Odoo JSON-RPC call method.
     *
     * @param string $service
     * @param string $method
     * @param array $args
     * @param string|bool $logCategory
     * @return mixed
     * @throws Exception
     */
    public function sendCallRequest(string $service, string $method, array $args, $logCategory = __METHOD__)
    {
        if (!$this->requested) {
            $this->enableLogging and Yii::info("Opening JSON-RPC connection: " . $this->dsn, __CLASS__ . '::open'); // Only log first request, not mean "keep connection".
            $this->requested = true;
        }

        $requestId = mt_rand();
        $params = compact('service', 'method', 'args');
        $params['context'] = $this->getContext()->toArray();

        list($enableProfiling, $token) = $this->logRequest($params, $logCategory);
        if ($enableProfiling) {
            Yii::beginProfile($token, $logCategory);
        }

        $response = $this->getClient()->send($this->getClient()->request($requestId, 'call', $params));

        if ($enableProfiling) {
            Yii::endProfile($token, $logCategory);
        }

        if ($response->getStatusCode() != 200) {
            throw new Exception($response->getRpcErrorMessage(), $response->getRpcErrorCode());
        }

        $result = $response->getRpcResult();

        return $result;
    }

    /**
     * @param array $params
     * @param string|bool $category
     * @return array
     */
    protected function logRequest(array $params, $category)
    {
        if (!$this->enableLogging || $category === false) {
            return [false, []];
        }

        if ($category === 'panlatent\\odoo\\Connection::sendCallRequest') {
            $messages = sprintf("Send request: %s",
                Json::encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $messages = Json::encode($params['args'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        Yii::info($messages, $category);

        return [$this->enableProfiling, $messages];
    }
}