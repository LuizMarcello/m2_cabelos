<?php

namespace AbraaoMarques\Correios\Controller\Search;

/*Assim, até o magento 2.3*/
//use Magento\Framework\App\Action\Action;

/*A partir do magento 2.4, é usando interfaces*/
/*Assim, para método "get"(urls no navegador)*/

//use Magento\Framework\App\Action\HttpGetActionInterface;

use AbraaoMarques\Correios\Model\Quotation as ModelQuotation;

use Magento\Framework\Controller\Result\JsonFactory;

/*Assim para método "post"(formulários)*/

use Magento\Framework\App\Action\HttpPostActionInterface;

/*É responsável por pegar os dados que possam vir via POST ou via GET*/

use Magento\Framework\App\RequestInterface;

//class Quotation implements HttpGetActionInterface
class Quotation implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ModelQuotation
     */
    private $modelQuotation;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;


    /**
     * @param RequestInterface $request
     * @param ModelQuotation $modelQuotation
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        RequestInterface $request,
        ModelQuotation   $modelQuotation,
        JsonFactory      $jsonFactory
    )

    {
        $this->modelQuotation = $modelQuotation;
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
    }


    public function execute()
    {
        /*$data: Seus valores vem lá do script.js*/
        /*getParams() retorna um array*/
        $data = $this->request->getParams(); /*Antes era getParam()*/
//        var_dump($data);
//        exit();
        /*Este 'zipcode' é o "name" do <input> no template product.phtml*/
//        $zipcode = $this->request->getParam('zipcode');
        $zipcode = $data['zipcode'];
        /*O ?? é conhecido como null coalescing. Retorna o primeiro operando
          se ele existir e não for nulo, do contrário retorna o segundo*/
        $weight = $data['weight'] ?? null;
        $width = $data['width'] ?? null;
        $height = $data['height'] ?? null;
        $length = $data['length'] ?? null;
        /*Este método search() é do model Quotation.php*/
        $result = $this->modelQuotation->inProductPage($zipcode, $weight, $height, $width, $length);
        /*Criando a fábrica para o jsonFactory. Todo o resultado será envelopado em json,
          para poder exibir no frontend, através do javascript.*/
        $json = $this->jsonFactory->create();
        return $json->setData($result);
        /*Todo esse conteúdo que está sendo passado via json,
          será o "response" no script.js */


    }
}


