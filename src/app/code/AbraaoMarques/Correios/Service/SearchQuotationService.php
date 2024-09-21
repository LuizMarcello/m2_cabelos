<?php
/*O service é a camada responsável por se comunicar
  com os correios, e retornar as informações.*/

namespace AbraaoMarques\Correios\Service;

use AbraaoMarques\Correios\Api\SearchQuotationServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class SearchQuotationService implements SearchQuotationServiceInterface
{

    /**
     * @param $url
     * @return object|null
     * @throws \Exception
     */
    public function search($url): ?object
    {
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/xml'
        ];
        $request = new Request('GET', $url, $headers);
        $res = $client->sendAsync($request)->wait();
        /*Estes são métodos do "Guzzle"*/
        /*$result está recebendo o resultado da busca aos correios como string*/
        $result = $res->getBody()->getContents();

        /*Convertendo então para objeto XML e retornando*/
//        return new \SimpleXMLElement($result);
        $data = new \SimpleXMLElement($result);

        return $data->cServico;
    }
}
