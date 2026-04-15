<?php

use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Contact;
use App\Models\ContactList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'campaign.draft' => \App\Http\Middleware\EnsureCampaignIsDraft::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $resolveModelNotFoundMessage = static function (?ModelNotFoundException $exception): ?string {
            if (!$exception) {
                return null;
            }

            return match ($exception->getModel()) {
                Contact::class => 'Contact not found.',
                ContactList::class => 'Contact list not found.',
                Campaign::class => 'Campaign not found.',
                CampaignSend::class => 'Campaign send not found.',
                default => 'Resource not found.',
            };
        };

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($resolveModelNotFoundMessage) {
            if (!$request->is('api/*')) {
                return null;
            }

            $message = $resolveModelNotFoundMessage($exception);

            return response()->json([
                'message' => $message,
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($resolveModelNotFoundMessage) {
            if (!$request->is('api/*')) {
                return null;
            }

            $message = $resolveModelNotFoundMessage($exception->getPrevious() instanceof ModelNotFoundException
                ? $exception->getPrevious()
                : null) ?? 'Resource not found.';

            return response()->json([
                'message' => $message,
            ], Response::HTTP_NOT_FOUND);
        });
    })->create();
