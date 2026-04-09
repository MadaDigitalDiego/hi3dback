<?php

namespace App\Services;

use App\Models\ActiveCampaignSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActiveCampaignService
{
    protected ?string $apiUrl;
    protected ?string $apiKey;
    protected bool $isEnabled;
    protected array $mapping;
    protected bool $loaded = false;
    protected bool $overridden = false;
    protected ?array $lastResponse = null;

    public function __construct()
    {
        // Configuration is loaded lazily to allow runtime overrides (CLI/testing)
    }

    protected function loadConfiguration(): void
    {
        if ($this->loaded || $this->overridden) {
            return;
        }

        $config = ActiveCampaignSetting::getActiveWithKey();

        $this->apiUrl = $config['api_url'] ?? null;
        $this->apiKey = $config['api_key'] ?? null;
        $this->isEnabled = !empty($config['is_enabled']);
        $this->mapping = is_array($config['mapping'] ?? null) ? ($config['mapping'] ?? []) : (json_decode($config['mapping'] ?? '[]', true) ?: []);
        $this->loaded = true;
    }

    protected function request(string $method, string $endpoint, array $payload = [])
    {
        $this->loadConfiguration();

        if (!$this->isEnabled || !$this->apiUrl || !$this->apiKey) {
            return null;
        }

        $base = rtrim($this->apiUrl, '/');
        $endpoint = ltrim($endpoint, '/');
        $url = strpos($endpoint, 'api/') === 0 ? $base . '/' . $endpoint : $base . '/api/3/' . $endpoint;

        $client = Http::withHeaders([
            'Api-Token' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(10);

        $method = strtoupper($method);
        if ($method === 'GET') {
            $resp = $client->get($url, $payload);
        } elseif ($method === 'DELETE') {
            $resp = $client->delete($url);
        } else {
            $resp = $client->send($method, $url, ['json' => $payload]);
        }

        if ($resp->successful()) {
            $this->lastResponse = $resp->json();
            return $resp->json();
        }

        $this->lastResponse = ['status' => $resp->status(), 'body' => $resp->body()];
        return null;
    }

    /**
     * Raw HTTP request returning decoded JSON or diagnostic array.
     */
    public function rawRequest(string $method, string $endpoint, array $payload = [])
    {
        $this->loadConfiguration();

        if (!$this->isEnabled || !$this->apiUrl || !$this->apiKey) {
            return null;
        }

        $base = rtrim($this->apiUrl, '/');
        $endpoint = ltrim($endpoint, '/');
        $url = strpos($endpoint, 'api/') === 0 ? $base . '/' . $endpoint : $base . '/api/3/' . $endpoint;

        $client = Http::withHeaders([
            'Api-Token' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(10);

        $m = strtoupper($method);
        if ($m === 'GET') {
            $resp = $client->get($url, $payload);
        } elseif ($m === 'DELETE') {
            $resp = $client->delete($url);
        } else {
            $resp = $client->send($m, $url, ['json' => $payload]);
        }

        if ($resp->successful()) {
            $decoded = $resp->json();
            $this->lastResponse = $decoded;
            return $decoded;
        }

        $diag = ['status' => $resp->status(), 'body' => $resp->body()];
        $this->lastResponse = $diag;
        return $diag;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Compatibility syncContact signature used by tests: syncContact($email, $data)
     */
    public function syncContact($emailOrData, $maybeData = null)
    {
        if (is_string($emailOrData)) {
            $email = $emailOrData;
            $data = is_array($maybeData) ? $maybeData : [];
            $payload = array_merge(['email' => $email], $data);
        } else {
            $payload = is_array($emailOrData) ? $emailOrData : [];
        }

        $resp = $this->request('POST', 'contact/sync', ['contact' => $payload]);

        if ($resp) {
            if (isset($resp['contact']['id'])) {
                return (int) $resp['contact']['id'];
            }
            if (isset($resp['contacts'][0]['id'])) {
                return (int) $resp['contacts'][0]['id'];
            }
        }

        return null;
    }

    public function updateContact(int $contactId, array $data): bool
    {
        $contactData = [
            'contact' => [],
        ];

        if (isset($data['firstName'])) {
            $contactData['contact']['firstName'] = $data['firstName'];
        }
        if (isset($data['lastName'])) {
            $contactData['contact']['lastName'] = $data['lastName'];
        }
        if (isset($data['phone'])) {
            $contactData['contact']['phone'] = $data['phone'];
        }
        if (isset($data['email'])) {
            $contactData['contact']['email'] = $data['email'];
        }

        $response = $this->request('POST', "contact/{$contactId}", $contactData);

        if ($response && isset($response['contact'])) {
            Log::info('ActiveCampaign contact updated', [
                'contact_id' => $contactId,
            ]);
            return true;
        }

        return false;
    }

    public function updateContactFields(int $contactId, array $fields): bool
    {
        $fieldData = [
            'field' => [
                'id' => $contactId,
                'value' => $fields,
            ],
        ];

        $response = $this->request('POST', 'contact/update', $fieldData);

        if ($response) {
            Log::info('ActiveCampaign contact fields updated', [
                'contact_id' => $contactId,
                'fields' => array_keys($fields),
            ]);
            return true;
        }

        return false;
    }

    public function addTag(int $contactId, string $tag): bool
    {
        $tagId = $this->getTagId($tag);

        if (!$tagId) {
            // try to create the tag
            $tagId = $this->createTag($tag);
            if (!$tagId) {
                Log::warning('ActiveCampaign tag not found and could not be created', ['tag' => $tag]);
                return false;
            }
        }

        $response = $this->request('POST', 'contactTags', [
            'contactTag' => [
                'contact' => $contactId,
                'tag' => $tagId,
            ],
        ]);

        if ($response) {
            Log::info('ActiveCampaign tag added', [
                'contact_id' => $contactId,
                'tag' => $tag,
            ]);
            return true;
        }

        return false;
    }

    public function removeTag(int $contactId, string $tag): bool
    {
        $tagId = $this->getTagId($tag);

        if (!$tagId) {
            return false;
        }

        // Find the contactTag id for this contact/tag
        $resp = $this->request('GET', "contacts/{$contactId}/contactTags");
        $contactTagId = null;

        if ($resp) {
            $items = $resp['contactTags'] ?? ($resp['contactTag'] ?? $resp);
            if (is_array($items)) {
                foreach ($items as $it) {
                    if ((isset($it['tag']) && (string)$it['tag'] === (string)$tagId) || (isset($it['tag']['id']) && (string)$it['tag']['id'] === (string)$tagId)) {
                        $contactTagId = $it['id'] ?? null;
                        if ($contactTagId) break;
                    }
                }
            }
        }

        if (! $contactTagId) {
            // nothing to delete
            return false;
        }

        $response = $this->request('DELETE', "contactTags/{$contactTagId}");

        if ($response || $response === null) {
            Log::info('ActiveCampaign tag removed', [
                'contact_id' => $contactId,
                'tag' => $tag,
                'contactTag_id' => $contactTagId,
            ]);
            return true;
        }

        return false;
    }

    public function addToList(int $contactId, int $listId): bool
    {
        // Use v3 contactLists endpoint
        $payload = [
            'contactList' => [
                'list' => $listId,
                'contact' => $contactId,
                'status' => 1,
            ],
        ];

        $response = $this->request('POST', 'contactLists', $payload);

        if ($response && isset($response['contactList']['id'])) {
            Log::info('ActiveCampaign contact added to list', [
                'contact_id' => $contactId,
                'list_id' => $listId,
                'contactList_id' => $response['contactList']['id'] ?? null,
            ]);
            return true;
        }

        Log::warning('ActiveCampaign addToList failed', ['contact' => $contactId, 'list' => $listId, 'response' => $response]);
        return false;
    }

    public function removeFromList(int $contactId, int $listId): bool
    {
        $response = $this->request('DELETE', "contact/{$contactId}/list/{$listId}");

        if ($response || $response === null) {
            Log::info('ActiveCampaign contact removed from list', [
                'contact_id' => $contactId,
                'list_id' => $listId,
            ]);
            return true;
        }

        return false;
    }

    public function addToAutomation(int $contactId, int $automationId): bool
    {
        // Prefer posting to the contact-specific contactAutomations endpoint
        $payload = [
            'contactAutomation' => [
                'automation' => $automationId,
            ],
        ];

        $response = $this->request('POST', "contacts/{$contactId}/contactAutomations", $payload);

        if ($response && (isset($response['contactAutomation']['id']) || isset($response['id']))) {
            Log::info('ActiveCampaign contact added to automation (nested)', [
                'contact_id' => $contactId,
                'automation_id' => $automationId,
            ]);
            return true;
        }

        // Fallback: try global contactAutomations endpoint
        $response = $this->request('POST', 'contactAutomations', [
            'contactAutomation' => [
                'contact' => $contactId,
                'automation' => $automationId,
            ],
        ]);

        if ($response && isset($response['contactAutomation']['id'])) {
            Log::info('ActiveCampaign contact added to automation', [
                'contact_id' => $contactId,
                'automation_id' => $automationId,
            ]);
            return true;
        }

        Log::warning('ActiveCampaign addToAutomation failed', ['contact' => $contactId, 'automation' => $automationId, 'response' => $this->lastResponse]);
        return false;
    }

    public function getTags(): array
    {
        $response = $this->request('GET', 'tags');

        if ($response && isset($response['tags'])) {
            return $response['tags'];
        }

        return [];
    }

    public function getLists(): array
    {
        $response = $this->request('GET', 'lists');

        if ($response && isset($response['lists'])) {
            return $response['lists'];
        }

        return [];
    }

    public function getAutomations(): array
    {
        $response = $this->request('GET', 'automations');

        if ($response && isset($response['automations'])) {
            return $response['automations'];
        }

        return [];
    }

    public function getFields(): array
    {
        $response = $this->request('GET', 'fields');

        if ($response && isset($response['fields'])) {
            return $response['fields'];
        }

        return [];
    }

    protected function getTagId(string $tag): ?int
    {
        $tags = $this->getTags();

        foreach ($tags as $t) {
            if (isset($t['tag']) && strtolower($t['tag']) === strtolower($tag)) {
                return (int) $t['id'];
            }
        }

        return null;
    }

    public function createTag(string $tag, string $tagType = 'contact'): ?int
    {
        $response = $this->request('POST', 'tags', [
            'tag' => [
                'tag' => $tag,
                'tagType' => $tagType,
            ],
        ]);

        if ($response && isset($response['tag']['id'])) {
            return (int) $response['tag']['id'];
        }

        return null;
    }

    public function createList(string $name, string $stringId = null): ?int
    {
        $payload = [
            'list' => [
                'name' => $name,
            ],
        ];

        if ($stringId) {
            $payload['list']['stringid'] = $stringId;
        }

        $response = $this->request('POST', 'lists', $payload);

        if ($response && isset($response['list']['id'])) {
            return (int) $response['list']['id'];
        }

        return null;
    }

    public function createField(string $title, string $type = 'text'): ?int
    {
        // Try to find existing field by title
        $existing = $this->request('GET', 'fields');
        if ($existing && isset($existing['fields'])) {
            foreach ($existing['fields'] as $f) {
                if (isset($f['title']) && strtolower($f['title']) === strtolower($title)) {
                    return (int) $f['id'];
                }
            }
        }

        $response = $this->request('POST', 'fields', [
            'field' => [
                'title' => $title,
                'type' => $type,
            ],
        ]);

        if ($response && isset($response['field']['id'])) {
            return (int) $response['field']['id'];
        }

        return null;
    }

    /**
     * Set a custom field value for a contact using contact/sync (preferred)
     */
    public function setCustomField(int $contactId, int $fieldId, $value): bool
    {
        // Use fieldValues endpoint to create/update a field value for a contact
        $payload = [
            'fieldValue' => [
                'contact' => $contactId,
                'field' => $fieldId,
                'value' => $value,
            ],
        ];

        $response = $this->request('POST', 'fieldValues', $payload);

        if ($response && isset($response['fieldValue']['id'])) {
            Log::info('ActiveCampaign field value set', ['contact_id' => $contactId, 'field_id' => $fieldId]);
            return true;
        }

        Log::warning('ActiveCampaign setCustomField failed', ['contact' => $contactId, 'field' => $fieldId, 'response' => $response]);
        return false;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * Return whether the integration is enabled.
     */
    public function isEnabled(): bool
    {
        $this->loadConfiguration();

        return (bool) ($this->isEnabled ?? false);
    }

    public function getMappedTag(string $key): ?string
    {
        return $this->mapping['tags'][$key] ?? null;
    }

    public function getMappedList(string $key): ?int
    {
        return $this->mapping['lists'][$key] ?? null;
    }

    public function getMappedAutomation(string $key): ?int
    {
        return $this->mapping['automations'][$key] ?? null;
    }

    public function testConnection(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $response = $this->request('GET', 'users/me');

        return $response !== null && isset($response['user']);
    }

    /**
     * Allow temporary injection of credentials without saving to DB.
     * Useful for CLI testing or one-off calls.
     */
    public function setCredentials(?string $apiUrl, ?string $apiKey, bool $enabled = true): self
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->isEnabled = $enabled;

        $this->overridden = true;

        return $this;
    }

    public static function make(): self
    {
        return new self();
    }
}
