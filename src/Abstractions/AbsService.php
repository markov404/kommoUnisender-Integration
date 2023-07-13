<?php

namespace Abstractions;

use Abstractions\Types\SetOfModelManagers;
use Abstractions\AbsResponse;

use Exception;
use Interfaces\ResponseInterface;
use Interfaces\ServiceInterface;
use Interfaces\LoggerInterface;

use Config\ValidationTraitsConfig;
use Throwable;

use Laminas\Diactoros\Response\JsonResponse;
use Utils\Utils;


/**
 * Class AbsService
 *
 * Abstract implementation of ServiceInterface, BASE class
 * of ServiceInterface should be used for encapsulation buisiness logic
 * in it.
 *
 * @package Abstractions\AbsService
 * @author mmarkov mmarkov@team.amocrm.com
 */
class AbsService implements ServiceInterface
{
    /** @var ResponseInterface $response Contains response object */
    protected ResponseInterface $response;

    /** @var LoggerInterface $serviceLogger Contains logger object */
    private LoggerInterface $serviceLogger;

    /** @var SetOfModelManagers $setOfModelManagers An associative array */
    protected SetOfModelManagers $setOfModelManagers;

    /** @var JsonResponse $laminasJsonResponse */
    protected JsonResponse $laminasJsonResponse;

    /** @var string $childService */
    protected string $childService;

    /** @var array $traitList */
    private array $traitList;

    /**
     * Constructor for AbsService
     * 
     * If you are not passing logger in time with making new instance
     * of Service, make sure u call setLogger() method right after creation
     * new instance (before using service).
     *
     * @param LoggerInterface $logger Object of LoggerInterface
     * @param SetOfModelManagers $modelManagers
     * @param string $child
     */
    public function __construct(
        LoggerInterface $logger = null,
        SetOfModelManagers $modelManagers = null,
        string $child = null,
        ValidationTraitsConfig $traitsConfig = null)
    {
        if (!is_null($logger)) {
            $this->serviceLogger = $logger;
        }

        if (!is_null($modelManagers)) {
            $this->setOfModelManagers = $modelManagers;
        }

        if (!is_null($child)) {
            $this->childService = $child;
        }

        if (!is_null($traitsConfig)) {
            $this->traitList = $traitsConfig->getTraits();
        }
    }

    /**
     * Function should be extend in child class.
     * In abstract state it is only logging data.
     *
     * @param array An array of input data.
     * @return void
     */
    public function execute(array $dataList = array()): void
    {
        $msg = $this->makeLogMessageFromInputArray($dataList);

        if (!isset($this->serviceLogger)) {
            throw new Exception('You should inject logger into your service before using it.');
        } else {
            $this->serviceLogger->info($msg);
        }

        if (!isset($this->childService)) { return; }

        if (!is_null($this->childService)) {
            foreach ($this->traitList as $trait) {
                if (in_array(
                    $trait,
                    array_keys((new \ReflectionClass($this->childService))->getTraits())
                )) {
                    try {
                        $method = get_class_methods($trait)[0];
                        $properties = (new \ReflectionClass($trait))->getStaticProperties();
                        $message = reset($properties);

                        $boolResponse = call_user_func($trait . '::' . $method);
                    } catch (Throwable $e) {
                        throw new Exception(
                            $trait . ' trait should hav only one static method' .
                            ' which returns BOOL, and one public static propertie which ' .
                            'has unique name and contains message if validation is false'
                        );
                    }

                    if (!$boolResponse) {
                        $this->makeResponseObject([
                            'info' => $message
                        ], 401);
                        return;
                    };
                }
            }
        }
    }

    /**
     * Returning true if response is 5.. code
     *
     * @return bool
     */
    public function isError(): bool
    {
        $result = false;
        if (isset($this->response)) {
            $code = intval($this->response->asDictionary()['code']);
            if (intdiv($code, 100) === 5) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Returning laminas JsonResponse if it is exists.
     * 
     * @return ?JsonResponse
     */
    public function getLaminasJsonResponse(): ?JsonResponse
    {
        return $this->laminasJsonResponse;
    }

    /**
     * Returning response
     *
     * @return ?ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * Returning is response set
     * 
     * @return bool
     */
    public function isResponseSet(): bool
    {
        return isset($this->response);
    }

    /**
     * Set logger object
     *
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->serviceLogger = $logger;

        return $this;
    }

    /**
     * Set managers list
     *
     * @param SetOfModelManagers $logger
     * @return $this
     */
    public function setModelManagersList(SetOfModelManagers $managersList): self
    {
        $this->setOfModelManagers = $managersList;

        return $this;
    }

    /**
     * Set TraitsConfig
     * 
     * @param ValidationTraitsConfig $traitsConfig
     * @return $this
     */
    public function setTraitsConfig(ValidationTraitsConfig $validationTraitsConfig): self 
    {
        $this->traitList = $validationTraitsConfig->getTraits();

        return $this;
    }

    /**
     * Processing array into new string for logging
     *
     * @param array $data
     * @return string
     */
    private function makeLogMessageFromInputArray(array $data): string
    {
        $output = '[';
        if (Utils::arrayIsList($data)) {
            foreach ($data as $item) {
                $output = $output . strval($item) . ",";
            }
        } else {
            foreach ($data as $key=>$value) {
                $cuurentPair = "(" . strval($key)
                . "=>" . strval($value) . ")";
                $output = $output . strval($cuurentPair) . ",";
            }
        }

        if (substr($output, -1) == ',') {
            $output = substr_replace($output, "", -1);
        }

        $output = $output . ']';

        return $output;
    }

    /**
     * Creating response object
     *
     * @param array $data
     * @param int $code
     * @param string $message
     *
     * @return $this
     */
    protected function makeResponseObject(
        array $data,
        int $code,
        string $message = null
    ): self
    {
        if (intdiv($code, 100) === 5) {
            $this->serviceLogger->error(
                'Something went wrong, creating response with '
                . strval($code) . ' status code.'
            );

            $this->response = new AbsResponse($data, $code, $message);

            $this->serviceLogger->error('Created response object => ' . $this->response);
        } elseif (intdiv($code, 100) === 4) {
            $this->serviceLogger->warning(
                'Client side error, creating response with ' . strval($code) . ' status code.'
            );

            $this->response = new AbsResponse($data, $code, $message);

            $this->serviceLogger->warning('Created response object => ' . $this->response);
        } else {
            $this->serviceLogger->info(
                'Creating response with ' . strval($code) . ' status code.'
            );

            $this->response = new AbsResponse($data, $code, $message);

            $this->serviceLogger->info('Created response object => ' . $this->response);
        }

        return $this;
    }

    /**
     * Validationg request parameters
     * 
     * @param array $dataList
     * @return bool
     */
    protected function validateRequestParams(
        array $dataList, array $mandatoryParams) : bool 
    {
        for ($i = 0; $i < count($mandatoryParams); $i++) {
            if (!array_key_exists($mandatoryParams[$i], $dataList)) {
                return false;
            } else {
                if ($dataList[$mandatoryParams[$i]] === '') {
                    return false;
                } else {
                    continue;
                };
            }
        }

        return true;
    }

    /**
     * For making log message with status error by yourself
     * 
     * @param string $message
     * @return void
     */
    protected function writeDebugLogLine(string $message): void
    {
        $this->serviceLogger->debug($message);
    }

    /**
     * For making log message with status error by yourself
     * 
     * @param string $message
     * @return void
     */
    protected function writeErrorLogLine(string $message): void
    {
        $this->serviceLogger->error($message);
    }
}
