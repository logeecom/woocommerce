<?php

namespace ChannelEngine\Utility;

interface Standard_Product_Attributes {

	/**
	 * Unique value which will represent all standard fields
	 */
	const PREFIX = 'ce_standard';

	/**
	 * Standard WC product fields
	 */
	const ATTRIBUTES = array(
		'id',
		'name',
		'vat',
		'stock',
		'description',
		'short_description',
		'price_incl_tax',
		'price_excl_tax',
		'sale_price_incl_tax',
		'sale_price_excl_tax',
		'sku',
		'product_url',
		'category',
		'image_url',
		'weight',
		'length',
		'width',
		'height',
	);
}
