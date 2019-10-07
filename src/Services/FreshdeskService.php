<?php

namespace KuznetsovZfort\Freshdesk\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Session\Session;
use KuznetsovZfort\Freshdesk\Enums\TicketStatus;
use KuznetsovZfort\Freshdesk\Exceptions\ApiException;

class FreshdeskService
{
    const FACADE_ACCESSOR = 'kuznetsov-zfort.freshdesk';
    const AGENT_SESSION_KEY = 'freshdesk_agent_id';

    private $cache;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Cache $cache
     * @param Config $config
     * @param Session $session
     */
    public function __construct(Cache $cache, Config $config, Session $session)
    {
        $this->cache = $cache;
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * @param array $data
     *
     * @return mixed
     *
     * @throws ApiException
     */
    public function createTicket(array $data)
    {
        return $this->apiCall('tickets', $data);
    }

    /**
     * @param string $email
     *
     * @return mixed
     *
     * @throws ApiException
     */
    public function getAgent(string $email)
    {
        $agents = $this->apiCall('agents?email=' . urlencode($email));
        if (is_array($agents)) {
            return reset($agents);
        }

        return $agents;
    }

    /**
     * @param string $email
     *
     * @return mixed
     *
     * @throws ApiException
     */
    public function getContact(string $email)
    {
        $contacts = $this->apiCall('contacts?email=' . urlencode($email));
        if (is_array($contacts)) {
            return reset($contacts);
        }

        return $contacts;
    }

    /**
     * @param string $email
     *
     * @return bool
     *
     * @throws ApiException
     */
    public function hasAgent(string $email): bool
    {
        return !empty($this->getAgent($email));
    }

    /**
     * @param string $email
     *
     * @return bool
     *
     * @throws ApiException
     */
    public function hasContact(string $email): bool
    {
        return !empty($this->getContact($email));
    }

    /**
     * @return mixed
     *
     * @throws ApiException
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
        return $this->session->has(self::AGENT_SESSION_KEY);
    }

    /**
     * @return int|null
     */
    public function getCurrentUserAgentId(): ?int
    {
        return $this->session->get(self::AGENT_SESSION_KEY);
    }

    /**
     * @param int $agentId
     */
    public function setCurrentUserAgentId(int $agentId)
    {
        $this->session->put(self::AGENT_SESSION_KEY, $agentId);
    }

    /**
     * @param int|null $status
     * @param Carbon|null $from
     * @param Carbon|null $to
     *
     * @return int
     *
     * @throws ApiException
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
     * @param string $name
     * @param string $email
     * @param string|null $redirect
     *
     * @return string
     */
    public function getSsoUrl(string $name, string $email, ?string $redirect = null): string
    {
        $secret = $this->config->get('freshdesk.shared_secret');
        $timestamp = time();
        $toBeHashed = $name . $secret . $email . $timestamp;
        $hash = hash_hmac('md5', $toBeHashed, $secret);
        $url = $this->config->get('freshdesk.sso_url');
        $url .= '?name=' . urlencode($name);
        $url .= '&email=' . urlencode($email);
        $url .= '&timestamp=' . $timestamp;
        $url .= '&hash=' . $hash;
        if ($redirect) {
            $url .= '&redirect_to=' . urlencode($redirect);
        }

        return $url;
    }

    /**
     * @param Authenticatable $user
     *
     * @return string
     *
     * @throws ApiException
     */
    public function getContactTicketsUrl(Authenticatable $user): string
    {
        if ($this->isCurrentUserAgent()) {
            if (isset($user->email)) {
                $contact = $this->getContact($user->email);
                if ($contact) {
                    $baseUrl = $this->config->get('freshdesk.tickets_url');
                    $baseUrl .= '?orderBy=created_at&orderType=desc';

                    return implode('&', [
                        $baseUrl,
                        $this->getQuery('agent', $this->getCurrentUserAgentId()),
                        $this->getQuery('status', TicketStatus::OPEN),
                        $this->getQuery('requester', $contact->id),
                    ]);
                }
            }
        }

        return '';
    }

    /**
     * @param string|null $attribute
     * @param mixed|null $value
     *
     * @return string
     */
    public function getNewTicketUrl(?string $attribute = null, $value = null): string
    {
        $url = $this->config->get('freshdesk.new_ticket_url');
        if (!empty($attribute) && !empty($value)) {
            $url .= '?' . $attribute . '=' . urlencode($value);
        }

        return $url;
    }

    /**
     * @param string $uri
     * @param array $data
     *
     * @return mixed
     *
     * @throws ApiException
     */
    private function apiCall(string $uri, array $data = [])
    {
        if (!empty($data)) {
            return $this->curlCall($uri, $data);
        }

        $cacheKey = 'freshdesk.api-call.' . md5($uri);
        $cacheDuration = $this->config->get('freshdesk.cache_duration');

        return $this->cache->remember($cacheKey, $cacheDuration, function () use ($uri) {
            return $this->curlCall($uri);
        });
    }

    /**
     * @param string $uri
     * @param array $data
     *
     * @return mixed
     *
     * @throws ApiException
     */
    private function curlCall(string $uri, array $data = [])
    {
        $key = $this->config->get('freshdesk.api_key');
        $url = $this->config->get('freshdesk.api_url') . $uri;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERPWD, "$key:X");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

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
            case 401:
                throw new ApiException('Authorization Required', 401);
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
     * @param string $attribute
     * @param mixed $value
     *
     * @return string
     */
    private function getQuery(string $attribute, $value): string
    {
        return 'q[]=' . urlencode("{$attribute}:[{$value}]");
    }
}