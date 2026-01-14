<?php

declare(strict_types=1);

namespace Zcc\Core;

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Http\Request;
use Zcc\Core\Http\Response;
use Zcc\Core\Http\Router;
use Zcc\Core\Security\Csrf;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

final class Kernel
{
    public function __construct(private readonly array $config)
    {
    }

    public function handle(Request $request): Response
    {
        $router = new Router();
        $view = new View(BASE_PATH . '/resources/views');
        $auth = new AuthManager($this->config['auth']);
        $theme = new ThemeManager();

        $router->get('/', static function () use ($view, $auth, $theme): Response {
            $content = $view->render('dashboard.php', [
                'user' => $auth->user(),
            ]);

            return new Response($view->render('layout.php', [
                'title' => 'Dashboard',
                'content' => $content,
                'menu' => require BASE_PATH . '/config/menu.php',
                'user' => $auth->user(),
                'theme' => $theme->current(),
            ]));
        });

        $router->get('/login', static function () use ($view, $theme): Response {
            $content = $view->render('login.php', [
                'csrf' => Csrf::token(),
            ]);

            return new Response($view->render('layout.php', [
                'title' => 'Login',
                'content' => $content,
                'menu' => [],
                'user' => null,
                'theme' => $theme->current(),
            ]));
        });

        $router->post('/login', static function (Request $request) use ($auth): Response {
            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            $username = (string) $request->input('username');
            $password = (string) $request->input('password');

            if (!$auth->attempt($username, $password)) {
                return Response::redirect('/login?error=1');
            }

            return Response::redirect('/');
        });

        $router->post('/logout', static function (Request $request) use ($auth): Response {
            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            $auth->logout();

            return Response::redirect('/login');
        });

        $router->post('/theme', static function (Request $request) use ($theme): Response {
            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            $choice = $request->input('theme');
            if (!in_array($choice, ['light', 'dark'], true)) {
                return new Response('Invalid theme', 400);
            }

            setcookie(ThemeManager::COOKIE_NAME, $choice, time() + 31536000, '/');

            return Response::redirect('/');
        });

        return $router->dispatch($request);
    }
}
