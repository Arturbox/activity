<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Impression;
use Datto\JsonRpc\Evaluator;
use Datto\JsonRpc\Server;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class JrpcController extends Controller implements Evaluator
{
    const JSON_HEADER = ['Content-Type' => 'application/json'];

    public function __invoke(Request $r)
    {
        try {
            $s = new Server($this);
            $res = $s->reply($r->getContent());

            return response(empty($res) ? [] : $res, 200, self::JSON_HEADER);

        } catch (\Throwable $e) {
            Log::error($e);
            return response($e, 500, self::JSON_HEADER);
        }

    }

    /**
     * @throws Exception
     */
    public function evaluate($method, $arguments)
    {
        try {
            // todo handle errors
            return $this->{$method}($arguments);
        } catch (Throwable $e) {
            Log::error($e);
            throw new Exception('Internal error', 500);
        }
    }

    private function addImpression(array $params): bool
    {
        return Impression::query()->insert([
            'url' => $params['url'],
            'datetime' => $params['datetime'],
        ]);
    }

    private function getImpressions(array $params): array
    {
        $paginator = Impression::query()
            ->groupBy('url')
            ->selectRaw('count(*) as count, url, max(datetime) as last_impression')
            ->orderByDesc('last_impression')
            ->paginate($params['per_page'])
            ->appends(request()->except('page'));
        return [
            'meta' => [
                'total' => $paginator->total(),
            ],
            'data' => $paginator->items()
        ];
    }
}
