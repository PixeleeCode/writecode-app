<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Client;
use RuntimeException;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;
use SendinBlue\Client\Model\SendTestEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class MailService.
 */
class MailService
{
    private TransactionalEmailsApi $instance;
    private ParameterBagInterface $parameter;
    private SerializerInterface $serializer;

    /**
     * MailService constructor.
     */
    public function __construct(ParameterBagInterface $parameterBag, SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->parameter = $parameterBag;
        $configuration = Configuration::getDefaultConfiguration()->setApiKey('api-key', $parameterBag->get('sendinblue_api'));
        $this->instance = new TransactionalEmailsApi(new Client(), $configuration);
    }

    /**
     * Envoi un email via le service SendinBlue.
     */
    public function sendEmail(int $templateId, array $vars): bool
    {
        $environment = $this->parameter->get('app.env');
        try {
            if ('dev' === $environment || 'test' === $environment) {
                $this->instance->sendTestTemplate(
                    $templateId,
                    new SendTestEmail([
                        'emailTo' => [$this->parameter->get('app.email')],
                    ])
                );
            } else {
                $this->instance->sendTransacEmail(new SendSmtpEmail([
                    'to' => [
                        [
                            'email' => $vars['emailTo'],
                            'name' => null,
                        ],
                    ],
                    'templateId' => $templateId,
                    'params' => 0 === count($vars['attributes']) ? null : $vars['attributes'],
                ]));
            }
        } catch (ApiException $e) {
            if (402 !== $e->getCode()) {
                throw new RuntimeException('Exception when calling TransactionalEmailsApi->sendTransacEmail: '.$e->getMessage().PHP_EOL);
            }
        }

        return true;
    }

    /**
     * Envoi un email via le service SendinBlue pour prévenir d'un échec
     * dans le messenger.
     *
     * @throws Exception
     */
    public function notifyFailed(string $message, ?object $entity = null): bool
    {
        $error = $entity ? $this->serializer->serialize($entity, 'json') : null;
        $error = json_decode($error, true);

        return $this->sendEmail(5, [
           'emailTo' => $this->parameter->get('app.email_failure'),
            'attributes' => [
                'MESSAGE' => $message,
                'ERROR_MESSAGE' => $error['errorMessage'] ?? '',
                'ERROR_CLASS' => $error['messageClass'] ?? '',
                'TRACE' => $error['trace'] ?? '',
                'CREATED_AT' => new DateTime('now', new DateTimeZone('Europe/Paris')),
            ],
        ]);
    }
}
