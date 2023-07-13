<?php

namespace Abstractions;

use Interfaces\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * Class AbsResponse.
 *
 * Abstract implementation of ResponseInterface as a variant
 * of super DTO which should be used in a way of communication
 * between AbsService and Handler.
 *
 * @package Abstractions\AbsResponse
 * @author mmarkov mmarkov@team.amocrm.com
 */
class AbsResponse implements ResponseInterface
{
    /** @var array $data Response data */
    private array $data;

    /** @var int $code Response status code */
    private int $code;

    /** @var string $message Not mandatory message */
    private string $message;

    /** @var string $status Overall status of response */
    private string $status;

    /** @var array $httpCodes Dicionary of code to message relation */
    public const httpCodes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Checkpoint',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot', // :)
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
    );

    /**
     *
     * Constructor
     *
     * @param array $data Any response data
     * @param int $code HTTP response status code as RFC9110
     * https://datatracker.ietf.org/doc/html/rfc9110
     *
     * @param string $message Any message
     *
     */
    public function __construct(
        array $data,
        int $code,
        string $message = null,
        string $status = null
    )
    {
        $this->data = $data;
        $this->code = $code;
        is_null($message) ? $this->generateMessage($code) : $this->message = $message;
        is_null($status) ? $this->generateStatus($code) : $this->status = $status;
    }

    /**
     * Response as Json
     *
     * @return JsonResponse
     */
    public function asJson(): JsonResponse
    {
        return new JsonResponse([
                'status' => $this->status,
                'data' => $this->data,
                'code' => $this->code,
                'message' => $this->message,
            ]);
    }

    /**
     * Response as associative array
     *
     * @return array
     */
    public function asDictionary(): array
    {
        $result = array(
            'status' => $this->status,
            'data' => $this->data,
            'code' => $this->code,
            'message' => $this->message
        );

        return $result;
    }

    /**
     * Response as array
     *
     * @return array
     */
    public function asList(): array
    {
        $result = array(
            $this->status,
            $this->data,
            $this->code,
            $this->message
        );

        return $result;
    }

    /**
     * Function should generate message depends on status code.
     *
     * @param int $statusCode Status code
     * @return $this
     */
    private function generateMessage(int $statusCode): self
    {
        $result = '';
        if (array_key_exists($statusCode, $this::httpCodes)) {
            $result = $this::httpCodes[$statusCode];
        } else {
            $result = 'No default message for this status code.';
        }

        $this->message = $result;

        return $this;
    }

    /**
     * Function should generate status depends on status code.
     * 
     * @param int $statusCode Status code
     * @return $this
     */
    private function generateStatus(int $statusCode): self
    {
        $result = '';
        if (intdiv($statusCode, 100) !== 2) {
            $result = 'error';
        } else {
            $result = 'success';
        }

        $this->status = $result;
        return $this;
    }


    /**
     * String representation of response object
     *
     * @return string
     */
    public function __toString(): string
    {
        $containsData = $this->asDictionary();
        $result = '|' . get_class($this)
        . ' with data: ['. implode(',', $containsData['data'])
        . '] ; status code: '
        . $this->code . ' ; and message: ' . $this->message . '|';

        return $result;
    }
}
