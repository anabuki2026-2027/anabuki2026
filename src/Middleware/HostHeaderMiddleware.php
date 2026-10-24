<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * 本番で公開したときに、ニセのアドレスからのアクセスをはじく「警備員」。
 * 開発中は何もしない。
 *
 * 【担当】A チーム
 * 【くわしく】docs/FILES.md
 */

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HostHeaderMiddleware implements MiddlewareInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Configure::read('debug')) {
            return $handler->handle($request);
        }

        $fullBaseUrl = Configure::read('App.fullBaseUrl');
        if (!$fullBaseUrl) {
            throw new InternalErrorException(
                'SECURITY: App.fullBaseUrl is not configured. ' .
                'This is required in production to prevent Host Header Injection attacks. ' .
                'Set APP_FULL_BASE_URL environment variable or configure App.fullBaseUrl in config/app.php',
            );
        }

        $configuredHost = parse_url($fullBaseUrl, PHP_URL_HOST);
        $requestHost = $request->getUri()->getHost();

        if ($configuredHost && $requestHost && strtolower($configuredHost) !== strtolower($requestHost)) {
            throw new BadRequestException(
                'Invalid Host header. Request host does not match configured application host.',
            );
        }

        return $handler->handle($request);
    }
}
