<?php

namespace App\Message;

final class MailMessage
{
    private int $userId;
    private int $templateId;
    private array $params;

    public function __construct(int $userId, int $templateId, array $params = [])
    {
        $this->userId = $userId;
        $this->templateId = $templateId;
        $this->params = $params;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
