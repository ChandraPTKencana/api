<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\PostTooLargeException;

use Illuminate\Auth\AuthenticationException;

use App\Exceptions\MyException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // $this->reportable(function (Throwable $e) {
        //     //
        // });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $rendered = parent::render($request, $exception);
        $status_code = $rendered->getStatusCode();

        // new line
        $debug = config('app.debug');
        // $message = 'Maaf Server Sedang Di tindak lanjuti harap kembali lagi nanti';
        $message = '';
        // cek jika eksepsinya dikarenakan model tidak ditemukan
        if ($exception instanceof ModelNotFoundException) {
          $message = 'Resource is not found';
        }
        // cek jika eksepsinya dikarenakan resource tidak ditemukan
        elseif ($exception instanceof NotFoundHttpException) {
          $message = 'Endpoint is not found';
        }
        // cek jika eksepsinya dikarenakan method tidak diizinkan
        elseif ($exception instanceof MethodNotAllowedHttpException) {
          $message = 'Method is not allowed';
        }
        // cek jika eksepsinya dikarenakan kegagalan validasi
        else if ($exception instanceof ValidationException) {
          return response()->json($exception->errors(), $status_code);
        }
        // cek jika eksepsinya dikarenakan kegagalan query
        else if ($exception instanceof QueryException) {
          if ($debug) {
            $message = $exception->getMessage();
          } else {
            $message = 'Query failed to execute';
          }
        }
        else if ($exception instanceof MyException) {
          $status_code = $exception->getCode() == 0 ? 400 : $exception->getCode();
          return response()->json($exception->getData(), $status_code);
        }
        // else if ($exception instanceof PostTooLargeException) {
        //   $status_code = 413;
        //   $message = 'File Too Large';
        // }
        // else if ($exception instanceof ParseError) {
        //   // $message = "Maaf Server Sedang Di tindak lanjuti harap kembali lagi nanti";
        // }
        if ( empty($message) ) {
          $message = $exception->getMessage();
        }
        $result =[
          'message' => $message,
        ];
        $errors = [];
        if ($debug) {
          $errors['message'] = $message;
          $errors['exception'] = get_class($exception);
          $errors['trace'] = explode("\n", $exception->getTraceAsString());
          $result['errors']=$errors;
        }
        return response()->json($result, $status_code);
    }

    protected function unauthenticated($request, AuthenticationException $exception){
      return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticate',
        'data' => null
      ], 401);
    }
}
