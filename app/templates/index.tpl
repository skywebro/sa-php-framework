{* $Id$ *}

<p>{$msg}</p>
<div>
	{if $post_msg}
	<div>{$post_msg}</div>
	<form method="GET">
		<input type="submit" value="Get me!">
	</form>
	{else}
	<div>{$get_msg}</div>
	<form method="POST">
		<input type="submit" value="Post me!">
	</form>
	{/if}
</div>