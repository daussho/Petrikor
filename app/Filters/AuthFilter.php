<?php

namespace App\Filters;

use App\Helpers\GlobalHelper;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do something here
        $response = service('response');
        $apiKey = $request->getHeaderLine('x-api-key');

        if (!$request->hasHeader('x-api-key')) {
            $response->setStatusCode(401);
            return $response->setJSON([
                'message' => 'Api key not found'
            ]);
        }

        if ($apiKey != $_ENV['API_KEY']){
            $response->setStatusCode(401);
            return $response->setJSON([
                'message' => 'Invalid api key'
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
