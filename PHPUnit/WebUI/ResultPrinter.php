<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id: ResultPrinter.php 3592 2008-08-23 03:39:29Z sb $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';

Base::import('PHPUnit_WebUI_HtmlOutput');

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Prints the result of a WebUI TestRunner run.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: 3.3.1
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class PHPUnit_WebUI_ResultPrinter implements PHPUnit_Framework_TestListener
{
    const EVENT_TEST_START      = 0;
    const EVENT_TEST_END        = 1;
    const EVENT_TESTSUITE_START = 2;
    const EVENT_TESTSUITE_END   = 3;

    /**
     * @var    integer
     */
    protected $column = 0;

    /**
     * @var    array
     */
    protected $numberOfTests = array();

    protected $numberOfTestSuite = 0;

    /**
     * @var    array
     */
    protected $testSuiteSize = array();

    /**
     * @var    integer
     */
    protected $lastEvent = -1;

    /**
     * @var    boolean
     */
    protected $lastTestFailed = FALSE;

    protected $actualTestSuiteName = NULL;

    protected $actualTestName = NULL;
    
    /**
     * @var    boolean
     */
    protected $ansi = FALSE;

    /**
     * @var    boolean
     */
    protected $verbose = FALSE;

    /**
     * @var    integer
     */
    protected $numAssertions = 0;

    /* == Printer part begin == */
    /**
     * If TRUE, flush output after every write.
     *
     * @var boolean
     */
    protected $autoFlush = FALSE;

    /**
     * @var    resource
     */
    protected $out;

    /**
     * @var    string
     */
    protected $outTarget;

    /**
     * @var    boolean
     */
    protected $printsHTML = FALSE;
    /* == Printer part end == */

    /**
     * Constructor.
     *
     * @param  mixed   $out
     * @param  boolean $verbose
     * @param  boolean $ansi
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.0.0
     */
    public function __construct($out = NULL, $verbose = FALSE, $ansi = FALSE)
    {
        /* == Printer part begin == */
        if ($out !== NULL) {
            if (is_string($out)) {
                if (strpos($out, 'socket://') === 0) {
                    $out = explode(':', str_replace('socket://', '', $out));

                    if (sizeof($out) != 2) {
                        throw new InvalidArgumentException;
                    }

                    $this->out = fsockopen($out[0], $out[1]);
                } else {
                    $this->out = fopen($out, 'wt');
                }

                $this->outTarget = $out;
            } else {
                $this->out = $out;
            }
        }
        /* == Printer part end == */

        if (is_bool($ansi)) {
            $this->ansi = $ansi;
        } else {
            throw new InvalidArgumentException;
        }

        if (is_bool($verbose)) {
            $this->verbose = $verbose;
        } else {
            throw new InvalidArgumentException;
        }

        $title = $_SERVER['SERVER_NAME']
               . ' | ' . PHPUnit_Runner_Version::getVersionString();
        print PHPUnit_WebUI_HtmlOutput::start($title);
    }

    /**
     * @param  PHPUnit_Framework_TestResult $result
     */
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $this->printHeader($result->time());

        if ($result->errorCount() > 0) {
            $this->printErrors($result);
        }

        if ($result->failureCount() > 0) {
            if ($result->errorCount() > 0) {
                print "<hr />\n";
            }

            $this->printFailures($result);
        }

        if ($this->verbose) {
            if ($result->notImplementedCount() > 0) {
                if ($result->failureCount() > 0) {
                    print "<hr />\n";
                }

                $this->printIncompletes($result);
            }

            if ($result->skippedCount() > 0) {
                if ($result->notImplementedCount() > 0) {
                    print "<hr />\n";
                }

                $this->printSkipped($result);
            }
        }

        $this->printFooter($result);
    }

    /**
     * @param  array   $defects
     * @param  integer $count
     * @param  string  $type
     */
    protected function printDefects(array $defects, $count, $type)
    {
        if ($count == 0) {
            return;
        }

        $this->write(
          sprintf(
            "<h3 class=\"defect\">There %s %d %s%s:</h3>\n",

            ($count == 1) ? 'was' : 'were',
            $count,
            $type,
            ($count == 1) ? '' : 's'
          )
        );

        $i = 1;

        $this->write("<ul>\n");
        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }
        $this->write("</ul>\n");
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @param  integer                       $count
     */
    protected function printDefect(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $failedTest = $defect->failedTest();
        $elementId = get_class($failedTest) . "_" . $failedTest->getName();
        $this->write("<li id=\"$elementId\">\n");
        $this->printDefectHeader($defect, $count);
        $this->printDefectTrace($defect);
        $this->write("</li><!-- #$elementId -->\n");
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @param  integer                       $count
     */
    protected function printDefectHeader(PHPUnit_Framework_TestFailure $defect, $count)
    {
        $failedTest = $defect->failedTest();

        if ($failedTest instanceof PHPUnit_Framework_SelfDescribing) {
            $testName = $failedTest->toString();
        } else {
            $testName = get_class($failedTest);
        }

        $this->write(
          sprintf(
            "<h4 class=\"defect\">%d) %s</h4>\n",

            $count,
            $testName
          )
        );
    }

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     */
    protected function printDefectTrace(PHPUnit_Framework_TestFailure $defect)
    {
        //FIXME: v "novem" PHPUnit zmizela metoda toStringVerbose -> nejsem si jist jestli toto je spravne reseni, ale nejspis je
        $string = method_exists($defect, 'toStringVerbose') ? $defect->toStringVerbose($this->verbose) : $defect->getExceptionAsString();
        $this->write(
          "<p class=\"failMsg\">" . htmlentities($string) . "</p>" .
          "<pre>" .
          PHPUnit_Util_Filter::getFilteredStacktrace(
            $defect->thrownException(),
            FALSE
          ) .
          "</pre>\n"
        );
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printErrors(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->errors(), $result->errorCount(), 'error');
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printFailures(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->failures(), $result->failureCount(), 'failure');
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printIncompletes(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->notImplemented(), $result->notImplementedCount(), 'incomplete test');
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     * @since  Method available since Release 3.0.0
     */
    protected function printSkipped(PHPUnit_Framework_TestResult $result)
    {
        $this->printDefects($result->skipped(), $result->skippedCount(), 'skipped test');
    }

    /**
     * @param  float   $timeElapsed
     */
    protected function printHeader($timeElapsed)
    {
        //$this->write("\n\nTime: " . PHPUnit_Util_Timer::secondsToTimeString($timeElapsed) . "\n\n");
        $this->write("<p class=\"time\">Time: " . self::secondsToTimeString($timeElapsed) . "</p>\n");
    }

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printFooter(PHPUnit_Framework_TestResult $result)
    {
        $this->write("<div id=\"totalResult\">");

        if ($result->wasSuccessful() &&
            $result->allCompletlyImplemented() &&
            $result->noneSkipped()) {
            if ($this->ansi) {
                $this->write("\x1b[30;42m\x1b[2K");
            }

            $this->write(
              sprintf(
                "<p><strong>OK</strong> (%d test%s, %d assertion%s)</p>\n",

                count($result),
                (count($result) == 1) ? '' : 's',
                $this->numAssertions,
                ($this->numAssertions == 1) ? '' : 's'
              )
            );

            if ($this->ansi) {
                $this->write("\x1b[0m\x1b[2K");
            }
        }

        else if ((!$result->allCompletlyImplemented() ||
                  !$result->noneSkipped())&&
                 $result->wasSuccessful()) {
            $this->write(
              sprintf(
                "<p><strong>OK, but incomplete or skipped tests!</strong></p>\n" .
                "<p>Tests: %d, Assertions: %d%s%s.</p>\n",

                count($result),
                $this->numAssertions,
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );
        }

        else {
            $this->write("\n");

            if ($this->ansi) {
                $this->write("\x1b[37;41m\x1b[2KFAILURES!\n\x1b[0m\x1b[37;41m\x1b[2K");
            } else {
                $this->write("<p><strong>FAILURES!</strong></p>\n");
            }

            $this->write(
              sprintf(
                "<p>Tests: %d, Assertions: %s%s%s%s.</p>\n",

                count($result),
                $this->numAssertions,
                $this->getCountString($result->failureCount(), 'Failures'),
                $this->getCountString($result->errorCount(), 'Errors'),
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );

            if ($this->ansi) {
                $this->write("\x1b[0m\x1b[2K");
            }
        }

        $this->write("</div><!-- #totalResult -->");
    }

    /**
     * @param  integer $count
     * @param  string  $name
     * @return string
     * @since  Method available since Release 3.0.0
     */
    protected function getCountString($count, $name)
    {
        $string = '';

        if ($count > 0) {
            $string = sprintf(
              ', %s: %d',

              $name,
              $count
            );
        }

        return $string;
    }

    /**
     * Formats elapsed time (in seconds) to a string.
     *
     * @param  float $time
     * @return float
     */
    public static function secondsToTimeString($time)
    {
        $buffer = '';

        $hours   = sprintf('%02d', ($time >= 3600) ? floor($time / 3600) : 0);
        $minutes = sprintf('%02d', ($time >= 60)   ? floor($time /   60) - 60 * $hours : 0);
        $seconds = sprintf('%2.14f', $time - 60 * 60 * $hours - 60 * $minutes);

        if ($hours == 0 && $minutes == 0) {
            //$seconds = sprintf('%2.4f', $seconds);

            $buffer .= $seconds . ' second';

            if (substr((string) $seconds, 0, 2) != '01') {
                $buffer .= 's';
            }
        } else {
            if ($hours > 0) {
                $buffer = $hours . ':';
            }

            $buffer .= $minutes . ':' . $seconds;
        }

        return $buffer;
    }

    /**
     */
    public function printWaitPrompt()
    {
        $this->write("\n<RETURN> to continue\n");
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $hrefElementId = $this->actualTestSuiteName . '_' .$test->getName();
        $this->writeProgress("<a href=\"#$hrefElementId\""
                           . " onmouseover=\"showDefectBlock('$hrefElementId')\""
                           . " onmouseout=\"hideDefectBlock('$hrefElementId')\">E</a>");
        $this->lastTestFailed = TRUE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $hrefElementId = $this->actualTestSuiteName . '_' .$test->getName();
        $this->writeProgress("<a href=\"#$hrefElementId\""
                           . " onmouseover=\"showDefectBlock('$hrefElementId')\""
                           . " onmouseout=\"hideDefectBlock('$hrefElementId')\">F</a>");
        $this->lastTestFailed = TRUE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $hrefElementId = $this->actualTestSuiteName . '_' .$test->getName();
        $this->writeProgress("<a href=\"#$hrefElementId\""
                           . " onmouseover=\"showDefectBlock('$hrefElementId')\""
                           . " onmouseout=\"hideDefectBlock('$hrefElementId')\">I</a>");
        $this->lastTestFailed = TRUE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $hrefElementId = $this->actualTestSuiteName . '_' .$test->getName();
        $this->writeProgress("<a href=\"#$hrefElementId\""
                           . " onmouseover=\"showDefectBlock('$hrefElementId')\""
                           . " onmouseout=\"hideDefectBlock('$hrefElementId')\">S</a>");
        $this->lastTestFailed = TRUE;
    }

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->actualTestSuiteName = $suite->getName();

        if ($this->verbose) {
            $name = $suite->getName();

            if (empty($name)) {
                $name = 'Test Suite';
            }

            $this->write(
              sprintf(
                "%s%s%s\n",

                $this->lastEvent == self::EVENT_TESTSUITE_END ? "\n" : '',
                str_repeat(' ', count($this->testSuiteSize)),
                $name
              )
            );
        }

        if ($this->verbose || empty($this->numberOfTests)) {
            array_push($this->numberOfTests, 0);
            array_push($this->testSuiteSize, count($suite));
        }

        $this->numberOfTestSuite++;
        if ($this->numberOfTestSuite != 1) {
            $this->write("<h2>" . $suite->getName() . "</h2>\n");
            $this->write("<pre class=\"resultsLine\">");
        }
        $this->lastEvent = self::EVENT_TESTSUITE_START;
    }

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->actualTestSuiteName = NULL;

        if ($this->verbose) {
            array_pop($this->numberOfTests);
            array_pop($this->testSuiteSize);

            $this->column = 0;

            if ($this->lastEvent != self::EVENT_TESTSUITE_END) {
                $this->write("\n");
            }
        }

        if ($this->lastEvent != self::EVENT_TESTSUITE_END) {
            $this->write("</pre>\n");
        }
        $this->lastEvent = self::EVENT_TESTSUITE_END;
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->actualTestName = $test->getName();

        if ($this->verbose) {
            $this->numberOfTests[count($this->numberOfTests)-1]++;
        }

        else if (isset($this->numberOfTests[0])) {
            $this->numberOfTests[0]++;
        }

        else {
            $this->numberOfTests = array(1);
        }

        $this->lastEvent = self::EVENT_TEST_START;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $this->actualTestName = NULL;

        if (!$this->lastTestFailed) {
            $this->writeProgress('.');
        }

        if ($test instanceof PHPUnit_Framework_TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        }

        $this->lastEvent      = self::EVENT_TEST_END;
        $this->lastTestFailed = FALSE;
    }

    /**
     * Zakonceni vypisu
     */
    public function __destruct ()
    {
      print PHPUnit_WebUI_HtmlOutput::finish();
    }

    /**
     * @param  string $progress
     */
    protected function writeProgress($progress)
    {
        $indent = max(0, count($this->testSuiteSize) - 1);

        if ($this->column == 0) {
            $this->write(str_repeat(' ', $indent));
        }

        $this->write($progress);

        if ($this->column++ == 60 - 1 - $indent) {
            if ($this->verbose) {
                $numberOfTests = $this->numberOfTests[count($this->numberOfTests)-1];
                $testSuiteSize = $this->testSuiteSize[count($this->testSuiteSize)-1];
            } else {
                $numberOfTests = $this->numberOfTests[0];
                $testSuiteSize = $this->testSuiteSize[0];
            }

            $width = strlen((string)$testSuiteSize);

            $this->write(
              sprintf(
                ' %' . $width . 'd / %' . $width . "d\n",

                $numberOfTests,
                $testSuiteSize
              )
            );

            $this->column = 0;
        }
    }

    /* == Printer part begin == */
    /**
     * Flush buffer, optionally tidy up HTML, and close output.
     *
     */
    public function flush()
    {
        if ($this->out !== NULL) {
            fclose($this->out);
        }

        if ($this->printsHTML === TRUE && $this->outTarget !== NULL && extension_loaded('tidy')) {
            file_put_contents(
              $this->outTarget, tidy_repair_file($this->outTarget)
            );
        }
    }

    /**
     * Performs a safe, incremental flush.
     *
     * Do not confuse this function with the flush() function of this class,
     * since the flush() function may close the file being written to, rendering
     * the current object no longer usable.
     *
     * @since  Method available since Release 3.3.0
     */
    public function incrementalFlush()
    {
        if ($this->out !== NULL) {
            fflush($this->out);
        } else {
            flush();
        }
    }

    /**
     * @param  string $buffer
     */
    public function write($buffer)
    {
        if ($this->out !== NULL) {
            fwrite($this->out, $buffer);

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        } else {
            if (php_sapi_name() != 'cli') {
                $buffer = ($buffer);
            }

            print $buffer;

            if ($this->autoFlush) {
                $this->incrementalFlush();
            }
        }
    }

    /**
     * Check auto-flush mode.
     *
     * @return boolean
     * @since  Method available since Release 3.3.0
     */
    public function getAutoFlush()
    {
        return $this->autoFlush;
    }

    /**
     * Set auto-flushing mode.
     *
     * If set, *incremental* flushes will be done after each write. This should
     * not be confused with the different effects of this class' flush() method.
     *
     * @param boolean $autoFlush
     * @since  Method available since Release 3.3.0
     */
    public function setAutoFlush($autoFlush)
    {
        if (is_bool($autoFlush)) {
            $this->autoFlush = $autoFlush;
        } else {
            throw new InvalidArgumentException;
        }
    }
    /* == Printer part end == */
}
?>
