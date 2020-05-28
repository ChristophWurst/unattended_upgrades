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

namespace OCA\UnattendedUpgrades\Command;

use OCA\UnattendedUpgrades\Service\Upgrader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DryRun extends Command {

	/** @var Upgrader */
	private $upgrader;

	public function __construct(Upgrader $upgrader) {
		parent::__construct('unattended-upgrades:dry-run');

		$this->upgrader = $upgrader;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$upgrades = $this->upgrader->upgrade(true);

		$output->writeln("$upgrades app(s) can be upgraded");
	}

}
