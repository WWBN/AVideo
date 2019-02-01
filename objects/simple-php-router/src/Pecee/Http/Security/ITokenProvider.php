<?php

namespace Pecee\Http\Security;

interface ITokenProvider
{

    /**
     * Refresh existing token
     */
    public function refresh();

    /**
     * Validate valid CSRF token
     *
     * @param string $token
     * @return bool
     */
    public function validate($token);

}