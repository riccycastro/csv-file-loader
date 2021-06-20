<?php

namespace App\Message\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Throwable;

class MessengerBusMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * MessengerBusMiddleware constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Envelope $envelope
     * @param StackInterface $stack
     * @return Envelope
     * @throws Throwable
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /**
         * The registered middleware are called twice during the message lifecycle:
         *  - when the message is dispatched
         *  - when the message is handled
         * This validation make sure that our code is introduced in the second case
         */
        if (null !== $envelope->last(ReceivedStamp::class)) {
            try {
                $envelope = $stack->next()->handle($envelope, $stack);
                echo "## " . date('Y-m-d H:i:s') . ": Message \"" . get_class($envelope->getMessage()) . "\" consumed successfully \n";
                return $envelope;
            } catch (Throwable $exception) {

                $this->logger->critical(self::class . ' :: ' . get_class($envelope->getMessage()), [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'trace' => $exception->getTraceAsString(),
                ]);

                throw $exception;
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
