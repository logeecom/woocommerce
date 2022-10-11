<?php

namespace ChannelEngine\DTO;

use ChannelEngine\Infrastructure\Data\DataTransferObject;

/**
 * Class AttributeMappings
 *
 * @package ChannelEngine\DTO
 */
class AttributeMappings extends DataTransferObject
{
    /**
     * @var string
     */
    private $brand;
    /**
     * @var string
     */
    private $color;
    /**
     * @var string
     */
    private $size;
	/**
	 * @var string
	 */
	private $gtin;
	/**
	 * @var string
	 */
	private $cataloguePrice;
	/**
	 * @var string
	 */
	private $price;
	/**
	 * @var string
	 */
	private $purchasePrice;
	/**
	 * @var string
	 */
	private $details;
	/**
	 * @var string
	 */
	private $category;

	/**
	 * @var string
	 */
	private $vendorProductNumber;

    /**
     * @var string
     */
    private $shippingTime;

    /**
     * @param $brand
     * @param $color
     * @param $size
     * @param $gtin
     * @param $cataloguePrice
     * @param $price
     * @param $purchasePrice
     * @param $details
     * @param $category
     * @param $vendorProductNumber
     * @param $shippingTime
     */
    public function __construct(
        $brand,
        $color,
        $size,
        $gtin,
	    $cataloguePrice,
	    $price,
	    $purchasePrice,
	    $details,
	    $category,
	    $vendorProductNumber,
        $shippingTime
    ) {
	    $this->brand  = $brand;
	    $this->color = $color;
	    $this->size   = $size;
		$this->gtin = $gtin;
		$this->cataloguePrice = $cataloguePrice;
		$this->price = $price;
	    $this->purchasePrice = $purchasePrice;
        $this->details = $details;
		$this->category = $category;
		$this->vendorProductNumber = $vendorProductNumber;
        $this->shippingTime = $shippingTime;
	}

	/**
	 * @return string
	 */
	public function get_brand() {
		return $this->brand;
	}

	/**
	 * @return string
	 */
	public function get_color() {
		return $this->color;
	}

	/**
	 * @return string
	 */
	public function get_size() {
		return $this->size;
	}

	/**
	 * @return string
	 */
	public function get_gtin() {
		return $this->gtin;
	}

	/**
	 * @return string
	 */
	public function get_catalogue_price() {
		return $this->cataloguePrice;
	}

	/**
	 * @return string
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * @return string
	 */
	public function get_purchase_price() {
		return $this->purchasePrice;
	}

	/**
	 * @return string
	 */
	public function get_details() {
		return $this->details;
	}

	/**
	 * @return string
	 */
	public function get_category() {
		return $this->category;
	}

	/**
	 * @return string
	 */
	public function get_vendor_product_number() {
		return $this->vendorProductNumber;
	}

    /**
     * @return string
     */
    public function get_shipping_time(): string
    {
        return $this->shippingTime;
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'brand' => $this->brand,
            'color' => $this->color,
            'size' => $this->size,
			'gtin' => $this->gtin,
	        'cataloguePrice' => $this->cataloguePrice,
	        'price' => $this->price,
            'purchasePrice' => $this->purchasePrice,
	        'details' => $this->details,
	        'category' => $this->category,
	        'vendorProductNumber' => $this->vendorProductNumber,
            'shippingTime' => $this->shippingTime
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
		return new self(
		    static::getDataValue($data, 'brand', null),
		    static::getDataValue($data, 'color', null),
		    static::getDataValue($data, 'size', null),
		    static::getDataValue($data, 'gtin', null),
		    static::getDataValue($data, 'cataloguePrice', null),
		    static::getDataValue($data, 'price', null),
		    static::getDataValue($data, 'purchasePrice', null),
		    static::getDataValue($data, 'details', null),
		    static::getDataValue($data, 'category', null),
			static::getDataValue($data, 'vendorProductNumber', null),
			static::getDataValue($data, 'shippingTime', null)
	    );
    }
}
