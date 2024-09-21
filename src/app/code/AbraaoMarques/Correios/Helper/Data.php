<?php

namespace AbraaoMarques\Correios\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    const BASE_CONFIG_PATH = 'carriers/abraaomarques_correios/';
    const BASE_VALUE_WEIGHT = 1;
    const BASE_VALUE_WIDTH = 16;
    const BASE_VALUE_HEIGHT = 2;
    const BASE_VALUE_LENGTH = 11;

    protected $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Context $context, ScopeConfigInterface $scopeConfig)
//    public function __construct(Context $context, ScopeInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /*Método para abstrair, para não ficar repetindo muito o mesmo path*/
    /**
     * @param $value
     * @return string|null
     */
    private function getValue($value): ?string
    {
        return $this->scopeConfig->getValue(self::BASE_CONFIG_PATH . $value, ScopeInterface::SCOPE_STORE);
    }

    /*Métodos abaixo, para pegar informações do banco de dados,
      para posterior tratamento*/

    /*Método para pegar a URL dos correios*/
    /**
     * @return string|null
     */
    public function getWebService(): ?string
    {
        return $this->getValue('webservice_url');
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->getValue('login');
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->getValue('password');
    }

    /**
     * @return string|null
     */
    public function getPostingMethods(): ?string
    {
        return $this->getValue('posting_methods');
    }

//    /**
//     * @return string|null
//     */
//    public function getTitle(): ?string
//    {
//        return $this->getValue('title');
//    }

    /**
     * @return string|null
     */
    public function getMaxWeight(): ?string
    {
        return $this->getValue('max_weight');
    }

    /**
     * @return float|null
     */
    public function getIncreaseDeliveryDays(): ?float
    {
        return $this->getValue('increase_delivery_days');
    }

    /**
     * @return float|null
     */
    public function getEnableLog(): ?float
    {
        return $this->getValue('enabled_log');
    }

    /**
     * @return float|null
     */
    private function getDefaultWeight(): ?float
    {
        return $this->getValue('default_weight');
    }

    /**
     * @return float|null
     */
    private function getDefaultWidth(): ?float
    {
        return $this->getValue('default_width');
    }

    /**
     * @return float|null
     */
    private function getDefaultHeight(): ?float
    {
        return $this->getValue('default_height');
    }

    /**
     * @return float|null
     */
    private function getDefaultLength(): ?float
    {
        return $this->getValue('default_length');
    }

    /*Retorna finalmente, o valor do peso do produto,
      para poder efetuar o cálculo do frete*/
    /**
     * @param $weight
     * @return float
     */
    public function getWeight($weight): float
    {
        /*Se existir o peso cadastrado no próprio produto*/
        if ($weight)
            return $weight;

        /*Senão, pega do admin*/
        $defaultWeight = $this->getDefaultWeight();
        if ($defaultWeight)
            return $defaultWeight;

        /*Senão, pega desta constante, criada acima*/
        return self::BASE_VALUE_WEIGHT;
    }

    /**
     * @param $width
     * @return float
     * Retorna finalmente, o valor da largura do produto,
     * para poder efetuar o cálculo do frete
     */
    public function getWidth($width): float
    {
        /*Pegando do admin*/
        $defaultWidth = $this->getDefaultWidth();
        /*Se não existir a largura e nem a largura padrão*/
        if (!$width && !$defaultWidth)
            return self::BASE_VALUE_WIDTH;

        /*Se não existir largura ou se largura for menor que a largura default*/
        if (!$width || $width < $defaultWidth)
            return $defaultWidth;

        return $width;
    }

    /**
     * @param $height
     * @return float
     * Retorna finalmente, o valor da altura do produto,
     * para poder efetuar o cálculo do frete
     */
    public function getHeight($height): float
    {
        /*Pegando do admin*/
        $defaultHeight = $this->getDefaultHeight();
        /*Se não existir a altura e nem a altura padrão*/
        if (!$height && !$defaultHeight)
            return self::BASE_VALUE_HEIGHT;

        /*Se não existir altura ou se altura for menor que a altura default*/
        if (!$height || $height < $defaultHeight)
            return $defaultHeight;

        return $height;
    }

    /**
     * @param $length
     * @return float
     * Retorna finalmente, o valor do comprimento do produto,
     * para poder efetuar o cálculo do frete
     */
    public function getLength($length): float
    {
        /*Pegando do admin*/
        $defaultLength = $this->getDefaultLength();
        /*Se não existir o comprimento e nem o comprimento padrão*/
        if (!$length && !$defaultLength)
            return self::BASE_VALUE_LENGTH;

        /*Se não existir comprimento ou se comprimento for menor que o comprimento default*/
        if (!$length || $length < $defaultLength)
            return $defaultLength;

        return $length;
    }

    /**
     * @return string
     */
    private function getOriginPostCode(): string
    {
        return $this->scopeConfig->getValue('shipping/origin/postcode', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $zipcode
     * @param $weight
     * @param $height
     * @param $width
     * @param $length
     * @return string
     */
    public function createEndPoint($zipcode, $weight, $height, $width, $length, $method): string
    {
        $webservice = $this->getWebService();
        $origin = $this->getOriginPostCode();
        $login = $this->getLogin();
        $password = $this->getPassword();

        $weight = $this->getWeight($weight);
        $height = $this->getHeight($height);
        $width = $this->getWidth($width);
        $length = $this->getLength($length);

        $hasContract = null;

        if ($login && $password)
            $hasContract = '&nCdEmpresa=' . $login . '&sDsSenha=' . $password;

        return $webservice . $hasContract . '&nCdFormato=1&nCdServico=' . $method . '&nVlComprimento=' . $length . '&nVlAltura=' . $height . '&nVlLargura=' . $width . '&sCepOrigem=' . $origin . '&sCdMaoPropria=N&sCdAvisoRecebimento=N&nVlPeso=' . $weight . '&sCepDestino=' . $zipcode;
    }
}
