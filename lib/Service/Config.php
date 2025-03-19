<?php

declare(strict_types=1);

/**
 * @copyright 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\UnattendedUpgrades\Service;

use DateTimeImmutable;
use OCP\IConfig;
use function is_array;

class Config {

	private const DEFAULT_MAINETNANCE_WINDOW_START = '00:00:00';
	private const DEFAULT_MAINTENANCE_WINDOW_END = '23:59:59';

	public function __construct(private IConfig $config) {}

	private function getConfigArray(): array {
		$config = $this->config->getSystemValue('unattended_upgrades', null);
		if (!is_array($config)) {
			return [];
		}
		return $config;
	}

	public function getMaintenanceWindow(): ?array {
		$config = $this->getConfigArray();

		$window = $config['maintenance_window'] ?? [];

		return [
			new DateTimeImmutable($window['start'] ?? self::DEFAULT_MAINETNANCE_WINDOW_START),
			new DateTimeImmutable($window['end'] ?? self::DEFAULT_MAINTENANCE_WINDOW_END),
		];
	}

	/**
	 * @return string[]
	 */
	public function getAllowedAppIds(): array {
		$config = $this->getConfigArray();

		return $config['allowed'] ?? [];
	}

	/**
	 * @return string[]
	 */
	public function getBlockedAppIds(): array {
		$config = $this->getConfigArray();

		return $config['blocked'] ?? [];
	}

}
