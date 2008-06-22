{* $Id$ *}

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