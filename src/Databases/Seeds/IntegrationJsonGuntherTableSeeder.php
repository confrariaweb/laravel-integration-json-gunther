<?php

use ConfrariaWeb\Integration\IntegrationType;
use Illuminate\Database\Seeder;

class IntegrationJsonGuntherTableSeeder extends Seeder
{
    public function run()
    {
        $integrationsTypes = $this->getIntegrations();

        foreach ($integrationsTypes as $type) {
            IntegrationType::create($type);
            $this->command->info("Tipo de Integração " . $type['name'] . " criada.");
        }
    }

    private function getIntegrations()
    {
        return [
            'integrationJsonGunther' => [
                'name' => 'Arquivo JSON - Gunther',
                'view' => 'integrationJsonGunther',
                'service' => 'IntegrationJsonGuntherService'
            ]
        ];
    }
}
