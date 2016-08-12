<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

use \ILIAS\UI\Component\Component as Component;
/**
 * This describes a Standard Panel.
 */
interface Standard extends Panel {
	/**
	 * @param Component[]|Component
	 * @return \ILIAS\UI\Component\Panel\Standard
	 */
	public function withContent($content);

	/**
	 * @return mixed content \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component
	 */
	public function getContent();
}
