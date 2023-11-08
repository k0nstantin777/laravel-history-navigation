<?php

namespace RodrigoPedra\HistoryNavigation\Http\Middleware;

use Illuminate\Http\Request;
use RodrigoPedra\HistoryNavigation\HistoryNavigationService;
use Symfony\Component\HttpFoundation\Response;

class TrackHistoryNavigation
{
    public function __construct(
        private readonly HistoryNavigationService $historyService,
    ) {}

    public function handle(Request $request, \Closure $next): Response
    {
        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        return \tap($next($request), function (Response $response) use ($request) {
            if ($response->isSuccessful() || $response->isEmpty()) {
                $this->historyService->push($request->fullUrl())->persist();
            }
        });
    }

    private function shouldIgnore(Request $request): bool
    {
        return ! $request->isMethod('GET')
            || $request->ajax()
            || $request->wantsJson();
    }
}
