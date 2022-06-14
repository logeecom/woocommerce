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
    private $colour;
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
	 * @param string $brand
	 * @param string $colour
	 * @param string $size
	 * @param string $gtin
	 * @param string $cataloguePrice
	 * @param string $price
	 * @param string $purchasePrice
	 * @param string $details
	 * @param string $category
	 */
    public function __construct(
        $brand,
        $colour,
        $size,
        $gtin,
	    $cataloguePrice,
	    $price,
	    $purchasePrice,
	    $details,
	    $category
    ) {
	    $this->brand  = $brand;
	    $this->colour = $colour;
	    $this->size   = $size;
		$this->gtin = $gtin;
		$this->cataloguePrice = $cataloguePrice;
		$this->price = $price;
	    $this->purchasePrice = $purchasePrice;
        $this->details = $details;
		$this->category = $category;
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
	public function get_colour() {
		return $this->colour;
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
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'brand' => $this->brand,
            'colour' => $this->colour,
            'size' => $this->size,
			'gtin' => $this->gtin,
	        'cataloguePrice' => $this->cataloguePrice,
	        'price' => $this->price,
            'purchasePrice' => $this->purchasePrice,
	        'details' => $this->details,
	        'category' => $this->category
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data)
    {
		return new self(
		    static::getDataValue($data, 'brand', null),
		    static::getDataValue($data, 'colour', null),
		    static::getDataValue($data, 'size', null),
		    static::getDataValue($data, 'gtin', null),
		    static::getDataValue($data, 'cataloguePrice', null),
		    static::getDataValue($data, 'price', null),
		    static::getDataValue($data, 'purchasePrice', null),
		    static::getDataValue($data, 'details', null),
		    static::getDataValue($data, 'category', null)
	    );
    }
}
