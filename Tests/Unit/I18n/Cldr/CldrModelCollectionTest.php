<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Tests\Unit\I18n\Cldr;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for the CldrModelCollection
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CldrModelCollectionTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function returnsRawArrayFromMergedDataCorrectly() {
		$sampleDataOfParent = array(
			'key1' => 'value1',
			'key2' => array(
				'subkey1' => 'value2',
				'subkey2' => 'value3',
			),
		);

		$sampleDataOfChild = array(
			'key1' => 'value4',
			'key2' => array(
				'subkey1' => 'value5',
				'subkey3' => 'value6',
			),
		);

		$mockCldrModelParent = $this->getMock('F3\FLOW3\I18n\Cldr\CldrModel', array(), array('fake/path'));
		$mockCldrModelParent->expects($this->at(0))->method('getRawArray')->with('foo')->will($this->returnValue($sampleDataOfParent));
		$mockCldrModelParent->expects($this->at(1))->method('getRawArray')->with('bar')->will($this->returnValue($sampleDataOfParent));
		$mockCldrModelParent->expects($this->at(2))->method('getRawArray')->with('baz')->will($this->returnValue(FALSE));

		$mockCldrModelChild = $this->getMock('F3\FLOW3\I18n\Cldr\CldrModel', array(), array('fake/path'));
		$mockCldrModelChild->expects($this->at(0))->method('getRawArray')->with('foo')->will($this->returnValue($sampleDataOfChild));
		$mockCldrModelChild->expects($this->at(1))->method('getRawArray')->with('bar')->will($this->returnValue(FALSE));
		$mockCldrModelChild->expects($this->at(2))->method('getRawArray')->with('baz')->will($this->returnValue(FALSE));

		$model = new \F3\FLOW3\I18n\Cldr\CldrModelCollection(array($mockCldrModelParent, $mockCldrModelChild));

		$result = $model->getRawArray('foo');
		$this->assertEquals('value4', $result['key1']);
		$this->assertEquals('value5', $result['key2']['subkey1']);
		$this->assertEquals('value6', $result['key2']['subkey3']);

		$result = $model->getRawArray('bar');
		$this->assertEquals('value1', $result['key1']);
		$this->assertEquals('value2', $result['key2']['subkey1']);

		$result = $model->getRawArray('baz');
		$this->assertEquals(FALSE, $result);
	}

	/**
	 * @test
	 * @author Karol Gusak <firstname@lastname.eu>
	 */
	public function returnsElementFromMergedDataCorrectly() {
		$sampleDataOfParent = array(
			\F3\FLOW3\I18n\Cldr\CldrParser::NODE_WITHOUT_ATTRIBUTES => 'value1',
			'key2' => 'value2',
		);

		$sampleDataOfChild = array(
			\F3\FLOW3\I18n\Cldr\CldrParser::NODE_WITHOUT_ATTRIBUTES => 'value3',
			'key2' => 'value4',
		);

		$mockCldrModelParent = $this->getMock('F3\FLOW3\I18n\Cldr\CldrModel', array(), array('fake/path'));
		$mockCldrModelParent->expects($this->at(0))->method('getRawArray')->with('foo')->will($this->returnValue($sampleDataOfParent));
		$mockCldrModelParent->expects($this->at(1))->method('getRawArray')->with('bar')->will($this->returnValue($sampleDataOfParent));
		$mockCldrModelParent->expects($this->at(2))->method('getRawArray')->with('baz')->will($this->returnValue(FALSE));

		$mockCldrModelChild = $this->getMock('F3\FLOW3\I18n\Cldr\CldrModel', array(), array('fake/path'));
		$mockCldrModelChild->expects($this->at(0))->method('getRawArray')->with('foo')->will($this->returnValue($sampleDataOfChild));
		$mockCldrModelChild->expects($this->at(1))->method('getRawArray')->with('bar')->will($this->returnValue(FALSE));
		$mockCldrModelChild->expects($this->at(2))->method('getRawArray')->with('baz')->will($this->returnValue(FALSE));

		$model = new \F3\FLOW3\I18n\Cldr\CldrModelCollection(array($mockCldrModelParent, $mockCldrModelChild));

		$result = $model->getElement('foo');
		$this->assertEquals('value3', $result);

		$result = $model->getElement('bar');
		$this->assertEquals('value1', $result);

		$result = $model->getElement('baz');
		$this->assertEquals(FALSE, $result);
	}
}

?>