<?php

namespace white\commerce\sendcloud\client;

use craft\base\Element;
use craft\commerce\base\PurchasableInterface;
use craft\commerce\elements\Order;
use craft\commerce\elements\Variant;
use craft\helpers\ArrayHelper;
use JouwWeb\SendCloud\Exception\SendCloudClientException;
use JouwWeb\SendCloud\Exception\SendCloudRequestException;
use JouwWeb\SendCloud\Exception\SendCloudStateException;
use JouwWeb\SendCloud\Model\Address;
use JouwWeb\SendCloud\Model\ParcelItem;
use JouwWeb\SendCloud\Model\ShippingMethod;
use white\commerce\sendcloud\client\SendcloudClient as Client;
use white\commerce\sendcloud\models\Parcel;
use white\commerce\sendcloud\SendcloudPlugin;

final class JouwWebSendcloudAdapter implements SendcloudInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $sendcloudShippingMethods;

    /**
     * JouwWebSendCloudAdapter constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param int $parcelId
     * @return Parcel
     * @throws SendCloudClientException
     */
    public function getParcel(int $parcelId): Parcel
    {
        $parcel = $this->client->getParcel($parcelId);
        
        return (new JouwWebParcelNormalizer())->getParcel($parcel);
    }

    /**
     * @param Order $order
     * @param int|null $servicePointId
     * @param int|null $weight
     * @return Parcel
     * @throws SendCloudRequestException
     */
    public function createParcel(Order $order, ?int $servicePointId = null, ?int $weight = null): Parcel
    {
        $address = $this->createAddress($order);

        if ($weight === null) {
            //$weight = $order->getTotalWeight();
            $weight = 0;
            foreach ($order->getLineItems() as $item) {
                $weight += $item->weight > 0 ? $item->weight : 1;
            }
        }
        
        $items = [];
        foreach ($order->getLineItems() as $item) {
            $purchasable = $item->getPurchasable();
            
            $parcelItem = new ParcelItem(
                $item->getDescription() ?? $purchasable->getDescription(),
                $item->qty,
                $item->weight > 0 ? $item->weight : 1,
                $item->getPrice(),
                null,
                null,
                $item->getSku() ?? $purchasable->getSku()
            );

            $settings = SendcloudPlugin::getInstance()->getSettings();
            if ($settings->hsCodeFieldHandle) {
                $parcelItem->setHarmonizedSystemCode($this->tryGetProductField($purchasable, $settings->hsCodeFieldHandle));
            }
            if ($settings->originCountryFieldHandle) {
                $parcelItem->setOriginCountryCode($this->tryGetProductField($purchasable, $settings->originCountryFieldHandle));
            }

            $items[] = $parcelItem;
        }
        
        $parcel = $this->client->createParcel(
            $address,
            $servicePointId,
            $order->getId(),
            $weight,
            $order->reference,
            \JouwWeb\SendCloud\Model\Parcel::CUSTOMS_SHIPMENT_TYPE_COMMERCIAL_GOODS,
            $items
        );

        return (new JouwWebParcelNormalizer())->getParcel($parcel);
    }

    /**
     * @param $parcelId
     * @param Order $order
     * @return Parcel
     * @throws SendCloudRequestException
     */
    public function updateParcel(int $parcelId, Order $order): Parcel
    {
        $address = $this->createAddress($order);
        $parcel = $this->client->updateParcel($parcelId, $address);

        return (new JouwWebParcelNormalizer())->getParcel($parcel);
    }

    /**
     * @param Order $order
     * @param int $parcelId
     * @return Parcel
     * @throws SendCloudClientException
     * @throws SendCloudRequestException
     */
    public function createLabel(Order $order, int $parcelId): Parcel
    {
        $shippingMethods = $this->getShippingMethods();
        if (!array_key_exists($order->shippingMethod->getName(), $shippingMethods)) {
            throw new \Exception("Could not find Sendcloud shipping method '{$order->shippingMethod->getName()}'.");
        }
        
        $shippingMethodId = $shippingMethods[$order->shippingMethod->getName()]->getId();
        
        $parcel = $this->client->getParcel($parcelId);
        $parcel = $this->client->createLabel($parcel, $shippingMethodId, null);
        
        return (new JouwWebParcelNormalizer())->getParcel($parcel);
    }

    public function getShippingMethods(): array
    {
        if (!$this->sendcloudShippingMethods) {
            $sendcloudShippingMethods = $this->client->getShippingMethods();
            $this->sendcloudShippingMethods = ArrayHelper::map(
                $sendcloudShippingMethods,
                static function (ShippingMethod $method) {
                    return $method->getName();
                },
                static function (ShippingMethod $method) {
                    return $method;
                }
            );
        }

        return $this->sendcloudShippingMethods;
    }

    /**
     * @param int $parcelId
     * @param int $format
     * @return string
     * @throws SendCloudClientException
     * @throws SendCloudRequestException
     * @throws SendCloudStateException
     */
    public function getLabelPdf(int $parcelId, int $format): string
    {
        return $this->client->getLabelPdf($parcelId, $format);
    }

    public function getLabelsPdf(array $parcelIds, int $format): string
    {
        return $this->client->getBulkLabelPdf($parcelIds, $format);
    }

    public function getReturnPortalUrl(int $parcelId): ?string
    {
        return $this->client->getReturnPortalUrl($parcelId);
    }

    /**
     * @param Order $order
     * @return Address
     */
    private function createAddress(Order $order): Address
    {
        $shippingAddress = $order->shippingAddress;
        return new Address(
            $shippingAddress->fullName ?: $shippingAddress->firstName . ' ' . $shippingAddress->lastName,
            $shippingAddress->businessName,
            $shippingAddress->address1,
            $shippingAddress->address2,
            $shippingAddress->city,
            $shippingAddress->zipCode,
            $shippingAddress->country->iso,
            $order->email,
            $shippingAddress->phone
        );
    }

    private function tryGetProductField(PurchasableInterface $purchasable, $fieldHandle)
    {
        if ($purchasable instanceof Element) {
            if ($purchasable->getFieldLayout()->isFieldIncluded($fieldHandle)) {
                return $purchasable->getFieldValue($fieldHandle);
            } elseif ($purchasable instanceof Variant) {
                $product = $purchasable->getProduct();
                if ($product->getFieldLayout()->isFieldIncluded($fieldHandle)) {
                    return $product->getFieldValue($fieldHandle);
                }
            }
        }

        return null;
    }
}