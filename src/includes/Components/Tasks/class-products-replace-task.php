<?php

namespace ChannelEngine\Components\Tasks;

use ChannelEngine\BusinessLogic\API\Http\Exceptions\RequestNotSuccessfulException;
use ChannelEngine\BusinessLogic\Products\Contracts\ProductsService;
use ChannelEngine\BusinessLogic\Products\Tasks\ProductsReplaceTask;
use ChannelEngine\Components\Services\Replace_Products_Service;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpCommunicationException;
use ChannelEngine\Infrastructure\Http\Exceptions\HttpRequestException;
use ChannelEngine\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use ChannelEngine\Infrastructure\ServiceRegister;

/**
 * Class Products_Replace_Task
 *
 * @package ChannelEngine\Components\Tasks
 */
class Products_Replace_Task extends ProductsReplaceTask
{
    /**
     * @inheritDoc
     */
    protected function exportProducts(&$batchOfProducts, $syncedProducts)
    {
        $syncConfig = $this->getProductsSyncConfigService()->get();
        if( $syncConfig === null || $syncConfig->isEnabledStockSync() ) {
            $this->export($batchOfProducts);
        } else {
            $this->exportWithoutStock($batchOfProducts);
        }

        $this->syncedNumber = $syncedProducts;
    }

    /**
     * @return ProductsService
     */
    protected function getProductsService()
    {
        return ServiceRegister::getService(Replace_Products_Service::class);
    }

    /**
     * @param array $batchOfProducts
     * @return void
     * @throws HttpRequestException
     * @throws RequestNotSuccessfulException
     * @throws HttpCommunicationException
     * @throws QueryFilterInvalidParamException
     */
    private function export(array $batchOfProducts)
    {
        foreach ($batchOfProducts as $merchantProductNo => $data) {
            try {
                $this->getProductsProxy()->purgeAndReplaceProducts($merchantProductNo, $data);
            } catch (HttpRequestException $exception) {
                if ($exception->getCode() === 404) {
                    $this->getProductsProxy()->upload($data);
                } else {
                    throw $exception;
                }
            }
        }
    }

    /**
     * @param array $batchOfProducts
     * @return void
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RequestNotSuccessfulException
     */
    private function exportWithoutStock(array $batchOfProducts)
    {
        foreach ($batchOfProducts as $merchantProductNo => $data) {
            try {
                $this->getProductsProxy()->purgeAndReplaceProductsWithoutStock($merchantProductNo, $data);
            } catch (HttpRequestException $exception) {
                if ($exception->getCode() === 404) {
                    $this->getProductsProxy()->uploadWithoutStock($data);
                } else {
                    throw $exception;
                }
            }
        }
    }
}
