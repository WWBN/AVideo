# Revisão independente das alterações de 2026-10-09

Revisão realizada em 2026-10-10, incluindo o diff staged, o diff unstaged e os caminhos reais de execução. As referências abaixo apontam para a árvore final; os cenários descrevem os defeitos encontrados antes das correções desta revisão. A gravidade é uma avaliação qualitativa, sem atribuição de CVSS.

## Achados confirmados, por gravidade

1. **Alta — CONFIRMED — conexão fechada durante uma transação de pagamento.** Referências: `plugin/YPTWallet/YPTWallet.php:397`, `objects/functionsMySQL.php:140`, `objects/functionsMySQL.php:236`, `plugin/YPTSocket/YPTSocket.php:312`. A gravação de saldo envia notificações; o caminho antigo do socket chama _mysql_close(). Em requisições externas com conexão não persistente, o fechamento era permitido. Isso perde a transação e o GET_LOCK; operações seguintes do DAL podem reconectar e gravar em autocommit, deixando saldo, histórico e marcadores sem atomicidade. O teste de conexão falhava antes da proteção; a integração executa a chamada de fechamento dentro de um pagamento e verifica que a mesma conexão permanece aberta.

2. **Média — CONFIRMED — falha SQL tratada como renovação opcional malsucedida.** Referências: `plugin/YPTWallet/YPTWallet.php:496`, `objects/mysql_dal.php:53`, `objects/mysql_dal.php:170`, `objects/mysql_dal.php:389`. Uma tabela ausente ou erro de execução durante a renovação podia resultar em false ou em uma exceção absorvida, seguida do commit do crédito e do marcador de pagamento. O provedor receberia 200 em vez de uma falha para tentar novamente. A reprodução com MariaDB falhou antes da correção nos modos estrito e não lançador do mysqli. Também foi reproduzido um erro capturado pelo plugin, incluindo erros informados apenas ao buscar resultados. Falhas de negócio continuam desfazendo somente a renovação; falhas SQL agora desfazem todo o pagamento.

3. **Média — CONFIRMED — capability de upload do Encoder no log.** Referências: `objects/aVideoEncoder.json.php:119`, `objects/video.php:4320`. O resumo removia senhas e video_id_hash, mas conservava encoderChunkToken dentro de return_vars. Video::queue gera esse token para EncoderChunkUpload, com validade padrão de sete dias. Quem consegue ler o log recebe uma credencial de upload; não foi presumido acesso público ao log. O teste falhou ao encontrar o token antes da correção e agora verifica sua remoção sem alterar a requisição usada pelo Encoder.

4. **Média — CONFIRMED — fallback de CloneSite aceitava dump inválido ou parcial.** Referências: `plugin/CloneSite/cloneClient.json.php:122`, `objects/functions.php:2094`, `objects/functions.php:2264`, `objects/functions.php:4775`, `objects/functionsExec.php:95`. Depois de wget rejeitar um download, url_get_contents/cURL podia retornar o corpo de um HTTP 404/500 ou substituir a URL remota por um arquivo local antigo com caminho equivalente. Uma gravação parcial, tanto no wget do Windows quanto em _file_put_contents no fallback, também deixava um arquivo não vazio elegível para restore. Os testes reproduziram esses casos antes da correção. O modo estrito agora exige resposta 2xx, evita a substituição local e descarta gravações incompletas.

5. **Média — CONFIRMED — caller de TLS omitido e diagnósticos com chave de CloneSite.** Referências: `plugin/CloneSite/cloneClient.json.php:82`, `plugin/CloneSite/cloneClient.json.php:292`, `objects/functions.php:4733`. A notificação para excluir o dump não recebia verifySSL, mesmo com verificação habilitada pelo administrador. Os caminhos de erro do fetch também podiam registrar a URL com a chave quando verifyTLS era false. Os testes falharam por ausência dos argumentos de política e por exposição de uma chave sintética. Todos os pedidos sensíveis de CloneSite agora recebem a política configurada e o modo estrito, que também suprime URLs nos diagnósticos. O padrão para certificados privados permanece preservado.

6. **Média — CONFIRMED — novo retorno de wget aceitava download fracassado no Linux.** Referência: `objects/functionsExec.php:123`. A correção do caminho usado por file_exists tornou possível retornar true mesmo quando wget terminou com erro e deixou arquivo parcial, ou quando o arquivo estava vazio. Importações e fallbacks que dependem do booleano poderiam consumir esse conteúdo. Os testes falhavam para saída não zero e arquivo vazio. Agora esses arquivos são removidos e o retorno é false; a exceção existente para a resposta vazia de Live/m3u8.php foi mantida.

## Pontos plausíveis e item preexistente

- **Baixa — PLAUSIBLE — savepoint reutilizado em chamadas aninhadas.** Referência: `plugin/YPTWallet/YPTWallet.php:504`. Se uma renovação passasse a chamar runPaymentStep novamente, o savepoint fixo poderia substituir o ponto externo. Não foi encontrado um caller aninhado nos plugins examinados, inclusive Subscription. Nenhuma alteração foi feita com base nessa hipótese.
- **Média operacional — PLAUSIBLE — latência de callbacks sob concorrência.** Referência: `plugin/YPTWallet/YPTWallet.php:413`. O lock serializa pagamentos e espera até dez segundos; uma entrega concorrente pode ultrapassar a janela de cinco segundos do Razorpay informada no pedido. Os testes verificam lock ocupado e idempotência concorrente, mas não o comportamento do provedor real. A possibilidade de retries adicionais permanece; não foi alterada a política de espera.
- **Preexistente, fora das correções por limite expresso:** `plugin/StripeYPT/StripeYPT.php:932`, correlação de invoice.payment_succeeded e charge.succeeded em payloads Stripe 2025-03-31+. Quando os payloads não ligam os IDs, permanece possível processar duas vezes. Uma solução exige decisão do mantenedor sobre a versão da API e a estratégia de correlação; nenhuma mudança foi feita nessa área.

## Correções realizadas

- `objects/functionsMySQL.php` e `plugin/YPTWallet/YPTWallet.php`: fixam a conexão durante o processamento, impedem fechamento/reconexão e distinguem falhas SQL de falhas opcionais de negócio.
- `objects/mysql_dal.php`: registra e propaga falhas de prepare, bind, execute e leitura de resultados dentro do escopo de pagamento. A sinalização persiste mesmo se um plugin capturar a exceção; statements com erro são fechados quando necessário. Fora desse escopo, os retornos e a recuperação existentes permanecem.
- `objects/aVideoEncoder.json.php`: inclui encoderChunkToken na redação do resumo da requisição e de return_vars.
- `objects/functions.php`: acrescenta failOnError aos helpers existentes, propaga-o nos redirects/fallbacks, exige 2xx e evita arquivos locais substitutos e URLs sensíveis nos logs desse modo.
- `objects/functionsExec.php` e `plugin/CloneSite/cloneClient.json.php`: verificam conclusão da transferência e da escrita; a clonagem aplica a política de TLS também na notificação final.
- Novos testes: `EncoderRequestLogRedactionTest.php`, `PaymentConnectionGuardTest.php`, `WgetCompletionTest.php`, `WgetWindowsCompletionTest.php` e `CloneSiteDownloadRegressionTest.php`. Também foram ampliados `VerifiedRemoteFetchTest.php` e a integração de pagamentos. A fixture usa o DAL real, substitui partes do framework e não carrega configuração de produção.

## Verificações e resultados

Comandos executados com PHP 8.2.12, PHPUnit 9.6.33 e MariaDB 10.4.32:

| Comando | Resultado final |
| --- | --- |
| php -l em cada PHP alterado ou novo | 40 arquivos, sem erro |
| composer test --no-interaction -- --configuration D:/git/htdocs/AVideo/phpunit.xml --do-not-cache-result | 859 testes, 5590 assertions, 46 skipped, exit 0 |
| php vendor/bin/phpunit --do-not-cache-result tests/Security | 392 testes, 1106 assertions, passou |
| php vendor/bin/phpunit --do-not-cache-result --testsuite "Unit Tests" | 420 testes, 4478 assertions, passou |
| AVIDEO_PAYMENT_MYSQL_TESTS=1 AVIDEO_TEST_DB_PORT=34087 php vendor/bin/phpunit --do-not-cache-result tests/Integration/PaymentWalletMySQLIntegrationTest.php | 17 testes, 93 assertions, passou |
| git -c core.whitespace=cr-at-eol diff --check | passou |
| git -c core.whitespace=cr-at-eol diff --cached --check | passou |

O Composer inicialmente falhou porque PHP não reconhecia o diretório raiz como diretório acessível neste sandbox. Foi executado de tests/, com COMPOSER apontando ao composer.json original e COMPOSER_VENDOR_DIR ao vendor original, além da configuração explícita acima. Não foram alterados manifesto ou dependências. Uma tentativa inicial da suíte também encontrou o diretório de sessões padrão do XAMPP sem permissão; as execuções finais usaram PHP_INI_SCAN_DIR com session.save_path temporário. O Composer antigo emitiu avisos de depreciação, sem falha dos testes.

Os testes skipped são integrações opt-in sem ambiente habilitado. A integração de pagamentos foi executada separadamente em um MariaDB criado exclusivamente para esta revisão, vinculado a 127.0.0.1:34087, com databases aleatórios criados e removidos pelos testes. Os testes de regressão produziram falhas antes das respectivas correções; não houve enfraquecimento de asserts para obter sucesso.

Também foram lidos os caminhos de Encoder remoto/Docker e suas credenciais por Streamer, SMTP, WordPress, BTCPay, callbacks Stripe/Razorpay, dispatch DiskUploadQuota, transferências e listagem de histórico, Subscription distribuído separadamente, logs RTMP e cache Windows/Linux. Não foi encontrada outra regressão confirmada nesses caminhos. A criação simultânea da mesma carteira por transferências independentes, provedores reais, RTMP ao vivo, clonagem integral, envio SMTP e TLS com certificados reais não receberam teste end-to-end; leitura de código e testes isolados não substituem essa validação.

As mudanças desta revisão permanecem unstaged e sem commit ou push. Nenhum arquivo de configuração de produção, vendor, migração publicada ou regra .github foi modificado nesta revisão. As alterações que já estavam staged, inclusive nas regras .github e no relatório anterior, foram preservadas.

O servidor MariaDB descartável foi encerrado com mysqladmin shutdown; seu diretório temporário e os demais arquivos temporários de execução foram removidos.
