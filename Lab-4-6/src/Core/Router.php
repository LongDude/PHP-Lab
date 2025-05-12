<?php
    namespace src\Core;

    class Router {
        private array $routes = [];
        public function get(string $route, callable $callback, ?array $roles=null, ?string $redirect=null) : void {
            $this->routes['GET'][$route]['callback'] = $callback;
            if (isset($roles)){
                $this->routes['GET'][$route]['roles'] = $roles;     
                if (isset($redirect)){
                    $this->routes['GET'][$route]['redirect'] = $redirect;
                }
            }
        }
        
        public function post(string $route, callable $callback, ?array $roles=null, ?string $redirect=null) : void {
            $this->routes['POST'][$route]['callback'] = $callback;
            if (isset($roles)){
                $this->routes['POST'][$route]['roles'] = $roles;                
                if (isset($redirect)){
                    $this->routes['GET'][$route]['redirect'] = $redirect;
                }
            }
        }

        public function put(string $route, callable $callback, ?array $roles=null, ?string $redirect=null) : void {
            $this->routes['PUT'][$route]['callback'] = $callback;
            if (isset($roles)){
                $this->routes['PUT'][$route]['roles'] = $roles;                
                if (isset($redirect)){
                    $this->routes['GET'][$route]['redirect'] = $redirect;
                }
            }
        }
        
        public function resolve(): void {
            session_start();

            $method=$_SERVER['REQUEST_METHOD'];
            $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);

            // Страница не найдена
            if (empty($this->routes[$method][$path])){
                http_response_code(404);
                require __DIR__ . "/404.html";
                exit;
            }
            $route = $this->routes[$method][$path];

            // Недостаточно прав
            if (isset($route['roles']) && (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $route['roles']))){
                http_response_code(401);
                if (isset($route['redirect'])){
                    header("Location: ".$route['redirect']);
                } else {
                    require __DIR__ . "/401.html";
                }
                exit;
            }

            call_user_func($route['callback']);
        }
    }
?>