<?PHP

namespace ConfrariaWeb\IntegrationJsonGunther\Services;

use ConfrariaWeb\Integration\Services\Contracts\IntegrationContract;
use Carbon\Carbon;

class IntegrationJsonGuntherService implements IntegrationContract
{
    protected $data = [];
    protected $file_get_contents = [];
    protected $json_decode = [];

    public function set(Array $data)
    {
        try {
            $this->data = $data;

            $this->file_get_contents = null;
            if (isset($this->data['url'])) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_URL, $this->data['url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
                $this->file_get_contents = curl_exec($ch);
                curl_close($ch);
            }

            //$this->file_get_contents = isset($this->data['url']) ? file_get_contents($this->data['url'],true) : null;
            $this->json_decode = json_decode($this->file_get_contents, true);
        } catch (Exception $e) {

        }
    }

    public function get()
    {
        $userCollect = [];

        foreach ($this->json_decode as $jDecode) {

            $jDecode['sync']['optionsValues'] = $jDecode;

            /*codigo intranet*/
            if (isset($jDecode['codigo_intranet']) && !empty($jDecode['codigo_intranet'])) {
                $jDecode['sync']['optionsValues']['intranet_code'] = $jDecode['codigo_intranet'];
            }

            /*Contacts*/
            if (isset($jDecode['telefone']) && !empty($jDecode['telefone'])) {
                $jDecode['sync']['contacts']['phone'] = $jDecode['telefone'];
            }
            if (isset($jDecode['telefone_celular']) && !empty($jDecode['telefone_celular'])) {
                $jDecode['sync']['contacts']['cellphone'] = $jDecode['telefone_celular'];
            }
            if (isset($jDecode['email']) && !empty($jDecode['email'])) {
                $jDecode['sync']['contacts']['email'][] = $jDecode['email'];
            }
            if (isset($jDecode['email_secundario']) && !empty($jDecode['email_secundario'])) {
                $jDecode['sync']['contacts']['email'][] = $jDecode['email_secundario'];
            }
            if (isset($jDecode['endereco']) && isset($jDecode['endereco']['cidade'])) {

                /*
                $data['country_id'] = config('erp.address.default.country');

                $state = resolve('StateService')->findBy('code', $jDecode['endereco']['uf']);
                $data['state_id'] = isset($state) ? $state->id : config('erp.address.default.state');

                $city = resolve('CityService')->findBy('name', $jDecode['endereco']['cidade']);
                if (!$city && isset($jDecode['endereco']['cidade'])) {
                    $city = resolve('CityService')->create([
                        'state_id' => $data['state_id'],
                        'name' => $jDecode['endereco']['cidade']
                    ]);
                }
                $data['syncs']['address']['city_id'] = isset($city) ? $city->id : config('erp.address.default.city');

                $neighborhood = resolve('NeighborhoodService')->findBy('name', $jDecode['endereco']['bairro']);
                if (!$neighborhood && isset($jDecode['endereco']['bairro'])) {
                    $neighborhood = resolve('NeighborhoodService')->create([
                        'name' => $jDecode['endereco']['bairro']
                    ]);
                }
                $data['syncs']['address']['neighborhood_id'] = isset($neighborhood) ? $neighborhood->id : config('erp.address.default.neighborhood');

                $logradouro = explode(',', $jDecode['endereco']['logradouro']);
                $data['syncs']['address']['street'] = isset($logradouro[0])? $logradouro[0] : NULL;
                $data['syncs']['address']['number'] = isset($logradouro[1])? $logradouro[1] : NULL;
                //$data['syncs']['address']['complement'] = isset($logradouro[1])? explode('-', $logradouro[1]) : NULL;
                $data['syncs']['address']['postal_code'] = $jDecode['endereco']['cep'];

                $jDecode['sync'] = $data['syncs'];

                */

                $data['state_code'] = $jDecode['endereco']['uf'];
                $data['city'] = $jDecode['endereco']['cidade'];
                $data['neighborhood'] = $jDecode['endereco']['bairro'];
                $logradouro = explode(',', $jDecode['endereco']['logradouro']);
                $data['street'] = isset($logradouro[0]) ? $logradouro[0] : NULL;
                $data['number'] = isset($logradouro[1]) ? $logradouro[1] : NULL;
                $data['postal_code'] = $jDecode['endereco']['cep'];

                //$jDecode['sync']['address'] = resolve('AddressService')->prepareData($data);
                //dd($jDecode['sync']['address']);
            }

            if (isset($jDecode['email_vendedor'])) {
                $for_base = resolve('UserService')->findBy('email', $jDecode['email_vendedor']);
                if ($for_base) {
                    //$jDecode['attach']['for_base'] = $for_base->id;
                    $jDecode['syncWithoutDetaching']['for_base'] = $for_base->id;
                }
            }

            if (isset($jDecode['indicador'])) {
                //$indicator = resolve('UserService')->findBy('option.codigo_intranet', $jDecode['indicador']['codigo_intranet']);
                $indicator = resolve('UserService')->findBy('email', $jDecode['indicador']['email']);
                //dd($indicator);
                if ($indicator) {
                    //$jDecode['sync']['$indicator'] = $indicator->id;
                    $jDecode['sync']['indicator'] = $indicator->id;
                }
            }

            foreach ($jDecode['historico'] as $k => $historic) {
                $jDecode['attach']['historics'][$k] = [
                    'created_at' => new Carbon($historic['data'] . ' ' . $historic['hora']),
                    'title' => 'integrations.imported.from.gunther',
                    'data' => [
                        'action' => 'imported.from.gunther',
                        'content' => __('created.by') . ' ' . $historic['usuario'] . ' - ' . $historic['historico']
                    ]
                ];
            }

            unset($jDecode['historico']);
            unset($jDecode['endereco']);
            unset($jDecode['email']);

            if (
                !isset($jDecode['nome']) ||
                $jDecode['nome'] == 'null' ||
                $jDecode['nome'] == null ||
                $jDecode['nome'] == '' ||
                empty($jDecode['nome']) ||
                !is_string($jDecode['nome']) ||
                is_null($jDecode['nome'])
            ) {
                $jDecode['nome'] = 'Sem nome';
            }

            //$userCollect[] = $jDecode;
            $userCollect[] = array_merge_recursive($this->data, $jDecode);

        }
        return collect($userCollect);
    }

    public function fields()
    {
        $fileds = collect(isset($this->json_decode[0]) ? array_keys($this->json_decode[0]) : null)
            ->mapWithKeys(function ($item) {
                return [strtolower($item) => __(ucfirst($item))];
            });
        return $fileds;

    }

    public function test()
    {
        return ($this->file_get_contents) ? true : false;
    }
}
