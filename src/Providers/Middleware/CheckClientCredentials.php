<?php

namespace LaravelSsoClient\Providers\Middleware;

use Closure;
use LaravelSsoClient\Exceptions\MissingScopeException;
use Illuminate\Auth\AuthenticationException;
use LaravelSsoClient\JWT;

class CheckClientCredentials
{
    /**
     * JWT.
     *
     * @param  LaravelSsoClient\JWT  $jwt
     */
    protected JWT $jwt;

    /**
     * Create a new middleware instance.
     *
     * @param  LaravelSsoClient\JWT  $jwt
     * @return void
     */
    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $this->validate($request, $scopes);

        return $next($request);
    }

    /**
     * Validate the scopes and jwt on the incoming request.
     *
     * @param  array  $scopes
     * @return true
     *
     * @throws \LaravelSsoClient\Exceptions\|\Illuminate\Auth\AuthenticationException
     */
    public function validate($scopes)
    {
        $this->validateClaims();

        $this->validateScopes($scopes);

        return true;
    }

    /**
     * Validate jwt claims.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function validateClaims()
    {
        if (!$this->jwt->isValid() || empty($this->jwt->getClaims())) {
            throw new AuthenticationException;
        }
    }

    /**
     * Validate jwt scopes.
     *
     * @param  array  $scopes
     * @return void
     *
     * @throws \LaravelSsoClient\Exceptions\MissingScopeException
     */
    protected function validateScopes($scopes)
    {
        if ('*' == $scopes || in_array('*', $jwtScope = $this->jwt->getScope())) {
            return;
        }

        foreach ($scopes as $scope) {
            if (!in_array($scope, $jwtScope)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
