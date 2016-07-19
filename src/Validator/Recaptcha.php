<?php
namespace Collecting\Validator;

use Zend\Http\Client;
use Zend\Validator\AbstractValidator;

class Recaptcha extends AbstractValidator
{
    const ERROR = 'recaptchaError';

    protected $messageTemplates = [
        self::ERROR => 'Could not verify that you are a human.', // @translate
    ];

    protected $client;

    protected $secretKey;

    protected $remoteIp;

    public function isValid($value)
    {
        $this->setValue($value);

        if (!$this->client) {
            // Provide a default client using common SSL configuration.
            $this->client = new Client(null, ['sslcapath' => '/etc/ssl/certs']);
        }

        $response = $this->client
            ->setUri('https://www.google.com/recaptcha/api/siteverify')
            ->setMethod('POST')
            ->setParameterPost([
                'response' => $value,
                'secret' => $this->secretKey,
                'remoteip' => $this->remoteIp,
            ])
            ->send();
        $apiResponse = json_decode($response->getBody(), true);

        if ($apiResponse['success']) {
            return true;
        }

        $this->error(self::ERROR);
        return false;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    public function setRemoteIp($remoteIp)
    {
        $this->remoteIp = $remoteIp;
        return $this;
    }
}
