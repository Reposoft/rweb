<?php
/**
This code distributed under the BSD License below, for more information about the BSD License see http://www.opensource.org/licenses/bsd-license.php.

Copyright (c) 2003, Jason Sheets <jsheets@shadonet.com>, Idaho Image Works LLC
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

    * Neither the name of the Idaho Image Works LLC nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.


THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

class coder {
   var $version = '1.5'; // coder version class

/* These directives can be changed but it is recommended you keep the defaults and change them from the calling PHP Script */

   /* Run Time Settings */
   var $debug = false; // display verbose message when file fails to encode, off by default
   var $max_execution_time = 600; // max execution time in seconds

   /* Directory Creation Settings */
   var $directory_mode = 0777; // octal mode created directorie will have
   var $file_mode = 0777; // octal mode for files
   var $recursive = true; // recursively process sub-directories

   /* These variables control which files are valid for encoding */
   var $copy_skipped_files = true; // copy files that were not eligible for encoding to the destination directory
   var $extensions = array('php', 'inc'); // extensions of files to process
   var $ignore_extensions = array(''); // extensions to never encode
   var $ignore_files = array('.', '..');

   /* Setup Restrictions */
   var $restrictions = array(); // array to hold list of restrictions selected

   var $restrictions_code = ''; // php code to implement restrictions

   /* Source and destination directory settings */
   var $src_dir = './encoded'; // source code origination directory
   var $dest_dir = './files'; // encoded file destination

   /* This code is prepended and appended to the script before it is encoded */
   var $php_pre_content = ''; // php content to prepend to source files before encoding, example would be date/time checking
   var $php_post_content = ''; // php content to append to source files before encoding, copyright, etc

/* No Changes Below Here */

   /* this code is included in the top of every encoded file in plain text, this is used to detect eaccelerator and give instructions for installing if it is empty. */
   var $loader_code = '/*This encoded file was generated using PHPCoder (http://phpcoder.sourceforge.net/) and eAccelerator (http://eaccelerator.sourceforge.net/)*/ if (!is_callable("eaccelerator_load") && !@dl("eAccelerator.so")) { die("This PHP script has been encoded using the excellent eAccelerator Optimizer, to run it you must install <a href=\"http://eaccelerator.sourceforge.net/\">eAccelerator or the eLoader</a>"); }';

   /*
      @Name: coder
      @Proto: void coder(void)
      @Desc: Constructor for coder class
   */

   function coder()
   {
      // make sure mmcache is available
      if (!is_callable('eaccelerator_encode') && !@dl('eaccelerator.so')) {
         die('You must have eAccelerator installed to use this encoder, it is freely available at: <a target="_blank" href="http://eaccelerator.sourceforge.net/">http://eaccelerator.sourceforge.net/</a>');
      }

      // set the max execution time if current max execution time is less than $this->max_execution_time
      if (!empty($this->max_execution_time)) {
         if (ini_get('max_execution_time') < $this->max_execution_time) {
            if (!ini_set('max_execution_time', $this->max_execution_time)) {
               print '<p><font color="red">Failed to set maximum execution time.</font></p>';
            }
         }
      }
   }


   /*
      @Name: DisplayForm
      @Proto: void DisplayForm(void)
      @Desc: Displays Main Encoder Form
   */

   function DisplayForm()
   {
      ?>
      <form method="post" action="<?php print $_SERVER['PHP_SELF']; ?>">
      <center>
      <table cellspacing="2" cellpadding="0">

      <tr>
         <td><b>Source Dir:</b></td>
         <td><input type="text" name="source_dir" size="60" value="<?php print $this->src_dir; ?>"
         title="The path to the directory that the source code you want to encode resides in.">
         </td>
      </tr>

      <tr>
         <td><b>Destination Dir:</b></td>
         <td><input type="text" name="destination_dir" size="60" value="<?php print $this->dest_dir; ?>"
         title="The path to the directory that the encoded files will be written to.">
      </tr>

      <tr><td colspan="2">&nbsp;</td></tr>
      <tr>
         <td><b>Encode Files:</b></td>
         <!-- GJH 27/2/06 Fixed spelling errors. -->
         <td><input type="text" name="extensions" size="12" value="<?php print implode(',', $this->extensions); ?>"
         title="A comma-separated list of filename extensions, without the '.', to be encoded.">
      </tr>

      <tr>
         <td><b>Skip Files:</b></td>
         <!-- GJH 27/2/06 Fixed spelling errors. -->
         <td><input type="text" name="ignore_extensions" size="12" value="<?php print implode(',', $this->ignore_extensions); ?>"
         title="A comma-separated list of filename extensions, without the '.', that won't be encoded.">
      </tr>

      <tr><td colspan="2">&nbsp;</td></tr>

      <tr>
         <td><b>Recursive Encoding:</b></td>
         <td>
         <input type="radio" name="recursive" value="0" <? if ($this->recursive == false) { print 'checked'; } ?>
         title="Don't recurse into sub-directories when encoding."> No &nbsp;&nbsp;
         <input type="radio" name="recursive" value="1" <? if ($this->recursive == true) { print 'checked'; } ?>
         title="Recurse into sub-directories when encoding."> Yes </td>
      </tr>

      <tr>
         <td><b>Copy Skipped Files:</b></td>
         <td>
         <input type="radio" name="copy_skipped_files" value="0" <? if ($this->copy_skipped_files == false) { print 'checked'; } ?>
         title="Don't copy files which aren't encoded into the destination directory."> No &nbsp;&nbsp;
         <input type="radio" name="copy_skipped_files" value="1" <? if ($this->copy_skipped_files == true) { print 'checked'; } ?>
         title="Copy files which aren't encoded into the destination directory."> Yes </td>
      </tr>
      <tr><td colspan="2">&nbsp;</td></tr>

      <tr>
         <td colspan="2"><b>Pre-Content Code:</b> <span class="smallfont">(Text, HTML or PHP Code)</span></td>
      </tr>

      <tr>
         <td></td>
         <td><textarea name="php_pre_content" cols="30" rows="5"title="Content entered here will be prepended to each PHP source file before it is encoded."><?php print $this->php_pre_content; ?></textarea></td>
      </tr>

      <tr><td colspan="2">&nbsp;</td></tr>

      <tr>
         <td colspan="2"><b>Post-Content Code:</b> <span class="smallfont">(Text, HTML or PHP Code)</span></td>
      </tr>

      <tr>
         <td>&nbsp;</td>
         <td><textarea name="php_post_content" cols="30" rows="5"title="Content entered here will be appended to each PHP source file before it is encoded."><?php print $this->php_post_content; ?></textarea></td>
      </tr>

      <tr><td colspan="2">&nbsp;</td></tr>

      <tr>
         <td colspan="2"><b>Restrictions:</b></td>
      </tr>

      <tr>
         <td></td>
         <td>
         <table>
         <tr><td valign="top"><b>Restrict Visitors&rsquo; IPs to:</b></td></tr>
         <tr><td colspan="2">
         <span class="smallfont">(Single IP or comma-separated list of IP addresses.)</span></td></tr>
         <tr>
          <td colspan="2" valign="top">
          <blockquote>
            <textarea cols="30" rows="4" name="restrictions[visitor_ip]"title="The IP addresses that are allowed to access these scripts."><?php print htmlspecialchars($this->StripSlashes($this->restrictions['visitor_ip'])); ?></textarea>
            </blockquote>
         </td></tr>
         <tr><td valign="top"><b>Restrict Server IPs to:</b></td></tr>
         <tr><td colspan="2"><span class="smallfont">(Single IP or comma-separated list of IP addresses.)</span></td></tr>

         <tr>
         <td colspan="2" valign="top"><blockquote>
         <textarea cols="30" rows="4" name="restrictions[server_ip]"title="The IP addresses of servers that are allowed to host these scripts."><?php print htmlspecialchars($this->StripSlashes($this->restrictions['server_ip'])); ?></textarea>
         </blockquote>
         </td></tr>
         <tr><td valign="top"><b>Restrict Server Name to:</b></td></tr>
         <tr><td colspan="2"><span class="smallfont">(Single domain or comma-separated list of domains.)</span></td></tr>
         <tr><td><blockquote>
         <textarea cols="30" rows="4" name="restrictions[server_name]"title="The domain names of servers that are allowed to host these scripts."><?php print htmlspecialchars($this->StripSlashes($this->restrictions['server_name'])); ?></textarea>
         </blockquote></td></tr>

         <tr>
          <td colspan="2"><b>Script Expiration:</b></td>
         </tr>
         <tr><td colspan="2"><span class="smallfont">(Script expires in this amount of time and will no longer run.)</span></td></tr>
         <tr>
          <td colspan="2">
          <blockquote>
            <p>Script expires in <input type="text" size="5" name="restrictions[expire_value]" value="<?php print htmlspecialchars($this->StripSlashes($this->restrictions['expire_value'])); ?>"
            title="The number of time units in which to expire the script."> &nbsp;
            <select name="restrictions[expire_unit]" title="The units of time to use.">
            <option value="">Select</option><option value="seconds" <?php if (strtolower($this->restrictions['expire_unit']) == 'seconds') { echo 'selected'; } ?>>Seconds</option><option value="minutes" <?php if (strtolower($this->restrictions['expire_unit']) == 'minutes') { echo 'selected'; } ?>>Minutes</option><option value="hours" <?php if (strtolower($this->restrictions['expire_unit']) == 'hours') { echo 'selected'; } ?>>Hours</option><option value="days" <?php if (strtolower($this->restrictions['expire_unit']) == 'days') { echo 'selected'; } ?>>Days</option><option value="weeks" <?php if (strtolower($this->restrictions['expire_unit']) == 'weeks') { echo 'selected'; } ?>>Weeks</option><option value="months" <?php if (strtolower($this->restrictions['expire_unit']) == 'months') { echo 'selected'; } ?>>Months</option><option value="years" <?php if (strtolower($this->restrictions['expire_unit']) == 'years') { echo 'selected'; } ?>>Years</option></select></p>
            <p><b>OR</b>
            <p>Script expires at <input type="text" size="20" name="restrictions[expire_english]" value="<?php print htmlspecialchars($this->StripSlashes($this->restrictions['expire_english'])); ?>"
            title="The expiration time in GNU date and time format."></p>
            <!-- GJH 28/2/06 Updated URL. -->
            <a href='http://www.gnu.org/software/tar/manual/html_node/Date-input-formats.html#Date-input-formats'>
            <span class="smallfont">(GNU date and time format.)</span>
            </a>
          </blockquote>
          </td>
        </tr>
      </table>
      </td>
      </tr>

      <tr><td colspan="2" align="center"><input type="submit" name="submit_button" value="Encode Files"></td></tr>

      </table>
      </center>
      </form>
      <?php
   }

   /*
      @Name: Encode
      @Proto: bool Encode(void)
      @Desc: Encodes source files
   */

   function Encode()
   {
      if (!is_dir($this->src_dir) || !is_readable($this->src_dir)) {
         print '<b>Unable to begin, source directory is not readable or does not exist.</b>';
         return false;
      } elseif (!is_writable($this->src_dir)) {
         print '<b>Unable to begin, source directory is not writable, on Unix you must run the command chmod -R 777 ' . $this->src_dir . ' before continuing.';
         return false;
      }

      $encode_success = 0;
      $encode_failed = 0;

      // get a list of files to encode
      $files = $this->_ListValidFiles($this->src_dir);

      if (!is_dir($this->dest_dir)) {
         if (!$this->_MkDir($this->dest_dir, true)) {
            print '<b>Unable to create output directory ' . $this->dest_dir . ', please manually create this directory and, on Unix, run the command chmod 777 on it.</b>';
            return false;
         }
      }

      if (!is_writable($this->dest_dir)) {
         print '<b>' . $this->dest_dir . ' is not writable, please fix the permissions on this directory (Unix: chmod 777 ' . $this->dest_dir . ').</b>';
         return false;
      }

      $this->restrictions_code = $this->_BuildRestrictions($_REQUEST['restrictions']);

      if (!is_array($files)) {
         print '<b>No files eligible for encoding exist in the source directory.</b>';
         return false;
      }

      // compile each eligible file and display status
      foreach ($files as $file) {
         if (empty($file)) {
            continue;
         // make sure directory is writable for temporary encoding purposes
         } else if (!is_writable(dirname($file))) {
          print '<span class="failfont">' . dirname($file) . ' is not writable, please run the command chmod -R 777 ' . dirname($file) . '.</span><br>';
          continue;
         }

         if ($this->EncodeFile($file, $this->dest_dir)) {
            print '<span class="successfont">Successfully encoded ' . $file . '</span><br>';
            $encode_success++;
         } else {
            print '<span class="failfont">Failed to encode ' . $file . '.</span><br>';
            $encode_failed++;
         }

         // flush output so if the script executes a long time we aren't waiting for output
         if (function_exists("ob_get_level")) {
             // GJH-Check that buffer exists before flushing to avoid failure errors.
             if(ob_get_contents())
                 ob_flush();
         }
      }

      // display summary of files
      print '<p>';
      print '<span class="smallfont"><b>Successfully Encoded ' . $encode_success . ' files.</span><br>';
      print '<span class="smallfont"><b>Failed to Encode ' . $encode_failed . ' files.</span><br>';
      print '</p>';

      // copy skipped files if we need to
      if ($this->copy_skipped_files == true) {
         print '<p>';
         if ($this->_CopySkippedFiles($this->src_dir, $this->dest_dir)) {
         // GJH-Removed output of "Copied non-source files" from here to prevent
         // output even when files weren't copied. Added output for each copied file
         // to _CopySkippedFiles().

            if ($encode_failed > 0) {
               print '<p><b>Files that failed to encode were copied in plain text to avoid breaking the application.</b></p>';
            }
         } else {
            print '<span class="failfont">Failed to copy skipped files.</span><br>';
         }
         print '</p>';
      }

   }

   /*
      @Name: EncodeFile
      @Proto: bool EncodeFile(path $file)
      @Desc: Reads, Encodes and Writes $file encoded using eaccelerator.
   */

   function EncodeFile($file, $destination)
   {
      if (!@is_file($file) || !@is_readable($file)) {
         return false;
      }

      // encode the file, make sure we were successful
      if (! $encoded_content = $this->_MmCacheEncode($file)) {
         return false;
      } elseif (strlen($encoded_content) == 0) {
         return false;
      }

      // build content string for writing to file, include loader code and strip uncessary tabs and new lines
      $content = str_replace(array("\n", "\t"), '', '<?php ' . $this->loader_code . "\n" . 'eaccelerator_load(\'' . $encoded_content . '\'); ?>');


      // write the file out to our destination
      $destination_file = substr(str_replace($this->src_dir, '', $file), count($this->src_dir)); // figure out destination file name
      $destination_directory = dirname($destination . '/' . $destination_file); // figure out destination directory
      $destination_path = $destination_directory . '/' . basename($destination_file); // absolute destination path

      // make sure destination directory exists
      if (!is_dir($destination_directory)) {
         // use recursion to make destination direcotory
         if (!$this->_MkDir($destination_directory, true)) {
            return false;
         }
      }

      // write out encoded file or return false
      if ($this->_WriteFile($destination_path, $content)) {
         chmod($destination_path, $this->file_mode);
         return true;
      } else {
         return false;
      }
   }

   /*
      @Name: HtmlFooter
      @Proto: void HtmlFooter(void)
      @Desc: Displays HTML Footer
   */

   function HtmlFooter()
   {
      ?>
        </td></tr>
        </table>
        </center>
        <p>
          <a href="http://validator.w3.org/">
          <img border="0" src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01!" height="31" width="88"></a>
          <a href="http://jigsaw.w3.org/css-validator/">
          <img src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" height="31" width="88" border=0></a>
        </p>
      </body>
      </html>
      <?php
   }

   /*
      @Name: HtmlHeader
      @Proto: void HtmlHeader(string $title)
      @Desc: Displays HTML Header
   */

   // GJH 27/2/06 Removed all CSS errors and warnings.
   function HtmlHeader($title = '')
   {
      ?>
      <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
         <html>
            <head><title>PHPCoder <?php print $this->version ?> (eAccelerator PHP Encoder Front End)</title>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <STYLE type="text/css">
              TD { 
                background-color: #cccccc;
                color: black;
              }

              BODY {
                font-family: sans-serif;
                font-size: medium;
              }

              TD {
                font-family: sans-serif;
                font-size: medium;
              }

              TD.withborder {
                font-family: sans-serif;
                font-size: medium;
                border-style: solid;
                border-width: 1px;        /* sets border width on all sides */
                border-color: black;
              }

              TD.withborder-medium {
                font-family: sans-serif;
                font-size: medium;
                border-style: solid;
                border-width: 3px;        /* sets border width on all sides */
                border-color: black;
              }


              .header {
                font-family: sans-serif;
                color: rgb(199,21,133);
                background-color: #cccccc;
                font-weight: bold;
                font-size: larger;
              }

              .coderborder {
                border-style: dashed;
                border-width: medium;        /* sets border width on all sides */
                border-color: black;
              }

              .smallfont {
                font-family: sans-serif;
                font-size: smaller;
              }

              .successfont {
                font-family: sans-serif;
                font-size: smaller;
                color: green;
                background-color: #cccccc;
              }

              .failfont {
                font-family: sans-serif;
                font-size: smaller;
                color: red;
                background-color: #cccccc;
              }

            </STYLE>
            </head>
            <body>

            <center><table><tr><td class="withborder-medium">
            <p><span class="header">PHPCoder v. <?php print $this->version; ?>
            (<a href="http://phpcoder.sourceforge.net/">http://phpcoder.sourceforge.net/</a>)
            </span></p>
            <p>
               <span class="smallfont">Hover over the controls and text areas or see the <a href="./PHPCoder Manual.htm">manual</a> for more information.</span>
            </p>

      <?php
   }

   /*
      @Name: StripSlashes
      @Proto: string StripSlashes(string $string)
      @Desc: Performs stripslashes on a string if magic quotes gpc is enabled
   */

   function StripSlashes($string)
   {
      if (ini_get('magic_quotes_gpc') == true) {
         return stripslashes($string);
      } else {
         return $string;
      }
   }

   /*
      @Name: _BuildRestrictions
      @Proto: string/null _BuildRestrictions(void)
      @Desc: Builds PHP code to implement restrictions
      @Scope: Private
   */

   function _BuildRestrictions()
   {
      // GJH-Declare $expire_stamp to avoid undeclared variable errors.
      $expire_stamp = "";
      $rest = &$this->restrictions; // alias $rest to $this->restrictions for shorter code

      $return_code = '';

      // strip out unwanted white space for variable that controls visitor IP access
      $visitor_ips = str_replace(array(" ", "\t", "\n"), '', $rest['visitor_ip']);

      if (!empty($visitor_ips)) {
        $return_code .= 'if (!strstr("' . $visitor_ips . '", $_SERVER[\'REMOTE_ADDR\'])) {die(\'This script has been locked to a specific visitor IP address.\'); }';
      }

      // locks the script to a specific server ip(s)
      $server_ips = str_replace(array(" ", "\t", "\n"), '', $rest['server_ip']);

      if (!empty($server_ips)) {
         $return_code .= 'if (!strstr("' . $server_ips . '", $_SERVER[\'SERVER_ADDR\'])) {die(\'This script has been locked to a specific server IP.\'); }';
      }

      $server_names = str_replace(array(" ", "\t", "\n"), '', $rest['server_name']);

      // this code locks the script to a specific server name/domain
      if (!empty($rest['server_name'])) {
         $return_code .= 'if (!strstr("' . $server_names . '", $_SERVER[\'SERVER_NAME\'])) {die(\'This script has been locked to a specific server name.\'); }';
      }

      // build script expiration timestamp
      if (!empty($rest['expire_value']) && is_numeric($rest['expire_value']) && !empty($rest['expire_unit'])) {
         $expire_stamp = strtotime('+' . $rest['expire_value'] . ' ' . $rest['expire_unit']);
      } elseif (!empty($rest['expire_english'])) {
        $expire_stamp = strtotime($rest['expire_english']);
      }

      if ($expire_stamp > 0) {
         $expire_english = date('m/d/y G:i:s', $expire_stamp);

         if (is_numeric($expire_stamp)) {
            $return_code .= 'if (time() >= \'' . $expire_stamp . '\') {die(\'This script has expired please contact the author for more information.\'); }';
         }

         unset($expire_stamp, $expire_english);
      }

      if (!empty($return_code)) {
         $return_code = '<?php ' . $return_code . ' ?>';

         return $return_code;
      } else {
         return '';
      }
   }

   /*
      @Name: _CopySkippedFiles
      @Proto: bool _CopySkippedFiles(path $src, path $dest)
      @Desc: Copies any files/subdirectories in $src that are missing in $dest
      @Scope: Private
   */

   function _CopySkippedFiles($src, $dest)
   {
      if (!is_dir($src) || !is_dir($dest)) {
         return false;
      }

      // make sure we can open the directory
      if (!$dh = opendir($src)) {
         return false;
      }

      // get a list of each file in the directory, skip files we shouldn't see
      while (FALSE !== ($file = readdir($dh))) {
         // skip $ignore_files, because this is where . and .. live
         if (is_array($this->ignore_files) && in_array($file, $this->ignore_files)) {
            continue;
         }

         $file_path = $src . '/' . $file;
         $dest_path = $dest . '/' . $file;

         // if entry is a directory and it doesn't exist create the directory
         if (@is_dir($file_path)) {
            if (!is_dir($dest . '/' . $file)) {
               if (!$this->_MkDir($dest . '/' . $file, true)) {
                  return false;
               }
            }

            // copy sub-directories and folders
            if (!$this->_CopySkippedFiles($src . '/' . $file, $dest . '/' . $file)) {
               return false;
            }
         } else {
            // otherwise copy the file
            if (!@is_file($dest_path)) {
               if (!@copy($file_path, $dest_path)) {
                  print '<font color="red">Failed to copy ' . $file_path . ' to ' . $dest_path . '.</font><br>';
                  return false;
               }
               // GJH-Print file-specific output when copying files.
               else{
                  print '<span class="successfont">Copied ' . $file_path . '.</span><br>';
               }
            }
         }
      }

      @closedir($dh);

      return true;

   }

   /*
      @Name: _FileGetContents
      @Proto: string/bool _FileGetContents(path $file)
      @Desc: Returns contents of $file, uses file_get_contents if available, otherwise uses fopen and fread
      @Scope: Private
   */
   function _FileGetContents($file)
   {
      if (!is_file($file) || !is_readable($file)) {
         return false;
      }

      // if file_get_contents exists use it (PHP >= 4.3.0)
      if (is_callable('file_get_contents')) {
         return file_get_contents($file);
      } else {
         // use fopen/fread/fclose to simulate file_get_contents
         if (!$fp = fopen($file, 'rb')) {
            return false;
         }

         $contents = fread($fp, filesize($file));
         @fclose($fp);

         if (strlen($contents) > 0) {
            return $contents;
         } else {
            return false;
         }
      }
   }

   /*
      @Name: _ListFiles
      @Proto: array/bool _ListFiles(path $file_dir, bool valid)
      @Desc: Returns an array of files from $file_dir
      @Scope: Private
   */

   function _ListValidFiles($file_dir)
   {
      // GJH-Declare variable to avoid errors.
      $return = "";
      if (!is_dir($file_dir)) {
         die($file_dir . ' is not a directory');
      }

      // make sure we can open the directory
      if (!$dh = opendir($file_dir)) {
         return false;
      }

      // get a list of each file in the directory, skip files we shouldn't see
      while (FALSE !== ($file = readdir($dh))) {
         // perform recursion if $file is a directory and is not in $ignore_files
         if (@is_dir($file_dir . '/' . $file) && $this->recursive == true) {
            if (is_array($this->ignore_files) && in_array($file, $this->ignore_files)) {
               continue;
            }

            // combine current results with sub-directory results
            $return = array_merge($return, $this->_ListValidFiles($file_dir . '/' . $file));

         } else {
            $ext = array_pop(explode('.', $file)); // get the file extension

            // continue if the file is not a valid file based on the rules setup
            if (is_array($this->ignore_files) && in_array($file, $this->ignore_files) || (is_array($this->ignore_extensions) && in_array($ext, $this->ignore_extensions)) || (is_array($this->extensions) && !in_array($ext, $this->extensions))) {
               continue;
            }
            // include the file in the path
            $return[] = $file_dir . '/' . $file;
         }
      }

      // close directory handle
      @closedir($dh);

      // check to see if we found results
      if (is_array($return) && count($return) > 0) {
         return $return;
      } else {
         return false;
      }
   }

   /*
      @Name: _MkDir
      @Proto: bool _MkDir(path $directory, bool $recursive)
      @Desc: Creates $directory on filesystem, $recursive makes recursive directory creation
      @Scope: Private
   */

   /*
      @Name: _MmCacheEncode
      @Proto: string/bool _MmCacheEncode(path file)
      @Desc: Returns the encoded contents of $file with $php_pre_content prepended
      @Scope: Private
   */
   function _MmCacheEncode($file) {
      // create temporary file name, put it in the current working directory so we don't break relative paths
      $tmp_name = tempnam (dirname($file) , "eaccelerator_encode_");

      // attempt to open the file for writing
      if (!$fp = @fopen($tmp_name, 'wb')) {
         print 'unable to open file';
         return false;
      }

      $contents = ''; // initialize contents

      if (!empty($this->restrictions_code)) {
         $contents .= $this->restrictions_code;
      }

      // include pre contents if specified
      if (!empty($this->php_pre_content)) {
         $contents .= $this->php_pre_content;
      }

      // get file contents
      $contents .= $this->_FileGetContents($file);

      // include post content if specified
      if (!empty($this->php_post_content)) {
         $contents .= $this->php_post_content;
      }

      // write our string to the file
      if (!fwrite($fp, $contents, strlen($contents))) {
         @fclose($fp);
         // remove our temporary file
         sleep(1);
         @unlink($tmp_name);

         return false;
      }

      // close the file
      fclose($fp);
      @usleep(6000); // give the os a chance to complete the fclose (windows)

      // suppress compile error messages if debug is not on
      if ($this->debug != true) {
         $encoded_contents = @eaccelerator_encode($tmp_name);
      } else {
         $encoded_contents = eaccelerator_encode($tmp_name);
      }

      @unlink($tmp_name);

      if (!empty($encoded_contents)) {
         return $encoded_contents;
      } else {
         return false;
      }
   }

   /*
      @Name: _MmCacheEncodeString
      @Proto: string/bool _MmCacheEncodeString(string string)
      @Desc: Returns the result of mmcache_encode on a string, simulates this function by writing to a temporary file
      @Scope: Private
   */
   function _MmCacheEncodeString($string)
   {
      if (empty($string)) {
         return false;
      }

      // create temporary file name
      $tmp_name = tempnam ("/tmp", "eaccelerator_encode_");
      
      // attempt to open the file for writing
      if (!$fp = fopen($tmp_name, 'wb')) {
         return false;
      }

      // write our string to the file
      if (!fwrite($fp, $string, strlen($string))) {
         @fclose($fp);
         // remove our temporary file
         @unlink($tmp_name);

         return false;
      }

      // close the file
      @fclose($fp);
      
      // use eaccelerator to encode the file
      $encoded_contents = eaccelerator_encode($tmp_name);

      // remove our temporary file
      @unlink($tmp_name);

      if (!empty($encoded_contents)) {
         return $encoded_contents;
      } else {
         return false;
      }

   }

   function _MkDir($directory, $recursive = false)
   {
      // make sure location is not already existing as a file
      if (empty($directory)) {
       return true;
      } elseif (is_file($directory)) {
         return false;
      } elseif (is_dir($directory)) {
         return true;
      }

      // if we were not asked to recursively create blindly attempt to mkdir and return the result
      if ($recursive == false) {
         return @mkdir($directory, $this->directory_mode);
      } else {
         // do recursive directory make
         $dirname = dirname($directory);

         if (!$this->_MkDir($dirname, true)) {
            return false;
         }

         return @mkdir($directory, $this->directory_mode);
      }
   }

   /*
      @Name: _WriteFile
      @Proto: bool _WriteFile(path $file, string $contents)
      @Desc: Writes $contents into $file or returns false
      @Scope: Private
   */
   function _WriteFile($file, $contents)
   {
      if (empty($file)) {
         return false;
      }

      if (!$fp = @fopen($file, 'wb')) {
         return false;
      }

      if (!fwrite($fp, $contents, strlen($contents))) {
         return false;
      }

      @fclose($fp);

      return true;
   }
}
?>







