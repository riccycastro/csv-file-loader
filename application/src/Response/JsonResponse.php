<?php

namespace App\Response;

class JsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse
{
    public const RESPONSE_MESSAGE = 'message';
    public const RESPONSE_RESULT = 'result';
    public const RESPONSE_ERRORS = 'errors';

    public const RESULT_OK = 'ok';
    public const RESULT_ERROR = 'error';

    public function __construct(int $status = 200, array $data = null)
    {
        if (!$data) {
            $data = [
                self::RESPONSE_MESSAGE => null,
                self::RESPONSE_RESULT => self::RESULT_OK,
                self::RESPONSE_ERRORS => [],
            ];
        }

        parent::__construct($data, $status);
    }

    /**
     * @param array $errors
     * @return JsonResponse
     */
    public function setErrors(array $errors): JsonResponse
    {
        $this->persistContent(self::RESPONSE_ERRORS, $errors);
        return $this;
    }

    /**
     * @param string $result
     * @return JsonResponse
     */
    public function setResult(string $result): JsonResponse
    {
        $this->persistContent(self::RESPONSE_RESULT, $result);
        return $this;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(string $message): JsonResponse
    {
        $this->persistContent(self::RESPONSE_MESSAGE, $message);
        return $this;
    }

    /**
     * @param string $key
     * @param $value
     */
    private function persistContent(string $key, $value)
    {
        $content = json_decode($this->getContent(), true);
        $content[$key] = $value;
        $this->setContent(json_encode($content));
    }
}
