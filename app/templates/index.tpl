{* $Id: index.tpl 57 2008-06-22 12:47:51Z andi.trinculescu $ *}

<p>{$msg}</p>
<div>
	{if $post_msg}
	<div>{$post_msg}</div>
	<form method="GET" action="{sa_url}">
		<input type="submit" value="Get me!">
	</form>
	{else}
	<div>{$get_msg}</div>
	<form method="POST" action="{sa_url}">
		<input type="submit" value="Post me!">
	</form>
	{/if}
</div>