<?php

namespace App\Filament\Resources\NewsletterCampaigns\Pages;

use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
use App\Filament\Support\StampsUser;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsletterCampaign extends CreateRecord
{
    use StampsUser;

    protected static string $resource = NewsletterCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->stampCreate($data);
    }
}
