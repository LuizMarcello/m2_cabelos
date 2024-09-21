<?php
/*Esta model será responsável por retornar todas as informações de
  frete lá na página do checkout, do carrinho, finalização do pedido. */

namespace AbraaoMarques\Correios\Model\Carrier;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use AbraaoMarques\Correios\Helper\Data;
use AbraaoMarques\Correios\Model\Quotation;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Shipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'abraaomarques_correios';
    protected $_rateResultFactory;
    protected $_rateMethodFactory;
    private $helper;
    private $quotation;
    private $productInterface;


    public function __construct(
        ProductRepositoryInterface                                 $productInterface,
        Quotation                                                  $quotation,
        Data                                                       $helper,
        ResultFactory                                              $rateResultFactory,
        MethodFactory                                              $rateMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface         $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface                                   $logger,
        array                                                      $data = []
    )
    {
        $this->productInterface = $productInterface;
        $this->quotation = $quotation;
        $this->helper = $helper;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return DataObject|Result|bool|null
     * @throws NoSuchEntityException
     *
     * Aqui ficará o cálculo para exibir no carrinho.
     * Também fazendo uma validação do total do peso dos itens do carrinho.
     */
    public function collectRates(RateRequest $request): DataObject|Result|bool|null
    {
        /*Verificando se o módulo está ativo ou não*/
        if (!$this->getConfigFlag('active'))
            return false;


        $helper = $this->helper;
        /*Pegando o peso total de todos os itens do carrinho.*/
        $totalWeight = $request->getPackageWeight();

        /*getMaxWeight(): Peso máximo previamente setado no admin.*/
        if ($totalWeight > $helper->getMaxWeight()) {
            $this->_logger->warning('Total weight is not allowed');
            return false;
        }

        /*getDestPostcode(): É através deste método que o magento
          pega o cep de destino, da página do produto.*/
        $destinationPostCode = $request->getDestPostcode();

        /*"$data" receberá todos os itens que estão no carrinho.*/
        $data = $request->getAllItems();
        $products = $this->getProductInformation($data);
        $totalHeight = null;
        $totalWidth = null;
        $totalLength = null;

        foreach ($products as $product) {
            $totalHeight += $helper->getHeight($product['height']);
            $totalWidth += $helper->getHeight($product['width  ']);
            $totalLength += $helper->getHeight($product['length ']);
        }

        $searchResult = $this->quotation->search($destinationPostCode, $totalWeight, $totalHeight, $totalWidth, $totalLength);

        /*Se a opção de ativar logs para todas as consultas do correio estiver ativa, no admin*/
        if ($helper->getEnableLog())
            /*"$searchResult" é um array, mas "info()" exige
               uma string, então transforma em um "json".*/
            $this->_logger->info(json_encode($searchResult));

        $result = $this->_rateResultFactory->create();
        $methodName = Quotation::METHOD_NAME_SEDEX;

        /*Exibir as informações na página do carrinho.*/
        foreach ($searchResult as $item) {
            /*Se no array, for encontrado os valores que constam na
              variável "pac", então $methodName receberá o valor
              da constante "METHOD_NAME_PAC" declarada no model Quotation.php.
              Se não encontrar, ele continuará como sedex.*/
            if (in_array($item->Codigo, $this->quotation->pac))
                $methodName = Quotation::METHOD_NAME_PAC;

            $method = $this->_rateMethodFactory->create();
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setCarrier($this->_code);
            $method->setMethodTitle($methodName);
            $method->setMethod($methodName);

            $amount = $item->Valor;
            $method->setPrice($amount);
            $method->setCost($amount);

            $result->append($method);
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @param $data
     * @return array
     * @throws NoSuchEntityException
     *
     * Método para conseguir todas as informações do produto
     */
    private function getProductInformation($data): array
    {
        $product = [];
        foreach ($data as $value) {
            /*Fazendo um load das informações do produto, pelo seu sku*/
            $productRepository = $this->productInterface->get($value->getSku());

            $product[] = [
                'height' => $productRepository->getData('height'),
                'width  ' => $productRepository->getData('width  '),
                'length ' => $productRepository->getData('length '),
            ];
        }
        return $product;
    }
}
