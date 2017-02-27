<?php
/**
 * Simple Slack API for RomanianIT.
 *
 * @package    RomanianIT
 * @subpackage Services
 * @author     Bogdan SOOS <bogdan.soos@dynweb.org>
 * @version    1.0
 * ...
 */
namespace WebsiteBundle\Services;

use GuzzleHttp\Client;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use WebsiteBundle\Entity\Member;

/**
 * Class Slack.
 *
 * @package WebsiteBundle\Services
 */
class Slack
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string - slack token
     */
    protected $token;

    /**
     * @var string - general channel id
     */
    protected $channel;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Slack constructor.
     *
     * @param $container
     * @param $token
     * @param $channel
     * @param $logger
     */
    public function __construct($container, $token, $channel, $logger)
    {
        $this->container = $container;
        $this->token = $token;
        $this->channel = $channel;
        $this->logger = $logger;

        $this->logger->debug($token);
        $this->logger->debug($channel);
    }


    /**
     * Invite a new user to our slack community.
     *
     * @param Member $member
     *
     * @return bool
     */
    public function inviteUser($member)
    {
        /** @var Client $client */
        $client = $this->container->get('guzzle.client.slack_api');

        try {
            $response = $client->get('/api/users.admin.invite', [
                'query' => $this->__getParameters([
                    'email' => $member->getEmail()
                ])
            ]);
        } catch (\Exception $e){
            $this->logger->critical('Error sending invite to: ' . $member->getEmail() . '. Error message: ' . $e->getMessage());
            return false;
        }

        return true;
    }


    /**
     * Create parameters array.
     *
     * @param array $parameters
     * @return array
     */
    private function __getParameters(array $parameters)
    {
        $config = array_merge([
            'token' => $this->token,
            'channels' => $this->channel
        ], $parameters);

        return $config;
    }
}