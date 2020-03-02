<?PHP

namespace ConfrariaWeb\IntegrationJsonGunther\Services;

use ConfrariaWeb\Integration\Services\Contracts\IntegrationContract;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class IntegrationJsonGuntherService implements IntegrationContract
{
    protected $data = [];

    public function set(Array $data)
    {
        try {
            $this->data = $data;
        } catch (Exception $e) {
            Log::error('Erro ao tentar importar informações do sistema Gunther');
        }
    }

    public function get()
    {
        $userCollect = [];
        if (!$this->data['url']) {
            return collect($userCollect);
        }
        $url = $this->data['url'];
        $userCollect = LazyCollection::make(function () use ($url) {
            $limit = 500; //Quantidade de registros
            $offset = 0; //Inicia deste registro
            $client = new Client();
            $continua = true;
            while ($continua) {
                $url_m = $url . '&limit=' . $offset . ',' . $limit;
                $response = $client->request('GET', $url_m);
                $lines = collect(json_decode($response->getBody(), true));
                $offset = $offset + $limit;
                $continua = ($lines->count() > 0);
                //$continua = ($lines->count() > 0 && $offset < 10);
                foreach ($lines as $line) {
                    yield $line;
                }
            }
        })
            ->map(function ($line) {
                $jDecode = [];
                if (isset($line['codigo_intranet']) && !empty($line['codigo_intranet'])) {
                    $jDecode['sync']['optionsValues']['intranet_code'] = $line['codigo_intranet'];
                }
                if (isset($line['telefone']) && !empty($line['telefone'])) {
                    $jDecode['sync']['contacts']['phone'] = $line['telefone'];
                }
                if (isset($line['telefone_celular']) && !empty($line['telefone_celular'])) {
                    $jDecode['sync']['contacts']['cellphone'] = $line['telefone_celular'];
                }
                if (isset($line['email']) && !empty($line['email'])) {
                    $jDecode['sync']['contacts']['email'][] = $line['email'];
                }
                if (isset($line['email_secundario']) && !empty($line['email_secundario'])) {
                    $jDecode['sync']['contacts']['email'][] = $line['email_secundario'];
                }
                if (isset($line['email_vendedor'])) {
                    $for_base = resolve('UserService')->findBy('email', $line['email_vendedor']);
                    if ($for_base) {
                        //$jDecode['syncWithoutDetaching']['for_base'] = $for_base->id;
                        $jDecode['sync']['baseOwner'] = $for_base->id;
                    }
                }
                if (isset($line['indicador'])) {
                    $indicator = resolve('MeridienUserService')->findBy('email', $line['indicador']['email']);
                    if ($indicator) {
                        $jDecode['sync']['indicator'] = $indicator->id;
                    }
                }
                /*Tenho que validar por data aqui para nao entrar repetidos*/
                if (isset($line['historico'])) {
                    foreach ($line['historico'] as $k => $historic) {
                        $jDecode['attach']['historics'][$k] = [
                            'created_at' => new Carbon($historic['data'] . ' ' . $historic['hora']),
                            'title' => Str::title($historic['usuario'] . ' via sistema antigo'),
                            'data' => [
                                'action' => 'imported.from.gunther',
                                'content' => $historic['usuario'] . ' - ' . $historic['historico']
                            ]
                        ];
                    }
                }

                if (
                    !isset($line['nome']) ||
                    empty($line['nome']) ||
                    !is_string($line['nome']) ||
                    is_null($line['nome'])
                ) {
                    $jDecode['nome'] = isset($line['email']) ? stristr($line['email'], '@', true) : 'Sem nome';
                }
                unset($line['historico']);
                unset($line['endereco']);
                unset($line['email']);
                return array_merge_recursive($jDecode, $line);
            });

        return collect($userCollect->all());
    }

    public function fields()
    {
        $fileds = [];
        if (isset($this->data['url'])) {
            $client = new Client();
            $response = $client->request('GET', $this->data['url'] . '&limit=100,1');
            $this->json_decode = json_decode($response->getBody(), true);
            $fileds = collect(array_keys($this->json_decode[0]))->mapWithKeys(function ($item) {
                return [strtolower($item) => str_replace('_', ' ', ucfirst($item))];
            });
        }
        return $fileds;

    }

    public function test()
    {
        return true;
    }
}
