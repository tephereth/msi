<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Model;

use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetNearbyZipcodesInterface;
use Magento\InventorySourceSelectionApi\Api\Data\AddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\Data\LatLngInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetDistanceProviderCodeInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api\GetLatLngFromAddressInterface;
use Magento\InventoryDistanceBasedSourceSelectionApi\Exception\NoSuchLatLngFromAddressProviderException;

/**
 * Get latitude and longitude object from address
 *
 * @api
 */
class GetNearbyZipcodes implements GetNearbyZipcodesInterface
{
    /**
     * @var GetNearbyZipcodesInterface[]
     */
    private $providers;

    /**
     * @var GetDistanceProviderCodeInterface
     */
    private $getDistanceProviderCode;

    /**
     * GetLatLngFromSource constructor.
     *
     * @param GetDistanceProviderCodeInterface $getDistanceProviderCode
     * @param GetNearbyZipcodesInterface[] $providers
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetDistanceProviderCodeInterface $getDistanceProviderCode,
        array $providers
    ) {
        foreach ($providers as $providerCode => $provider) {
            if (!($provider instanceof GetNearbyZipcodesInterface)) {
                throw new \InvalidArgumentException(
                    'LatLng provider ' . $providerCode . ' must implement ' . GetNearbyZipcodesInterface::class
                );
            }
        }

        $this->providers = $providers;
        $this->getDistanceProviderCode = $getDistanceProviderCode;
    }

    public function execute(string $country, string $zipcode, int $radius)
    {
        $code = $this->getDistanceProviderCode->execute();
        if (!isset($this->providers[$code])) {
            throw new NoSuchLatLngFromAddressProviderException(
                __('No such latitude and longitude from address provider: %1', $code)
            );
        }

        return $this->providers[$code]->execute($country, $zipcode, $radius);
    }

}
