<?php

namespace ChannelEngine\DTO;

use ChannelEngine\Infrastructure\Data\DataTransferObject;

/**
 * Class ExtraDataAttributeMappings
 *
 * @package ChannelEngine\DTO
 */
class ExtraDataAttributeMappings extends DataTransferObject {
	/**
	 * @var array
	 */
	private $mappings;

	/**
	 * @param $mappings
	 */
	public function __construct( $mappings  ) {
		$this->mappings = $mappings;
	}

	/**
	 * @return array
	 */
	public function get_mappings() {
		return $this->mappings;
	}

	/**
	 * @inheritdoc
	 */
	public function toArray() {
		return $this->mappings;
	}
}