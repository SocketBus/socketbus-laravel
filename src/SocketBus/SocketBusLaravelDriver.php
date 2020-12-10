<?php

namespace SocketBus;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SocketBusLaravelDriver extends Broadcaster
{
    /**
     * @var SocketBus
     */
    protected $socketbus;

    public function __construct($settings)
    {
        $this->socketBus = new SocketBus($settings);
    }

    private function isPrivate(string $channelName)
    {
        if (strpos($channelName, 'private-') === 0 || strpos($channelName, 'presence-') === 0) {
            return true;
        } else if (strpos($channelName, 'public-') === 0) {
            return false;
        }
    }

    private function normalizeChannel(string $channelName)
    {
        return str_replace(['private-', 'presence-', 'public-'], '', $channelName);
    }

    private function verifyCanAccessPublicChannel($request, string $channelName)
    {
        foreach ($this->channels as $pattern => $callback) {
            if (! $this->channelNameMatchesPattern($channelName, $pattern)) {
                continue;
            }

            $parameters = $this->extractAuthParameters($pattern, $channelName, $callback);

            $handler = $this->normalizeChannelHandlerToCallable($callback);

            if ($result = $handler(...$parameters)) {
                return $this->validAuthenticationResponse($request, $result);
            }
        }

        throw new AccessDeniedHttpException;
    }

    public function getUserId($request)
    {
        return $this->retrieveUser($request, $request->channel_name)
            ->getAuthIdentifier();
    } 


    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function auth($request)
    {
        $channelName = $this->normalizeChannel($request->channel_name);
        if ($this->isPrivate($request->channel_name)){
            return parent::verifyUserCanAccessChannel(
                $request, $channelName
            );
        }
        return $this->verifyCanAccessPublicChannel(
            $request, $channelName
        );
    }

    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        if (strpos($request->channel_name, 'private-') === 0 || strpos($request->channel_name, 'presence-') === 0 || strpos($request->channel_name, 'public-') === 0) {
            return $this->socketBus->auth(
                $request->socket_id, 
                $request->channel_name,
                $result
            );
        }

        return $this->socketBus->authPresence(
            $request->socket_id, 
            $request->channel_name, 
            $this->getUserId($request),
            $result
        );
    }

    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $formatted_channels = $this->formatChannels($channels);

        $response = $this->socketBus->broadcast($formatted_channels, $event, $payload);

        if ($response !== true) {
            throw new BroadcastException(
                "Error trying to broadcast an event to SocketBus\n{$response}"
            );
        }

        return true;
    }


    /**
     * Gets the status of the application
     * 
     */
    public function getStatus()
    {
        return $this->socketBus->getStatus();
    }

    /**
     * Lists all the channels
     * 
     */
    public function getChannels()
    {
        return $this->socketBus->getChannels();
    }

    /**
     * Gets the total users in a channel
     * 
     */
    public function getCountUsersInChannel(string $channelName)
    {
        return $this->socketBus->getCountUsersInChannel($channelName);
    }


    /**
     * Get all users information in a given channel
     * 
     */
    public function getChannelUsers(string $channelName)
    {
        return $this->socketBus->getChannelUsers($channelName);
    }

    public function authWebhook($request)
    {
        return $request->header('authorization') && $this->socketBus->authWebhook($request->header('authorization'));
    }
}
