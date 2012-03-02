<?php
/*
Idea on preview box on details page, based on the old "recommendation" concept and the new (graphics) transforms.
 - Transforms could be made more flexible, for example support graphviz and wiki syntax files.
 - Contents of proprietary formats could be retrieved from indexing.

<!-- <h2>Actions to view {=$file,kind2}</h2> -->
<!--{if $file,isFile}-->
<p class="view recommendation">
{=if $file,size gt 102400}
This file is bigger than 100 kb, so we recommend <a href="download/?target={=$target}{=$file,revParam}">download</a>.
{=elseif $file,type eq 'text/html'}
This is an HTML file, so we recommend <a href="open/?target={=$target}{=$file,revParam}">open</a>.
{=elseif $file,isPlaintext}
This is file is plain text, so we recommend <a href="file/?target={=$target}{=$file,revParam}">View in Repos</a>.
{=/if}
</p>
<!--{/if}-->
<!--{if isset($fromrev)}-->
 */


class ReposPreview {
	
	function ReposPreview($svnOpenFile) {
		
	}
	
	function getHtml() {
			
	}
	
}

?>