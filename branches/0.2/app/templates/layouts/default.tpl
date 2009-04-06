{* $Id: default.tpl 71 2008-06-24 14:14:24Z andi.trinculescu $ *}
<base href="{sa_base_href}">
<title>default layout</title>
<div style="border:1px solid navy;padding:5px;">{$__CONTENT_FOR_LAYOUT__}</div>
<div><a href="{sa_url page='nested/' actions='something, else' x=1 y=2 next='http://php.net/' z=$var_not_defined}">nested page with actions and plugin</a></div>
