<?php
declare(strict_types=1);

namespace Magento\InventoryDistanceBasedSourceSelection\Model\DistanceProvider\Offline;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryDistanceBasedSourceSelectionApi\Api;

class GetNearbyZipcodes implements Api\GetNearbyZipcodesInterface
{
    private const EARTH_RADIUS_KM = 6371000;

    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(string $country, string $zipcode, int $radius)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_geoname');

        $qry = $connection->select()->from($tableName)
            ->where('country_code = ?', $country)
            ->where('postcode = ?', $zipcode)
            ->limit(1);
        $row = $connection->fetchRow($qry);
        if(!$row){
            throw new NoSuchEntityException(
                __('Unknown geoname for %1 in %2', $zipcode, $country)
            );
        }

        // Still here so the target zipcode is valid
        $lat = (float)$row['latitude'];
        $lng = (float)$row['longitude'];

        // Build up a radial query
        $qry = $connection->select()
            ->from($tableName)
            ->columns(['postcode', $this->_createDistanceColumn($lat, $lng) . ' AS distance'])
            ->having('distance <= ?', $radius);

        $rows = $connection->fetchAll($qry);

        $results = [];
        array_walk($rows, function($row) use(&$results){
           $results[] = $row['postcode'];
        });

        return $results;


    }

    private function _createDistanceColumn(float $fLatitude, float $fLongitude){
        return '(' . self::EARTH_RADIUS_KM . ' * ACOS('
            . 'COS(RADIANS(' . (float)$fLatitude . ')) * '
            . 'COS(RADIANS(latitude)) * '
            . 'COS(RADIANS(longitude) - RADIANS(' . (float)$fLongitude . ')) + '
            . 'SIN(RADIANS(' . (float)$fLatitude . ')) * '
            . 'SIN(RADIANS(latitude))'
            . '))';
    }
}