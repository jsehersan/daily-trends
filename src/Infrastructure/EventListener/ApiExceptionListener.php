<?php

namespace App\Infrastructure\EventListener;

use App\Domain\Exception\DomainException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ApiExceptionListener
{
    public function __construct(
            // Inyectamos el prefijo que hemos configurado para detectar request de API para retonar JSON
        #[Autowire('%app.api_root%')]
        private string $apiPrefix
    ) {
    }
    public function onKernelException(ExceptionEvent $event): void
    {

        $request = $event->getRequest();

        // Solo seguimos si la peticion es json (configurado en routes.yaml)
        $isApiUrl = str_starts_with($request->getPathInfo(), $this->apiPrefix);

        $isJsonRequest = $request->getRequestFormat() === 'json' || str_contains($request->headers->get('Accept', ''), 'json');

        if (!$isApiUrl && !$isJsonRequest) {
            return;
        }

        $exception = $event->getThrowable();

        // Valores por defecto (Error 500)
        $statusCode = 500;
        $type = 'server_error';
        $message = 'Internal Server Error';
        $details = null;

        // Primer caso: que sea una excepción HTTP
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $type = 'http_error';
            $message = $exception->getMessage();

            // Si es un error de validación, extraemos los detalles
            $previous = $exception->getPrevious();
            if ($previous instanceof ValidationFailedException) {
                $type = 'validation_error';
                $statusCode = 422; // Bad Request
                $message = 'Validation failed';
                $details = $this->formatValidationErrors($previous);
            }
        }
        // Segundo caso: excepciones de Dominio
        elseif ($exception instanceof DomainException) {
            $statusCode = $exception->getCode() ?: 400;
            $type = 'business_error';
            $message = $exception->getMessage();
        }

        //Montamos una respuesta unificada
        //Todo: que sea un DTO para mantener la coherencia, de momento se queda en array.
        $data = [
            'status' => 'error',
            'type' => $type,
            'code' => $statusCode,
            'message' => $message,
        ];

        if ($details) {
            $data['details'] = $details;
        }

        // En modo desarrollo, añadimos más info para debug
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'dev') {
            $data['debug'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $this->formatTrace($exception->getTrace()),
            ];
        }

        $event->setResponse(new JsonResponse($data, $statusCode));
    }

    private function formatValidationErrors(ValidationFailedException $exception): array
    {
        $errors = [];
        foreach ($exception->getViolations() as $violation) {
            $field = str_replace(['[', ']'], '', $violation->getPropertyPath());
            $errors[$field] = $violation->getMessage();
        }
        return $errors;
    }
    private function formatTrace(array $trace): array
    {
        return array_map(function ($step) {
            return [
                'file' => $step['file'] ?? 'unknown',
                'line' => $step['line'] ?? 0,
                'function' => $step['function'] ?? 'unknown',
                'class' => $step['class'] ?? null,
                'type' => $step['type'] ?? null,
                // 'args' => $step['args'] ?? [], 
            ];
        }, $trace);
    }
}