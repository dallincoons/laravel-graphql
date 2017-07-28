<?php
namespace StudioNet\GraphQL\Support;

interface TypeInterface {
	/**
	 * Return availabled attributes
	 *
	 * @return array
	 */
	public function getAttributes();

	/**
	 * Return availabled types
	 *
	 * @return array
	 */
	public function getFields();

	/**
	 * Return availabled interfaces
	 *
	 * @return array
	 */
	public function getInterfaces();

	/**
	 * Convert type to array
	 *
	 * @return array
	 */
	public function toArray();

	/**
	 * Convert instance to ObjectType
	 *
	 * @return ObjectType
	 */
	public function toType();
}