<?php

declare(strict_types=1);

final class App
{
    public function __construct(private readonly EntitlementAnalysisService $service)
    {
    }

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if ($method === 'GET' && $path === '/') {
            JsonResponse::send([
                'service' => 'entitlement-request-portal-api',
                'status' => 'ok',
                'docs' => '/docs',
            ]);
            return;
        }

        if ($method === 'GET' && $path === '/docs') {
            header('Content-Type: text/html; charset=utf-8');
            echo $this->docsHtml();
            return;
        }

        if ($method === 'GET' && $path === '/api/dashboard/summary') {
            JsonResponse::send([
                'summary' => $this->service->summary(),
                'requests' => $this->service->requests(),
            ]);
            return;
        }

        if ($method === 'GET' && $path === '/api/sample') {
            JsonResponse::send([
                'requests' => $this->service->requests(),
            ]);
            return;
        }

        if ($method === 'GET' && preg_match('#^/api/requests/([^/]+)$#', $path, $matches) === 1) {
            $request = $this->service->requestById($matches[1]);
            if ($request === null) {
                JsonResponse::send(['error' => 'Request not found'], 404);
                return;
            }

            JsonResponse::send($request);
            return;
        }

        if ($method === 'POST' && $path === '/api/analyze/request') {
            $body = file_get_contents('php://input');
            $payload = json_decode($body ?: '{}', true);
            if (!is_array($payload)) {
                JsonResponse::send(['error' => 'Invalid JSON payload'], 400);
                return;
            }

            JsonResponse::send($this->service->analyze($payload));
            return;
        }

        JsonResponse::send(['error' => 'Not found'], 404);
    }

    private function docsHtml(): string
    {
        return <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Entitlement Request Portal API</title>
    <style>
      body { font-family: Segoe UI, Arial, sans-serif; margin: 40px; background: #091423; color: #ebefe4; }
      code, pre { background: #12213a; color: #9dd0ff; padding: 4px 8px; border-radius: 8px; }
      pre { padding: 16px; overflow: auto; }
      h1, h2 { color: #f3f0de; }
      .panel { background: #13233a; border: 1px solid #274465; border-radius: 18px; padding: 20px; margin-bottom: 20px; }
    </style>
  </head>
  <body>
    <h1>Entitlement Request Portal API</h1>
    <div class="panel">
      <h2>Endpoints</h2>
      <ul>
        <li><code>GET /</code></li>
        <li><code>GET /docs</code></li>
        <li><code>GET /api/dashboard/summary</code></li>
        <li><code>GET /api/sample</code></li>
        <li><code>GET /api/requests/{requestId}</code></li>
        <li><code>POST /api/analyze/request</code></li>
      </ul>
    </div>
    <div class="panel">
      <h2>Sample POST Payload</h2>
      <pre>{
  "sensitivity": "critical",
  "privileged": true,
  "sox_scoped": true,
  "contractor_access": false,
  "approval_lag_hours": 9
}</pre>
    </div>
  </body>
</html>
HTML;
    }
}

