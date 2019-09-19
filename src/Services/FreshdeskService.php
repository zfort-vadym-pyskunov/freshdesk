<?php

namespace KuznetsovZfort\Freshdesk\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Session\Session;
use KuznetsovZfort\Freshdesk\Enums\TicketStatus;
use KuznetsovZfort\Freshdesk\Exceptions\ApiException;
use Psr\Log\LoggerInterface;

class FreshdeskService
{
    const FACADE_ACCESSOR = 'kuznetsov-zfort.freshdesk';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Config $config
     * @param LoggerInterface $log
     * @param Session $session
     */
    public function __construct(Config $config, LoggerInterface $log, Session $session)
    {
        $this->config = $config;
        $this->log = $log;
        $this->session = $session;
    }

    /**
     * @param string $email
     *
     * @return bool|mixed
     */
    public function getAgent(string $email)
    {
        $agents = $this->apiCall('agents?email=' . $email);
        if (is_array($agents)) {
            return reset($agents);
        }

        return $agents;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function hasAgent(string $email): bool
    {
        return !empty($this->getAgent($email));
    }

    /**
     * @return bool|mixed
     */
    public function getNewTickets()
    {
        return $this->apiCall('tickets?filter=new_and_my_open');
    }

    /**
     * @return bool
     */
    public function isCurrentUserAgent(): bool
    {
        return $this->session->has('freshdesk_agent_id');
    }

    /**
     * @return int|null
     */
    public function getCurrentUserAgentId(): ?int
    {
        return $this->session->get('freshdesk_agent_id');
    }

    /**
     * @param int|null $status
     * @param Carbon|null $from
     * @param Carbon|null $to
     *
     * @return int
     */
    public function getTicketsCount(
        ?int $status = null,
        ?Carbon $from = null,
        ?Carbon $to = null
    ) {
        if ($this->isCurrentUserAgent()) {
            $query = "agent_id:" . $this->getCurrentUserAgentId();

            if (in_array($status, TicketStatus::listKeys())) {
                $query .= " AND status:" . $status;
            }

            if ($from) {
                $query .= " AND created_at:>'" . $from->format('Y-m-d') . "'";
            }

            if ($to) {
                $query .= " AND created_at:<'" . $to->format('Y-m-d') . "'";
            }

            $query = str_replace(['%3A', '%3E', '%3C'], [':', '>', '<'], rawurlencode($query));
            $response = $this->apiCall('search/tickets?query="' . $query . '"');
            if ($response) {
                return $response->total;
            }
        }

        return 0;
    }

    /**
     * @param string $uri
     *
     * @return bool|mixed
     */
    public function apiCall(string $uri)
    {
        try {
            return $this->curlCall($uri);
        } catch (ApiException $apiException) {
            $this->log->error('Freshdesk API error', [
                'code' => $apiException->getCode(),
                'message' => $apiException->getMessage(),
                'response' => $apiException->getResponse(),
            ]);
        } catch (Exception $exception) {
            $this->log->error($exception->getMessage());
        }

        return false;
    }

    /**
     * @param $uri
     * @return mixed
     * @throws ApiException
     */
    private function curlCall($uri)
    {
        $key = $this->config->get('matchbingo.freshdesk.api_key');
        $url = $this->config->get('matchbingo.freshdesk.api_url') . $uri;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERPWD, "$key:X");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new Exception($error);
        }

        switch ($info['http_code']) {
            case 200:
                return json_decode($response);
                break;
            case 404:
                throw new ApiException('Endpoint does not exist', 404);
                break;
            default:
                throw new ApiException('Unexpected error', $info['http_code'], $response);
                break;
        }
    }

    /**
     * @param string $name
     * @param string $email
     * @param string|null $redirect
     *
     * @return string
     */
    public function getSsoUrl(string $name, string $email, ?string $redirect = null): string
    {
        $secret = $this->config->get('matchbingo.freshdesk.shared_secret');
        $timestamp = time();
        $toBeHashed = $name . $secret . $email . $timestamp;
        $hash = hash_hmac('md5', $toBeHashed, $secret);
        $url = $this->config->get('matchbingo.freshdesk.sso_url');
        $url .= '?name=' . urlencode($name);
        $url .= '&email=' . urlencode($email);
        $url .= '&timestamp=' . $timestamp;
        $url .= '&hash=' . $hash;
        if ($redirect) {
            $url .= '&redirect_to=' . urlencode($redirect);
        }

        return $url;
    }
}