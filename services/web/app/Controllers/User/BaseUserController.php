<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseUserController extends BaseController
{
    protected int $userId;
    protected $currentUser;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        if (auth()->loggedIn()) {
            $this->currentUser = auth()->user();
            $this->userId = (int) auth()->id();
        }
    }
}
