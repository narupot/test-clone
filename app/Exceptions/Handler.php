<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        /*\Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,*/
    ];



    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function register()
    {
        //parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
   /* public function render($request, Exception $exception) {

        if ($exception instanceof AuthenticationException) {
            return redirect()->action('Auth\RegisterController@login');
        }
        
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {

            $admin_url = getConfigValue('ADMIN_URL_KEY');

            if(str_contains(request()->path(),$admin_url)){
                return response()->view('errors.admin.404', [], 404);
            } else {
                return response()->view('errors.404', [], 404);
            }
        }

        // custom error message for syntax error, variable undefined, etc
        // if ($exception instanceof \ErrorException && config('app.debug') === false) {
        //     return response()->view('errors.500', [], 500);
        // }            

        return parent::render($request, $exception); 
    }*/

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    /*protected function unauthenticated($request, AuthenticationException $exception)
    {

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }*/
}
