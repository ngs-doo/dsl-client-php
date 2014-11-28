<?php
namespace NGS\Client;

abstract class BaseProxy {

    protected $client;

    /**
     * Create a new proxy instance
     *
     * @param HttpClient $client HttpClient instance used for http request.
     * Optionally specify an instance, otherwise use a singleton instance
     * @throws \InvalidArgumentException If $client is null and global instance is not configured
     */
    public function __construct(HttpClient $client = null)
    {
        if ($client !== null)
            $this->client = $client;
        else if (($globalClient = HttpClient::instance()) !== null)
            $this->client = $globalClient;
        else
            throw new \InvalidArgumentException('Could not construct proxy '.__CLASS__.'. Provided HttpClient was null and global HttpClient was not instantiated. Did you pass in a valid HttpClient instance or configured a global client with HttpClient::instance?');
    }
}