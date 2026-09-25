<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;
    protected $helpers = ['auth', 'form', 'url'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
        // Load language files
        $this->language = \Config\Services::language();
        $this->language->setLocale($this->request->getLocale());
    }

    public function initController1(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Check if user is logged in for protected pages
        $currentURI = $request->uri->getPath();
        $authPages = ['auth/login', 'auth/register', 'auth/forgot-password', 'auth/reset-password', 'auth/activate'];

        // If trying to access protected page without login
        if (!session()->has('user_id') && !in_array($currentURI, $authPages)) {
            return redirect()->to('/auth/login')->with('error', 'Please login to access this page.');
        }

        // If already logged in and trying to access auth pages
        if (session()->has('user_id') && in_array($currentURI, $authPages)) {
            return redirect()->to('/dashboard')->with('info', 'You are already logged in.');
        }
    }
}
