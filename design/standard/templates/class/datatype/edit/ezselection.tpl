{* DO NOT EDIT THIS FILE! Use an override template instead. *}
<div class="block">
<input type="checkbox" name="ContentClass_ezselection_ismultiple_value_{$class_attribute.id}" {section show=$class_attribute.data_int1}checked{/section} /><label>{"Multiple choice"|i18n("design/standard/class/datatype")}</label>
</div>

<label>{'Options.'|i18n( 'design/standard/class/datatype' )}</label>

{section show=$class_attribute.content.options}
{section name=Option loop=$class_attribute.content.options}
<input type="text" name="ContentClass_ezselection_option_name_array_{$class_attribute.id}[{$Option:item.id}]" value="{$Option:item.name}" />
<input type="checkbox" name="ContentClass_ezselection_option_remove_array_{$class_attribute.id}[{$Option:item.id}]" value="1" title="{'Select option for removal.'|i18n( 'design/standard/class/datatype' )}" />
<br />
{/section}
{section-else}
{'There are no options.'|i18n( 'design/standard/class/datatype' )}
{/section}

<input class="button" type="submit" name="ContentClass_ezselection_newoption_button_{$class_attribute.id}" value="{"New option"|i18n("design/standard/class/datatype")}" title="{'Add a new option.'|i18n( 'design/standard/class/datatype' )}" />
<input class="button" type="submit" name="ContentClass_ezselection_removeoption_button_{$class_attribute.id}" value="{"Remove selected"|i18n("design/standard/class/datatype")}" title="{'Remove selected options.'|i18n( 'design/standard/class/datatype' )}" />
