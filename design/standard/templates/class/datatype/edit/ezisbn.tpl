{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{let content=$class_attribute.content}
{if $content.ranges.has_content|eq(false())}
<div class="message-warning">
  <h2>{'Range data for the ISBN-13 does not exist'|i18n( 'design/standard/class/datatype' )}</h2>
  <p>{'Please follow the instructions for the isbn datatype to install the valid ranges.'|i18n( 'design/standard/class/datatype' )}</p>
  <div class="block">
    <h3>{'Definition from distribution'|i18n( 'design/standard/class/datatype' )}</h3>
    <p>{'You can load the definition that follows the standard distribution here:'|i18n( 'design/standard/class/datatype' )}</p>
    <input type="submit" name="CustomActionButton[{$class_attribute.id}_ImportISBN13Data]" value="{'Import Isbn range data'|i18n( 'design/standard/class/datatype' )}" />
  </div>
  <div class="block">
    <h3>{'Import newest version of the Isbn ranges'|i18n( 'design/standard/class/datatype' )}</h3>
    <p>{"It's possible to import the data with the script: bin/php/updateisbn13.php --file=ranges.js"|i18n( 'design/standard/class/datatype' )}</p>
    <p>{"Please read the documentation in the file doc/specifications/3.10/isbn/technical_specifications.txt for how to proceed."|i18n( 'design/standard/class/datatype' )}</p>
  </div>
</div>
{/if}
<div class="block">
    <input type="checkbox" name="ContentClass_ezisbn_13_value_{$class_attribute.id}" {if $content.ISBN13}checked="checked"{/if}/>
    <b>{'ISBN-13 format'|i18n( 'design/standard/class/datatype' )}</b>
    <input type="hidden" name="ContentClass_ezisbn_13_value_{$class_attribute.id}_exists" value="1" />
</div>
{/let}
