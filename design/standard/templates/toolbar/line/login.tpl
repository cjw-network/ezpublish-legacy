{section show=eq($current_user.is_logged_in)}
<a href={"/user/login"|ezurl}>{"Login"|i18n("design/shop/layout")}</a>&nbsp;
{section-else}
<a href={"/user/logout"|ezurl}>{"Logout"|i18n("design/shop/layout")}({$current_user.contentobject.name})</a>&nbsp;
{/section}