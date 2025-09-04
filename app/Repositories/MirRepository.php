<?php

namespace App\Repositories;

use App\Models\Device;
use App\Models\MirStatus;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\URL;
use Psr\Http\Message\ResponseInterface;

class MirRepository {
    private string|null $mirHost = null;
    private ?Device $device = null;
    private ?ResponseInterface $res = null;
    private array $headers = [
        'Accept-Language' => 'UI_API_DEFAULT_LANGUAGE',
        'Content-Type'    => 'application/json',
        'Authorization'   => 'Basic ZGlzdHJpYnV0b3I6NjJmMmYwZjFlZmYxMGQzMTUyYzk1ZjZmMDU5NjU3NmU0ODJiYjhlNDQ4MDY0MzNmNGNmOTI5NzkyODM0YjAxNA=='
    ];
    private float $timeout = 10.0;
    private Client $client;

    public function __construct() {
        $this->client = new Client([
            'timeout' => $this->timeout, // 5秒超時
        ]);
        if(!$this->mirHost) {
            $this->getHost();
        }
    }

    public function getDevice(): ?Device {
        return $this->device;
    }

    public function getHost(): ?string {
        $this->device = Device::first();
        if(env('APP_ENV') == 'local') {
            $this->mirHost = URL::to('/api/v2.0.0') . '/';
        } else {
            if($this->device) {
                if($this->device->ip) {
                    /** @noinspection HttpUrlsUsage */
                    $this->mirHost = "http://{$this->device->ip}/api/v2.0.0/";
                } else if($this->device->ap) {
                    $this->mirHost = "{$this->device->ap}/api/v2.0.0/";
                } else {
                    $this->mirHost = null;
                }

                $this->device = $this->device->load('mirStatus');
                /** @var MirStatus $mirStatus */
                $mirStatus = $this->device->mirStatus;
                if($mirStatus) {
                    $this->device->mirStatus->load([
                        'roomEnvironment',
                        'missionQueue.mission',
                        'location'
                    ]);
                }
            } else {
                $this->mirHost = null;
            }
        }

        return $this->mirHost;
    }

    /**
     * @throws GuzzleException
     */
    public function getRegister($id): float|string|int|array {
        $this->res = $this->client->get("{$this->mirHost}registers/$id", [
            'headers' => $this->headers
        ]);
        $res = json_decode($this->res->getBody()->getContents(), true);
        return $res['value'];
    }

    /**
     * @throws GuzzleException
     */
    public function postRegister($id, $value = 0): bool {
        $this->res = $this->client->post("{$this->mirHost}registers/$id", [
            'json'    => [
                'value' => $value
            ],
            'headers' => $this->headers
        ]);
        $res = json_decode($this->res->getBody()->getContents(), true);
        return $res['value'] == 0;
    }

    /**
     * @throws GuzzleException
     */
    public function getStatus(): array {
        $this->res = $this->client->get("{$this->mirHost}status", [
            'headers' => $this->headers
        ]);
        $res = json_decode($this->res->getBody()->getContents(), true);

        return [
            'position'           => $res['position'],
            'robot_model'        => $res['robot_model'],
            'mission_text'       => $res['mission_text'],
            'velocity'           => $res['velocity'],
            'battery_percentage' => $res['battery_percentage'],
            'mission_queue_id'   => $res['mission_queue_id'],
            'map_id'             => $res['map_id'],
            'state_text'         => $res['state_text'],
            'state_id'           => $res['state_id']
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function getMissions(): array {
        $this->res = $this->client->get("{$this->mirHost}missions", [
            'headers' => $this->headers
        ]);

        return json_decode($this->res->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getMissionQueues(): array {
        $res = $this->client->get("{$this->mirHost}mission_queue/", [
            'headers' => $this->headers
        ]);
        return json_decode($res->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getMissionQueue($id): array {
        $res = $this->client->get("{$this->mirHost}mission_queue/{$id}", [
            'headers' => $this->headers
        ]);
        $a = $res->getBody()->getContents();
        return json_decode($a, true);
    }

    /**
     * @throws GuzzleException
     */
    public function putStatus($statusId): bool {
        $this->res = $this->client->put("{$this->mirHost}status/", [
            'headers' => $this->headers,
            'json'    => [
                'state_id' => $statusId
            ]
        ]);
        if($this->res->getStatusCode() == 200) {
            $success = true;
        } else {
            $success = false;
        }
        return $success;
    }

    /**
     * @throws GuzzleException
     */
    public function postMissionQueue($id): bool {
        $this->res = $this->client->post("{$this->mirHost}mission_queue", [
            'headers' => $this->headers,
            'json'    => [
                'mission_id' => $id
            ]
        ]);
        if($this->res->getStatusCode() == 201) {
            $success = true;
        } else {
            $success = false;
        }
        return $success;
    }

    /**
     * @throws GuzzleException
     */
    public function deleteMissionQueue($id): bool {
        $this->res = $this->client->delete("{$this->mirHost}mission_queue", [
            'headers' => $this->headers,
            'json'    => [
                'mission_id' => $id
            ]
        ]);
        if($this->res->getStatusCode() == 200) {
            $success = true;
        } else {
            $success = false;
        }
        return $success;
    }

    public function getStatusCode(): int {
        if(!$this->res) {
            return -1;
        }

        return $this->res->getStatusCode();
    }

    /**
     * @throws GuzzleException
     */
    public function getMaps(): array {
        $this->res = $this->client->get("{$this->mirHost}maps", [
            'headers' => $this->headers
        ]);

        return json_decode($this->res->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getMap($id): array {
        $this->res = $this->client->get("{$this->mirHost}maps/{$id}", [
            'headers' => $this->headers
        ]);

        return json_decode($this->res->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getHookStatus(): array {
        $this->res = $this->client->get("{$this->mirHost}hook/status", [
            'headers' => $this->headers
        ]);

        return json_decode($this->res->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getMission($id): array {
        $this->res = $this->client->get("{$this->mirHost}missions/{$id}", [
            'headers' => $this->headers
        ]);

        return json_decode($this->res->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getPositions($mapId): array {
        $this->res = $this->client->get("{$this->mirHost}maps/$mapId/positions", [
            'headers' => $this->headers
        ]);

        return json_decode($this->res->getBody()->getContents(), true);
    }

    public function getPosition($id): array {
        $this->res = $this->client->get("{$this->mirHost}positions/$id", [
            'headers' => $this->headers
        ]);

        return json_decode($this->res->getBody()->getContents(), true);
    }

    /**
     * @throws GuzzleException
     */
    public function getWifiConnections(): bool {
        $this->res = $this->client->get("{$this->mirHost}wifi/connections", [
            'headers' => $this->headers
        ]);
        return $this->res->getStatusCode() == 200;
    }


    /**
     * @throws GuzzleException
     */
    public function resetError(): bool {
        $this->res = $this->client->put("{$this->mirHost}status", [
            'headers' => $this->headers,
            'json'    => [
                'clear_error' => True
            ]
        ]);
        return $this->res->getStatusCode() == 200;
    }

    /**
     * @throws GuzzleException
     */
    public function getErrorReports($id): string {
        $this->res = $this->client->get("{$this->mirHost}log/error_reports/$id", [
            'headers' => $this->headers
        ]);
        $res = json_decode($this->res->getBody()->getContents(), true);

        return $res['message'];
    }
}
