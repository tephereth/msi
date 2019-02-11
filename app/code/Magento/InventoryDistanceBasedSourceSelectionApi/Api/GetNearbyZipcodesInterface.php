<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelectionApi\Api;

/**
 * Get nearby zip codes of a given zip code, based on the given radius in KM
 *
 * @api
 */
interface GetNearbyZipcodesInterface
{
    /**
     * Get nearby zip codes of a given zip code, based on the given radius in KM
     *
     * @param string $country
     * @param string $zipcode
     * @param int $radius
     * @return string[]
     */
    public function execute(string $country, string $zipcode, int $radius);
}
