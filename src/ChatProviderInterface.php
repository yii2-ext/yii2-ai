<?php

declare(strict_types=1);

namespace proweb\ai;

use AIAccess\Chat\Chat;
use AIAccess\Model;

/**
 * A chat provider: creates chat sessions and lists the models the backend offers.
 *
 * Implementations wrap one ai-access provider client.
 */
interface ChatProviderInterface
{
    /**
     * Creates a chat session with the provider's default generation options applied.
     */
    public function createChat(string|null $model = null): Chat;

    /**
     * Lists the models the backend offers.
     *
     * @return list<Model>
     */
    public function listModels(): array;
}
