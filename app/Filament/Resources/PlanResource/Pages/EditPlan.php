<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use App\Services\StripeService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

        protected function afterSave(): void
        {
            $state = $this->form->getState();

            if (! ($state['auto_sync_to_stripe'] ?? false)) {
                return;
            }

            try {
                app(StripeService::class)->syncPlanWithStripe($this->record);

                Notification::make()
                    ->title('Plan synchronise avec Stripe')
                    ->body('Le produit et les prix mensuel/annuel ont ete crees ou mis a jour sur Stripe.')
                    ->success()
                    ->send();
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Erreur lors de la synchronisation Stripe')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
}
