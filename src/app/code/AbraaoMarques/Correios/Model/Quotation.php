<?php

namespace AbraaoMarques\Correios\Model;

use AbraaoMarques\Correios\Helper\Data;
use AbraaoMarques\Correios\Api\SearchQuotationServiceInterface;

class Quotation
{
    const METHOD_NAME_SEDEX = 'Sedex';
    const METHOD_NAME_PAC = 'Pac';
    public $pac = [41106, 4669];
    private $helper;
    private $quotationService;

    /**
     * @param Data $helper
     * @param SearchQuotationServiceInterface $quotationService
     */
    public function __construct(
        Data                            $helper,
        SearchQuotationServiceInterface $quotationService
    )
    {
        $this->helper = $helper;
        $this->quotationService = $quotationService;
    }

    /**
     * @param $zipcode
     * @param $weight
     * @param $height
     * @param $width
     * @param $length
     * @return array
     *
     * Este método será responsável por consumir a service SearchQuotationService,
     * que fará então a consulta aos correios, e retornará o resultado.
     */
    public function search($zipcode, $weight, $height, $width, $length): array
    {
        /*Instanciando o objeto "Data", dentro de "$helper"*/
        $helper = $this->helper;
        /*$methods(que será um array), receberá os códigos que foram selecionados
          no admin, e que estão armazenados no BD, através de $helper(helper Data.php) */
        $methods = explode(",", $helper->getPostingMethods());
        $result = [];

        foreach ($methods as $method) {
            $url = $helper->createEndPoint($zipcode, $weight, $height, $width, $length, $method);
//            return $url;
            /*Assim, estará concatenando o conteúdo da variável
              com os dados recebidos.*/
            /*Este search() é da camada service "SearchQuotationService."*/
            $result[] = $this->quotationService->search($url);
        }
        return $result;
    }

    /**
     * @param $zipcode
     * @param $weight
     * @param $height
     * @param $width
     * @param $length
     * @return ?string
     *
     *  E este método contém a lógica necessária para pegar o resultado da
     *  consulta e exibir as informações de cotação no frontend, na página do produto.
     */
    public function inProductPage($zipcode, $weight, $height, $width, $length): ?string
    {
        /*Método search() acima, que retorna um array para $data.*/
        $data = $this->search($zipcode, $weight, $height, $width, $length);
        $count = count($data);
        /*Constante METHOD_NAME_SEDEX declarada acima*/
        /*$methodName será como "sedex"*/
        $methodName = self::METHOD_NAME_SEDEX;
        $result = null;

        for ($i = 0; $i < $count; $i++) {
            /*Se na posição "i" do array, for encontrado os valores que constam na
              variável "pac", declarada acima, então $methodName receberá o valor
              da constante "METHOD_NAME_PAC" declarada acima. Se não encontrar, ele
              continuará como sedex.*/
            if (in_array($data[$i]->Codigo, $this->pac))
                $methodName = self::METHOD_NAME_PAC;

            $days = $data[$i]->PrazoEntrega;
            $addDays = $this->helper->getIncreaseDeliveryDays();

            /*Se tiver algum valor retornado para "$addDays".*/
            if ($addDays)
                $days = $days + $addDays;

            $result .= "<span>{$methodName} - Em média {$days} dia(s) <strong>R$ {$data[$i]->Valor}</strong></span>";
        }
        return $result;
    }
}



