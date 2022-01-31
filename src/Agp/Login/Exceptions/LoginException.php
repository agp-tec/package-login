<?php

namespace Agp\Login\Exceptions;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class LoginException extends Exception
{
    /**
     * The validator instance.
     */
    private $response;
    private $status;

    /**
     * Create a new exception instance.
     *
     * @return void
     */
    public function __construct($response = null, $status = 200)
    {
        parent::__construct('The given data was invalid.');

        $this->response = $response;
        $this->status = $status;
    }

    public function render($request)
    {
        return response()->json($this->response, $this->status);
    }
}
