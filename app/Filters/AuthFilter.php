<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            // Check for Remember Me cookie
            if (isset($_COOKIE['remember_token'])) {
                $cookieValue = $_COOKIE['remember_token'];
                $parts = explode(':', $cookieValue);
                
                if (count($parts) === 2) {
                    $id = $parts[0];
                    $token = $parts[1];
                    
                    $model = new \App\Models\UserModel();
                    $user = $model->find($id);
                    
                    if ($user && $user['remember_token'] === $token) {
                        // Log user in
                        $ses_data = [
                            'id_code' => $user['id_code'],
                            'user_name' => $user['user_name'],
                            'name' => $user['name'] ?? $user['user_name'],
                            'role' => $user['role'],
                            'shift' => $user['shift'],
                            'isLoggedIn' => TRUE
                        ];
                        session()->set($ses_data);
                        return; // Allow request to proceed
                    }
                }
            }
            
            return redirect()->to('/login');
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
