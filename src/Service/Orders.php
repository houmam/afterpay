<?php
namespace CultureKings\Afterpay\Service;

use CultureKings\Afterpay\Exception\ApiException;
use CultureKings\Afterpay\Model\Authorization;
use CultureKings\Afterpay\Model\ErrorResponse;
use CultureKings\Afterpay\Model\OrderDetails;
use CultureKings\Afterpay\Model\OrderToken;
use CultureKings\Afterpay\Traits\AuthorizationTrait;
use CultureKings\Afterpay\Traits\ClientTrait;
use CultureKings\Afterpay\Traits\SerializerTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use JMS\Serializer\SerializerInterface;

/**
 * Class Orders
 *
 * @package CultureKings\Afterpay\Service
 */
class Orders
{
    use ClientTrait;
    use AuthorizationTrait;
    use SerializerTrait;

    /**
     * Payments constructor.
     * @param Client              $client
     * @param Authorization       $authorization
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Client $client,
        Authorization $authorization,
        SerializerInterface $serializer
    ) {
        $this->setClient($client);
        $this->setAuthorization($authorization);
        $this->setSerializer($serializer);
    }

    /**
     * @param OrderDetails $order
     * @return OrderToken|object
     */
    public function create(OrderDetails $order)
    {
        try {
            $result = $this->getClient()->post(
                'orders',
                [
                    'auth' => [
                        $this->getAuthorization()->getMerchantId(),
                        $this->getAuthorization()->getSecret(),
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'body' => $this->getSerializer()->serialize($order, 'json'),
                ]
            );
        } catch (ClientException $e) {
            throw new ApiException(
                $this->getSerializer()->deserialize(
                    $e->getResponse()->getBody()->getContents(),
                    ErrorResponse::class,
                    'json'
                )
            );
        }

        return $this->getSerializer()->deserialize(
            $result->getBody()->getContents(),
            OrderToken::class,
            'json'
        );
    }

    /**
     * @param string $token
     * @return OrderDetails|object
     */
    public function get($token)
    {
        try {
            $result = $this->getClient()->get(
                sprintf('orders/%s', $token),
                [
                    'auth' => [
                        $this->getAuthorization()->getMerchantId(),
                        $this->getAuthorization()->getSecret(),
                    ],
                ]
            );
        } catch (ClientException $e) {
            throw new ApiException(
                $this->getSerializer()->deserialize(
                    $e->getResponse()->getBody()->getContents(),
                    ErrorResponse::class,
                    'json'
                )
            );
        }

        return $this->getSerializer()->deserialize(
            $result->getBody()->getContents(),
            OrderDetails::class,
            'json'
        );
    }
}
