<?php

namespace AbraaoMarques\Correios\Api;

interface SearchQuotationServiceInterface
{
    /**
     * @param $url
     * @return object|null
     */
    public function search($url): ?object;
}
