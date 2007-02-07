<?php
// adapt the standard selenium test runner to our application
$s = '/repos/lib/selenium/core/';

// do <?php echo $s; X> for each src="

// TODO seems like the testcase is not updated correctly with styles with this concept
?>
<html>

<head>
    <HTA:APPLICATION ID="SeleniumHTARunner" APPLICATIONNAME="Selenium">
        <!-- the previous line is only relevant if you rename this
     file to "TestRunner.hta" -->

        <!-- The copyright notice and other comments have been moved to after the HTA declaration,
to work-around a bug in IE on Win2K whereby the HTA application doesn't function correctly -->
        <!--
        Copyright 2004 ThoughtWorks, Inc

         Licensed under the Apache License, Version 2.0 (the "License");
         you may not use this file except in compliance with the License.
         You may obtain a copy of the License at

             http://www.apache.org/licenses/LICENSE-2.0

         Unless required by applicable law or agreed to in writing, software
         distributed under the License is distributed on an "AS IS" BASIS,
         WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
         See the License for the specific language governing permissions and
         limitations under the License.
        -->
        <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type"/>

        <title>Selenium Functional Test Runner</title>
        <link rel="stylesheet" type="text/css" href="<?php echo $s; ?>selenium.css"/>
        <script type="text/javascript" src="<?php echo $s; ?>scripts/narcissus-defs.js"></script>
        <script type="text/javascript" src="<?php echo $s; ?>scripts/narcissus-parse.js"></script>
        <script type="text/javascript" src="<?php echo $s; ?>scripts/narcissus-exec.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>lib/prototype.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/htmlutils.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>lib/scriptaculous/scriptaculous.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>lib/cssQuery/cssQuery-p.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-browserdetect.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-browserbot.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/find_matching_child.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-api.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-commandhandlers.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-executionloop.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-testrunner.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-logging.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/selenium-version.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>xpath/misc.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>xpath/dom.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>xpath/xpath.js"></script>
        <script language="JavaScript" type="text/javascript" src="<?php echo $s; ?>scripts/user-extensions.js"></script>
        <script language="JavaScript" type="text/javascript">
            function openDomViewer() {
                var autFrame = document.getElementById('myiframe');
                var autFrameDocument = new SeleniumFrame(autFrame).getDocument();
                this.rootDocument = autFrameDocument;
                var domViewer = window.open(getDocumentBase(document) + 'domviewer/domviewer.html');
                return false;
            }
        </script>
</head>

<body onLoad="onSeleniumLoad();">
<table class="layout">
<form action="" name="controlPanel">

<!-- Suite, Test, Control Panel -->

<tr class="selenium">
<td width="25%" height="30%">
    <iframe name="testSuiteFrame" id="testSuiteFrame" src="<?php echo $s; ?>TestPrompt.html" application="yes"></iframe>
</td>
<td width="50%" height="30%">
    <iframe name="testFrame" id="testFrame" application="yes"></iframe>
</td>

<td width="25%">
    <table class="layout">
        <tr class="selenium">
            <th width="25%" height="1" class="header">
                <h1><a href="http://selenium.thoughtworks.com" title="The Selenium Project">Selenium</a> TestRunner
                </h1>
            </th>
        </tr>
        <tr>
            <td width="25%" height="30%" id="controlPanel">
                <fieldset>
                    <legend>Execute Tests</legend>

                    <div id="imageButtonPanel">
                        <button type="button" id="runSuite" onClick="htmlTestRunner.startTestSuite();"
                                title="Run All tests">
                        </button>
                        <button type="button" id="runSeleniumTest" onClick="htmlTestRunner.runSingleTest();"
                                title="Run the Selected test">
                        </button>
                        <button type="button" id="pauseTest" disabled="disabled"
                                title="Pause/Continue" class="cssPauseTest">
                        </button>
                        <button type="button" id="stepTest" disabled="disabled"
                                title="Step">
                        </button>
                    </div>

                    <div style="float:left">Fast</div>
                    <div style="float:right">Slow</div>
                    <br/>
                    <div id="speedSlider">
                        <div id="speedTrack">&nbsp;</div>
                        <div id="speedHandle">&nbsp;</div>
                    </div>

                    <div class="executionOptions">
                        <input id="highlightOption" type="checkbox" name="highlightOption" value="0"/>
                        <label for="highlightOption">Highlight elements</label>
                    </div>

                </fieldset>

                <table id="stats" align="center">
                    <tr>
                        <td colspan="2" align="right">Elapsed:</td>
                        <td id="elapsedTime" colspan="2">00.00</td>
                    </tr>
                    <tr>
                        <th colspan="2">Tests</th>
                        <th colspan="2">Commands</th>
                    </tr>
                    <tr>
                        <td class="count" id="testRuns">0</td>
                        <td>run</td>
                        <td class="count" id="commandPasses">0</td>
                        <td>passed</td>
                    </tr>
                    <tr>
                        <td class="count" id="testFailures">0</td>
                        <td>failed</td>
                        <td class="count" id="commandFailures">0</td>
                        <td>failed</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td class="count" id="commandErrors">0</td>
                        <td>incomplete</td>
                    </tr>
                </table>

                <fieldset>
                    <legend>Tools</legend>

                    <button type="button" id="domViewer1" onClick="openDomViewer();">
                        View DOM
                    </button>
                    <button type="button" onClick="LOG.show();">
                        Show Log
                    </button>

                </fieldset>

            </td>
        </tr>
    </table>
</td>
</tr>

<!-- AUT -->

<tr>
    <td colspan="3" height="70%">
        <iframe name="myiframe" id="myiframe" src="<?php echo $s; ?>TestRunner-splash.html"></iframe>
    </td>
</tr>

    </form>
    </table>

</body>
</html>