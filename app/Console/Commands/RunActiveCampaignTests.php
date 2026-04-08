<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ActiveCampaignService;

class RunActiveCampaignTests extends Command
{
    protected $signature = 'activecampaign:run-tests {--url=} {--key=} {--email=dev+ac_test@example.com}';

    protected $description = 'Run a series of ActiveCampaign operations for testing (contact, tags, lists, fields, automation)';

    public function handle(ActiveCampaignService $service)
    {
        $url = $this->option('url');
        $key = $this->option('key');
        $email = $this->option('email');

        if (! $url || ! $key) {
            $this->error('Please provide --url and --key');
            return 1;
        }

        $this->line('Starting ActiveCampaign operations with email: ' . $email);

        $service->setCredentials($url, $key, true);

        // 1) Create or sync contact
        $contactId = $service->syncContact($email, ['firstName' => 'AC', 'lastName' => 'Test']);
        if (! $contactId) {
            $this->error('syncContact failed');
            $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));
            return 2;
        }
        $this->info('Contact synced: id=' . $contactId);
        $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));

        // 2) Add tag
        $tagName = 'ac_test_tag';
        $added = $service->addTag($contactId, $tagName);
        $this->info('Add tag ' . $tagName . ': ' . ($added ? 'ok' : 'failed'));
        $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));

        // 3) Remove tag
        $removed = $service->removeTag($contactId, $tagName);
        $this->info('Remove tag ' . $tagName . ': ' . ($removed ? 'ok' : 'failed'));
        $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));

        // 4) Create/get list and add to list
        $listName = 'ac_test_list';
        $lists = $service->getLists();
        $listId = null;
        foreach ($lists as $l) {
            if (isset($l['name']) && $l['name'] === $listName) {
                $listId = (int) $l['id'];
                break;
            }
        }
        if (! $listId) {
            $listId = $service->createList($listName);
            $this->info('Created list id=' . $listId);
            $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));
        }

        $addedToList = $service->addToList($contactId, $listId);
        $this->info('Add to list ' . $listName . ': ' . ($addedToList ? 'ok' : 'failed'));
        $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));

        // 5) Create custom field and set value
        $fieldTitle = 'ac_test_field';
        $fieldId = $service->createField($fieldTitle);
        if ($fieldId) {
            $this->line('Field created response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));
            $setField = $service->setCustomField($contactId, $fieldId, 'test-value');
            $this->info('Set custom field ' . $fieldTitle . ': ' . ($setField ? 'ok' : 'failed'));
            $this->line('Set field response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));
        } else {
            $this->error('Could not create custom field');
            $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));
        }

        // 6) Add to automation (if automation exists)
        $automations = $service->getAutomations();
        $automationId = null;
        if (! empty($automations)) {
            $automationId = (int) $automations[0]['id'];
            $this->info('Using automation id=' . $automationId . ' (first found)');
            $addedAuto = $service->addToAutomation($contactId, $automationId);
            $this->info('Add to automation: ' . ($addedAuto ? 'ok' : 'failed'));
            $this->line('Last response: ' . json_encode($service->getLastResponse(), JSON_PRETTY_PRINT));

            if (! $addedAuto) {
                $this->line('Attempting expanded automation payloads/endpoints for enrollment...');

                $endpoints = [
                    "contactAutomations",
                    "contacts/{$contactId}/contactAutomations",
                    "automationContacts",
                    "contacts/{$contactId}/automations",
                    "automations/{$automationId}/contacts",
                ];

                $variants = [
                    ['contactAutomation' => ['contact' => $contactId, 'automation' => $automationId]],
                    ['contactAutomation' => ['contact' => $contactId, 'automation' => $automationId, 'seriesid' => 0]],
                    ['contactAutomation' => ['contact' => $contactId, 'automation' => $automationId, 'sdate' => now()->toIso8601String()]],
                    ['contactAutomation' => ['contact' => $automationId]],
                    ['contact' => $contactId, 'automation' => $automationId],
                    ['contact_id' => $contactId, 'automation_id' => $automationId],
                    ['automation' => $automationId, 'contact' => $contactId],
                ];

                foreach ($endpoints as $ep) {
                    foreach ($variants as $i => $payload) {
                        $this->line("Endpoint: {$ep}  Try #" . ($i + 1) . ' payload: ' . json_encode($payload));
                        $resp = $service->rawRequest('POST', $ep, $payload);
                        $this->line('Response: ' . json_encode($resp, JSON_PRETTY_PRINT));
                        if (is_array($resp) && (isset($resp['contactAutomation']['id']) || isset($resp['id']) || (isset($resp['status']) && $resp['status'] >= 200 && $resp['status'] < 300))) {
                            $this->info('Succeeded on endpoint ' . $ep . ' with payload #' . ($i + 1));
                            break 2;
                        }
                    }
                }
            }
        } else {
            $this->line('No automations found to test add-to-automation.');
        }

        $this->info('ActiveCampaign run-tests completed.');

        return 0;
    }
}
