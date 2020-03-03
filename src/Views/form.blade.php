<div class="form-row">
    <div class="col-8">
        <div class="form-group">
            <label class="control-label">EndPoint <span class="required"> * </span></label>
            {!! Form::text('options[data][endpoint]', 'https://www.meridienclube.com.br/sinc-bases/associados-list', ['class' => 'form-control', 'placeholder' => 'EndPoint da integração', 'required']) !!}
            <small>https://www.meridienclube.com.br/sinc-bases/associados-list</small>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label class="control-label">Token <span class="required"> * </span></label>
            {!! Form::text('options[data][token]', $integration->options['data']['token']?? 'intra2SGM', ['class' => 'form-control', 'placeholder' => 'Digite a URL do JSON', 'required']) !!}
            <small>Token de validação de acesso.</small>
        </div>
    </div>
</div>
<div class="form-row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label"> Environment </label>
            {!! Form::select('options[data][environment]', [NULL => 'Escolha uma opção', 'development' => 'Development', 'testing' => 'Testing', 'production' => 'Production'], $integration->options['data']['environment']?? NULL, ['class' => 'form-control', 'required']) !!}
            <small>
                <ul style="padding-left: 20px;">
                    <li>Development: Não atualiza o status de sincronia da tabela de controle do Intranet</li>
                    <li>Testing: Mesmo comportamento de "production"</li>
                    <li>Production: Atualiza o status de sincronia da tabela de controle do Intranet</li>
                </ul>
            </small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label"> Function </label>
            {!! Form::select('options[data][function]', [NULL => 'Escolha uma opção', 'create' => 'Create', 'update' => 'Update', 'retrieve' => 'Retrieve', 'searchBy' => 'Search By', 'listBase' => 'List Base'], $integration->options['data']['function']?? NULL, ['class' => 'form-control', 'required']) !!}
            <small>
                <ul style="padding-left: 20px;">
                    <li>Create: Busca por casos a ser cadastrados no SGM (novos associados Intranet)</li>
                    <li>Update: Busca por casos a ser atualizados no SGM (alterações Intranet)</li>
                    <li>Retrieve: Lista todos os casos do Intranet</li>
                    <li>Search By: Busca por algum registro específico no Intranet</li>
                    <li>List Base: Busca por todos os registros de associados de um vendedor específicou</li>
                </ul>
            </small>
        </div>
    </div>
</div>
<div class="form-row">
    <div class="col-6">
        <div class="form-group">
            <label class="control-label"> Status </label>
            {!! Form::select('options[data][status]', [NULL => 'Escolha uma opção', 'a' => 'Associados Ativos', 'i' => 'Associados Inativos', '*' => 'Todos Associados'], $integration->options['data']['status']?? NULL, ['class' => 'form-control', 'required']) !!}
            <small>
                <ul style="padding-left: 20px;">
                    <li>Somente sob 'retrieve'</li>
                    <li>Busca associados Ativos</li>
                    <li>Busca associados Inativos</li>
                    <li>Busca todos associados</li>
                </ul>
            </small>
        </div>
    </div>
    <div class="col-3">
        <div class="form-group">
            <label class="control-label"> Limit X</label>
            {!! Form::text('options[data][limit_x]', $integration->options['data']['limit_x']?? NULL, ['class' => 'form-control', 'placeholder' => 'Limit X']) !!}
            <small>
                Somente sob 'retrieve'.
                Informar limit "X", que será o offset para paginação.
            </small>
        </div>
    </div>
    <div class="col-3">
        <div class="form-group">
            <label class="control-label"> Limit Y</label>
            {!! Form::text('options[data][limit_y]', $integration->options['data']['limit_y']?? NULL, ['class' => 'form-control', 'placeholder' => 'Limit Y']) !!}
            <small>
                Somente sob 'retrieve'.
                Informar limit "Y" para quantidade de registros buscados por página.
            </small>
        </div>
    </div>
</div>
<div class="form-row">
    <div class="col-4">
        <div class="form-group">
            <label class="control-label">Param</label>
            {!! Form::select('options[data][param]', [NULL => 'Escolha uma opção', 'codigo' => 'Código', 'cpf' => 'CPF', 'nome' => 'Nome', 'email' => 'E-mail', 'emailSecundario' => 'E-mail Secundário'], $integration->options['data']['param']?? NULL, ['class' => 'form-control']) !!}
            <small>
                <ul style="padding-left: 20px;">
                    <li>Somente sob 'searchBy'</li>
                    <li>Código do associado no Intranet</li>
                    <li>Cpf do associado no Intranet</li>
                    <li>Nome (ou parte do nome) do associado no Intranet</li>
                    <li>E-mail principal do associado no Intranet</li>
                    <li>E-mail secundário do associado no Intranet</li>
                </ul>

            </small>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label class="control-label">Value</label>
            {!! Form::text('options[data][value]', $integration->options['data']['value']?? NULL, ['class' => 'form-control']) !!}
            <small>
                Somente sob 'searchBy', aceita qualquer valor que será buscado de acordo com "param".
            </small>
        </div>
    </div>
    <div class="col-4">
        <div class="form-group">
            <label class="control-label">E-mail Vendedor</label>
            {!! Form::text('options[data][emailVendedor]', $integration->options['data']['emailVendedor']?? NULL, ['class' => 'form-control']) !!}
            <small>
                Somente sob 'listBase', informe o email do vendedor que deseja listar a base.
            </small>
        </div>
    </div>
</div>
<div class="form-row">
    <div class="col-12 text-right">
        <a href="{{ resolve('IntegrationJsonGuntherService')->url($integration->options['data'], 1, 1) }}" target="_blank">
            >>> Url da integração para testes
        </a>
    </div>
</div>
<hr>

