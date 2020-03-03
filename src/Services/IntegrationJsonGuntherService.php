<?PHP

namespace ConfrariaWeb\IntegrationJsonGunther\Services;

use ConfrariaWeb\Historic\Models\Historic;
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
        if (!isset($this->data['endpoint']) || !isset($this->data['token'])) {
            return collect($userCollect);
        }
        $limit = $this->data['limit_y']?? 500; //Quantidade de registros
        $offset = $this->data['limit_x']?? 0; //Inicia deste registro
        $userCollect = LazyCollection::make(function () use($limit, $offset) {
            $client = new Client();
            $continua = true;
            while ($continua) {
                $url_m = $this->url($this->data, $limit, $offset);
                echo $url_m . ' | ';
                $response = $client->request('GET', $url_m);
                $lines = collect(json_decode($response->getBody(), true));
                $offset = $offset + $limit;
                $continua = ($lines->count() > 0);
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
                        $jDecode['syncWithoutDetaching']['for_base'] = $for_base->id;
                        //$jDecode['sync']['baseOwner'] = $for_base->id;
                    }
                }
                if (isset($line['indicador'])) {
                    $indicator = resolve('MeridienUserService')->findBy('email', $line['indicador']['email']);
                    if ($indicator) {
                        $jDecode['sync']['indicator'] = $indicator->id;
                    }
                }
                /*
                if (isset($line['historico'])) {
                    foreach ($line['historico'] as $k => $historic) {
                        $title = Str::title($historic['usuario'] . ' via sistema antigo');
                        $doesntExist = Historic::where('title', $title)
                            ->whereDate('created_at', $historic['data'])
                            ->whereTime('created_at', $historic['hora'])
                            ->doesntExist();
                        if ($doesntExist) {
                            $jDecode['attach']['historics'][$k] = [
                                'created_at' => new Carbon($historic['data'] . ' ' . $historic['hora']),
                                'title' => $title,
                                'data' => [
                                    'action' => 'imported.from.gunther',
                                    'content' => $historic['usuario'] . ' - ' . $historic['historico']
                                ]
                            ];
                        }
                    }
                }
                */
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
        $url = $this->url($this->data, 1, 0);
        if (isset($url)) {
            $client = new Client();
            $response = $client->request('GET', $url);
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

    public function url($options, $limit = 1, $offset = 0){
        if (!isset($options['endpoint']) || !isset($options['token'])) {
            return NULL;
        }
        $url = $options['endpoint'] . '?';
        $url .= 'token=' . $options['token'];
        if(isset($options['environment'])){
            $url .= '&environment=' . $options['environment'];
        }
        if(isset($options['function'])){
            $url .= '&function=' . $options['function'];
        }
        if(isset($options['status'])){
            $url .= '&status=' . $options['status'];
        }
        if(isset($options['param'])){
            $url .= '&param=' . $options['param'];
        }
        if(isset($options['value'])){
            $url .= '&value=' . $options['value'];
        }
        if(isset($options['emailVendedor'])){
            $url .= '&emailVendedor=' . $options['emailVendedor'];
        }
        $url .= '&limit=' . $offset . ',' . $limit;

        return $url;
    }
}
