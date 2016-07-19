<?php
namespace Collecting\Form\Element;

use Collecting\Validator\Recaptcha as RecaptchaValidator;
use Zend\Form\Element;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\ValidatorInterface;

class Recaptcha extends Element implements InputProviderInterface
{
    protected $attributes = [
        'type' => 'recaptcha',
        'name' => 'g-recaptcha-response',
        'class' => 'g-recaptcha',
    ];

    protected $siteKey;

    protected $secretKey;

    protected $remoteIp;

    protected $validator;

    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['site_key'])) {
            $this->setSiteKey($this->options['site_key']);
        }
        if (isset($this->options['secret_key'])) {
            $this->setSecretKey($this->options['secret_key']);
        }
        if (isset($this->options['remote_ip'])) {
            $this->setRemoteIp($this->options['remote_ip']);
        }

        return $this;
    }

    public function setSiteKey($siteKey)
    {
        $this->siteKey = $siteKey;
        $this->setAttribute('data-sitekey', $siteKey);
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

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function getValidator()
    {
        if (!$this->validator) {
            // Provide a default validator.
            $this->validator = new RecaptchaValidator;
        }
        return $this->validator
            ->setSecretKey($this->secretKey)
            ->setRemoteIp($this->remoteIp);
    }

    public function getInputSpecification()
    {
        return [
            'name' => 'g-recaptcha-response',
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'You must verify that you are human by completing the CAPTCHA below.', // @translate
                        ],
                    ],
                ],
                $this->getValidator(),
            ],
        ];
    }
}
